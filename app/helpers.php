<?php

if (!function_exists('runBackgroundJob')) {
    /**
     * Trigger the execution of a background job via the CLI runner.
     *
     * This helper function calls the run-job.php script located at the project root.
     * It builds a command that passes:
     *   - The job class (shorthand, without a fully qualified namespace)
     *   - The method name to call
     *   - A comma-separated string of parameters
     *
     * The function uses Symfony Process to ensure platform independence.
     *
     * @param string $class  The job class to run (e.g., "LessonCreatedService")
     * @param string $method The method to invoke on the class
     * @param array  $params Array of parameters to pass to the method
     *
     * @return \Symfony\Component\Process\Process The process instance (if needed for later monitoring)
     */
    function runBackgroundJob($class, $method, $params = [])
    {
        // Convert the parameters array into a comma-separated string.
        $paramString = implode(',', $params);
        
        // Get the path to run-job.php. In Laravel, the base_path() helper provides the project root.
        $runJobPath = base_path('run-job.php');
        
        // Get the PHP executable using Symfony's PhpExecutableFinder.
        $phpBinary = (new \Symfony\Component\Process\PhpExecutableFinder())->find();
        
        // Build the command as an array for safety (handles spaces and quoting issues).
        // The command structure: php run-job.php ClassName methodName "param1,param2"
        $command = [
            $phpBinary,
            $runJobPath,
            $class,
            $method,
            $paramString
        ];
        
        // Create a new Symfony Process instance with the command.
        $process = new \Symfony\Component\Process\Process($command);
        
        // Set the process timeout to 0 (meaning no timeout) if desired.
        $process->setTimeout(0);
        
        // Run the process asynchronously (fire and forget).
        $process->start();
        
        // You can return the process instance if you'd like to monitor it, or simply return void.
        return $process;
    }
}
