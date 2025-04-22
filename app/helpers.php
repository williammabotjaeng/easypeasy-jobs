<?php

if (!function_exists('runBackgroundJob')) {
    function runBackgroundJob($class, $method, $params = [])
    {
        $command = escapeshellcmd("php run-job.php $class $method " . implode(',', $params));

        // Detect OS
        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

        // Execute command asynchronously based on OS
        if ($isWindows) {
            pclose(popen("start /B " . $command, "r"));
        } else {
            shell_exec($command . " > /dev/null 2>&1 &");
        }
    }
}

if (!function_exists('getJobLogs')) {
    function getJobLogs()
    {
        $logPath = storage_path('logs/background_jobs.log');
        return file_exists($logPath) ? file_get_contents($logPath) : "No job logs found.";
    }
}

if (!function_exists('getErrorLogs')) {
    function getErrorLogs()
    {
        $errorLogPath = storage_path('logs/background_jobs_errors.log');
        return file_exists($errorLogPath) ? file_get_contents($errorLogPath) : "No error logs found.";
    }
}
