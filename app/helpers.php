<?php

if (!function_exists('runBackgroundJob')) {
    /**
     * Trigger the execution of a background job via the CLI runner.
     *
     * @param string $class  The job class to run (e.g., "LessonCreatedService")
     * @param string $method The method to invoke on the class
     * @param array  $params Array of parameters to pass to the method
     * @param int    $delay  Optional delay in seconds before the job is executed.
     *
     * @return \Symfony\Component\Process\Process The process instance (for monitoring, if needed)
     */
    function runBackgroundJob($class, $method, $params = [], $delay = 0)
    {
        // Convert the parameters array into a comma-separated string.
        $paramString = implode(',', $params);
        
        // Get the path to run-job.php (using Laravel's base_path() helper).
        $runJobPath = base_path('run-job.php');
        
        // Get the PHP executable using Symfony's PhpExecutableFinder.
        $phpBinary = (new \Symfony\Component\Process\PhpExecutableFinder())->find();
        
        // Build the command as an array. If a delay is provided, append it as the next argument.
        $command = [
            $phpBinary,
            $runJobPath,
            $class,
            $method,
            $paramString
        ];
        
        if ($delay > 0) {
            $command[] = $delay;
        }
        
        // Create a new Symfony Process instance with the command.
        $process = new \Symfony\Component\Process\Process($command);
        $process->setTimeout(0);
        $process->start();
        
        return $process;
    }
}
