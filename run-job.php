#!/usr/bin/env php
<?php

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

require __DIR__ . '/vendor/autoload.php';

$configPath = __DIR__ . '/config/background-jobs.php';
$logPath = __DIR__ . '/storage/logs/background_jobs.log';
$errorLogPath = __DIR__ . '/storage/logs/background_jobs_errors.log';
$jobsDir = __DIR__ . '/storage/jobs/';

if (!file_exists($configPath)) {
    file_put_contents($errorLogPath, "[ERROR] Config file not found: background-jobs.php\n", FILE_APPEND);
    exit(1);
}

$config = require $configPath;

if ($argc < 3) {
    file_put_contents($errorLogPath, "[ERROR] Invalid arguments passed.\n", FILE_APPEND);
    exit(1);
}

$className = $argv[1];
$methodName = $argv[2];
$params = isset($argv[3]) ? explode(',', $argv[3]) : [];

// Detect OS Type
$isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

// Security Check
if (!isset($config['allowed_jobs'][$className]) || !in_array($methodName, $config['allowed_jobs'][$className])) {
    file_put_contents($errorLogPath, "[SECURITY] Unauthorized job execution attempt: $className::$methodName\n", FILE_APPEND);
    exit(1);
}

// Ensure class exists
if (!class_exists($className)) {
    file_put_contents($errorLogPath, "[ERROR] Class does not exist: $className\n", FILE_APPEND);
    exit(1);
}

// Ensure method exists in class
$instance = new $className();
if (!method_exists($instance, $methodName)) {
    file_put_contents($errorLogPath, "[ERROR] Method not found: $className::$methodName\n", FILE_APPEND);
    exit(1);
}

// Lock File - Prevent Duplicate Execution
$lockFile = $jobsDir . "{$className}_{$methodName}.lock";
if (file_exists($lockFile)) {
    file_put_contents($errorLogPath, "[WARNING] Duplicate job execution attempt: $className::$methodName\n", FILE_APPEND);
    exit(1);
}
touch($lockFile);

// Execute Job with Retry & Logging
$maxRetries = 3;
$retryDelay = 5;
$attempts = 0;

while ($attempts < $maxRetries) {
    try {
        $command = "php -r 'call_user_func_array([new $className, \"$methodName\"], " . json_encode($params) . ");'";

        if ($isWindows) {
            // Windows execution
            $process = new Process(["cmd.exe", "/c", "start /B " . $command]);
        } else {
            // Unix/Linux/macOS execution
            $process = new Process(["nohup", $command, ">", "/dev/null", "2>&1", "&"]);
        }

        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        file_put_contents($logPath, "[SUCCESS] $className::$methodName executed successfully.\n", FILE_APPEND);
        break;
    } catch (Exception $e) {
        file_put_contents($errorLogPath, "[ERROR] Attempt $attempts failed: " . $e->getMessage() . "\n", FILE_APPEND);
        sleep($retryDelay);
    }
    $attempts++;
}

// Cleanup Lock File
unlink($lockFile);

exit(0);
