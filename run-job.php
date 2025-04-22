#!/usr/bin/env php
<?php

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\PhpExecutableFinder;

require __DIR__ . '/vendor/autoload.php';

$configPath    = __DIR__ . '/config/background-jobs.php';
$logPath       = __DIR__ . '/storage/logs/background_jobs.log';
$errorLogPath  = __DIR__ . '/storage/logs/background_jobs_errors.log';
$jobsDir       = __DIR__ . '/storage/jobs/';

// Ensure the config file exists.
if (!file_exists($configPath)) {
    file_put_contents($errorLogPath, "[ERROR] Config file not found.\n", FILE_APPEND);
    exit(1);
}

$config = require $configPath;

if ($argc < 3) {
    file_put_contents($errorLogPath, "[ERROR] Invalid arguments passed.\n", FILE_APPEND);
    exit(1);
}

// Get the command-line arguments.
$inputClass = $argv[1]; // e.g., "LessonStartedService"
$methodName = $argv[2];
$params     = isset($argv[3]) ? explode(',', $argv[3]) : [];

// If the input class does not contain a backslash, prepend the default namespace.
if (strpos($inputClass, '\\') === false) {
    $fullClassName  = "App\\Services\\" . $inputClass;
    $shortClassName = $inputClass;
} else {
    $fullClassName = $inputClass;
    $parts = explode('\\', $fullClassName);
    $shortClassName = end($parts);
}

// SECURITY CHECK: Compare against allowed_jobs using the short class name.
if (
    !isset($config['allowed_jobs'][$shortClassName]) ||
    !in_array($methodName, $config['allowed_jobs'][$shortClassName])
) {
    file_put_contents(
        $errorLogPath,
        "[SECURITY] Unauthorized job execution attempt: $shortClassName::$methodName\n",
        FILE_APPEND
    );
    exit(1);
}

// Ensure the class exists (using the fully qualified name).
if (!class_exists($fullClassName)) {
    file_put_contents($errorLogPath, "[ERROR] Class does not exist: $fullClassName\n", FILE_APPEND);
    exit(1);
}

// Ensure that the method exists.
$instance = new $fullClassName();
if (!method_exists($instance, $methodName)) {
    file_put_contents($errorLogPath, "[ERROR] Method not found: $fullClassName::$methodName\n", FILE_APPEND);
    exit(1);
}

// LOCK FILE – Prevent Duplicate Execution (using the short class name).
$lockFile = $jobsDir . "{$shortClassName}_{$methodName}.lock";
if (file_exists($lockFile)) {
    file_put_contents($errorLogPath, "[WARNING] Duplicate job execution attempt.\n", FILE_APPEND);
    exit(1);
}
touch($lockFile);

// Locate the PHP executable.
$phpFinder = new PhpExecutableFinder();
$phpBinary = $phpFinder->find() ?: 'php';

// Get the absolute path to autoload.php and normalize slashes for Windows.
$autoloadPath = realpath(__DIR__ . '/vendor/autoload.php');
$autoloadPath = str_replace('\\', '/', $autoloadPath);

// Encode parameters as JSON and then base64 encode them to prevent quoting issues.
$encodedParams = base64_encode(json_encode($params));

// Build the inline PHP code that will be executed.
// The code includes the autoloader, decodes the parameters (defaulting to an empty array if needed),
// and then calls the specified service method.
$code = "require '$autoloadPath'; ";
$code .= "\$args = json_decode(base64_decode('$encodedParams'), true); ";
$code .= "if (!is_array(\$args)) { \$args = []; } ";
$code .= "call_user_func_array([new $fullClassName, '$methodName'], \$args);";

// Build the final command string. The entire -r argument is wrapped in double quotes.
$command = "$phpBinary -r \"" . $code . "\"";

// (Optional: Log the command for debugging purposes.)
// file_put_contents($logPath, "[DEBUG] Command: $command\n", FILE_APPEND);

try {
    $process = Process::fromShellCommandline($command);
    $process->setTimeout($config['max_execution_time'] ?? 300);

    // Run synchronously; stream output for real-time logging.
    $process->run(function ($type, $buffer) use ($logPath, $errorLogPath) {
        if ($type === Process::ERR) {
            file_put_contents($errorLogPath, "[ERROR] $buffer\n", FILE_APPEND);
        } else {
            file_put_contents($logPath, "[INFO] Running Job: $buffer\n", FILE_APPEND);
        }
    });

    if (!$process->isSuccessful()) {
        throw new ProcessFailedException($process);
    }
    
    file_put_contents($logPath, "[SUCCESS] $fullClassName::$methodName executed successfully.\n", FILE_APPEND);
} catch (ProcessFailedException $e) {
    file_put_contents(
        $errorLogPath,
        "[ERROR] Job execution failed: " . $e->getMessage() . "\n",
        FILE_APPEND
    );
}

// Cleanup: remove the lock file.
unlink($lockFile);

exit(0);
