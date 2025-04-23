#!/usr/bin/env php
<?php

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\PhpExecutableFinder;

require __DIR__ . '/vendor/autoload.php';

$configPath   = __DIR__ . '/config/background-jobs.php';
$logPath      = __DIR__ . '/storage/logs/background_jobs.log';
$errorLogPath = __DIR__ . '/storage/logs/background_jobs_errors.log';
$jobsDir      = __DIR__ . '/storage/jobs/';

// Helper function to get current timestamp
function currentTimestamp() {
    return date('Y-m-d H:i:s');
}

// Logging helper (optional, but helps keep consistency)
function logMessage($file, $class, $method, $status, $message = '') {
    $timestamp = currentTimestamp();
    $logLine = "[$timestamp] [$status] $class::$method " . ($message ? "- " . $message : "") . "\n";
    file_put_contents($file, $logLine, FILE_APPEND);
}

// Ensure config file exists.
if (!file_exists($configPath)) {
    logMessage($errorLogPath, 'N/A', 'N/A', 'ERROR', 'Config file not found.');
    exit(1);
}

$config = require $configPath;

if ($argc < 3) {
    logMessage($errorLogPath, 'N/A', 'N/A', 'ERROR', 'Invalid arguments passed.');
    exit(1);
}

// Get command-line arguments.
$inputClass = $argv[1]; // e.g., "LessonStartedService"
$methodName = $argv[2];
$params     = isset($argv[3]) ? explode(',', $argv[3]) : [];

// Prepend default namespace if not provided.
if (strpos($inputClass, '\\') === false) {
    $fullClassName  = "App\\Services\\" . $inputClass;
    $shortClassName = $inputClass;
} else {
    $fullClassName = $inputClass;
    $parts         = explode('\\', $fullClassName);
    $shortClassName = end($parts);
}

// SECURITY: Validate against allowed_jobs using the short class name.
if (
    !isset($config['allowed_jobs'][$shortClassName]) ||
    !in_array($methodName, $config['allowed_jobs'][$shortClassName])
) {
    logMessage($errorLogPath, $shortClassName, $methodName, 'SECURITY', 'Unauthorized job execution attempt.');
    exit(1);
}

// Ensure the class exists.
if (!class_exists($fullClassName)) {
    logMessage($errorLogPath, $fullClassName, $methodName, 'ERROR', 'Class does not exist.');
    exit(1);
}

// Ensure the method exists.
$instance = new $fullClassName();
if (!method_exists($instance, $methodName)) {
    logMessage($errorLogPath, $fullClassName, $methodName, 'ERROR', 'Method not found.');
    exit(1);
}

// Lock file: Prevent duplicate execution.
$lockFile = $jobsDir . "{$shortClassName}_{$methodName}.lock";
if (file_exists($lockFile)) {
    logMessage($errorLogPath, $shortClassName, $methodName, 'WARNING', 'Duplicate job execution attempt.');
    exit(1);
}
touch($lockFile);

// Locate PHP Binary.
$phpFinder = new PhpExecutableFinder();
$phpBinary = $phpFinder->find() ?: 'php';

// Get absolute autoloader path and normalize slashes for Windows.
$autoloadPath = realpath(__DIR__ . '/vendor/autoload.php');
$autoloadPath = str_replace('\\', '/', $autoloadPath);

// Encode parameters using base64 to prevent quoting issues.
$encodedParams = base64_encode(json_encode($params));

// Build the inline PHP code.
// The code includes the autoloader, decodes the parameters (defaulting to an empty array if needed),
// and then calls the specified service method.
$code  = "require '$autoloadPath'; ";
$code .= "\$args = json_decode(base64_decode('$encodedParams'), true); ";
$code .= "if (!is_array(\$args)) { \$args = []; } ";
$code .= "call_user_func_array([new $fullClassName, '$methodName'], \$args);";

// Build the final command string. The entire -r argument is wrapped in double quotes.
$command = "$phpBinary -r \"" . $code . "\"";

// Execute the job.
try {
    $process = Process::fromShellCommandline($command);
    $process->setTimeout($config['max_execution_time'] ?? 300);
    
    // Run synchronously while logging standard output and errors.
    $process->run(function ($type, $buffer) use ($logPath, $errorLogPath, $shortClassName, $methodName) {
        if ($type === Process::ERR) {
            logMessage($errorLogPath, $shortClassName, $methodName, 'ERROR', $buffer);
        } else {
            logMessage($logPath, $shortClassName, $methodName, 'INFO', trim($buffer));
        }
    });

    if (!$process->isSuccessful()) {
        throw new ProcessFailedException($process);
    }
    
    logMessage($logPath, $fullClassName, $methodName, 'SUCCESS', 'executed successfully.');
} catch (ProcessFailedException $e) {
    logMessage($errorLogPath, $fullClassName, $methodName, 'ERROR', 'Job execution failed: ' . $e->getMessage());
}

// Cleanup: Remove the lock file.
unlink($lockFile);

exit(0);
