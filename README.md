# Laravel Custom Background Job Runner

This project implements a custom background job runner for Laravel that executes PHP classes as background jobs without using Laravel's built-in queue system. It emphasizes scalability, error handling, cross-platform compatibility, and controlled execution of pre-approved job classes and methods.

## Table of Contents

- [Introduction](#introduction)
- [Installation & Setup](#installation--setup)
- [Usage](#usage)
  - [CLI Execution](#cli-execution)
  - [Global Helper Function](#global-helper-function)
- [Configuration](#configuration)
  - [Allowed Jobs](#allowed-jobs)
  - [Retry Attempts and Delays](#retry-attempts-and-delays)
- [Examples](#examples)
- [Advanced Features](#advanced-features)
- [Documentation & Support](#documentation--support)

## Introduction

This project demonstrates a custom background job runner that:
- Accepts a job class, method, and parameters via CLI (e.g., `php run-job.php ClassName methodName "param1,param2"`).
- Logs each job execution with a timestamp, class, method, and execution status (success or failure).
- Provides robust error handling (logging errors to `storage/logs/background_jobs_errors.log`).
- Implements a configurable retry mechanism.
- Uses a global helper function `runBackgroundJob()` to trigger the background job runner from within Laravel.

## Installation & Setup

1. **Clone the Repository:**  
   ```bash
   git clone https://github.com/williammabotjaeng/easypeasy-jobs.git
   cd laravel-background-job-runner
   ```

2. **Install Dependencies:**  
   Make sure you have [Composer](https://getcomposer.org) installed, then run:
   ```bash
   composer install
   ```

3. **Autoload Global Helpers:**  
   To make the global helper `runBackgroundJob()` available throughout your Laravel app, add the following to your composer.json:
   ```json
   "autoload": {
       "files": [
           "app/helpers.php"
       ]
   }
   ```
   Then run:
   ```bash
   composer dump-autoload
   ```

4. **Configure the Job Runner:**  
   Edit the configuration file located at `config/background-jobs.php` to register allowed jobs and set retry options (details below).

## Usage

### CLI Execution

You can run background jobs directly from the command line by providing:
- The (short) class name (without namespace)
- The method name
- A comma-separated list of parameters

For example:
```bash
php run-job.php LessonCreatedService handle "English Grammar 101"
```
This command will trigger the `handle` method on `App\Services\LessonCreatedService` (the runner automatically prepends the default namespace `App\Services\`).

### Global Helper Function

The global helper function `runBackgroundJob($class, $method, $params = [])` allows you to trigger background jobs from anywhere in your Laravel application. It uses Symfony Process to execute the CLI runner script, ensuring compatibility with both Windows and Unix-based systems.

**Example Usage:**
```php
// In your controller, service, or route closure
runBackgroundJob('LessonCreatedService', 'handle', ['English Grammar 101']);
runBackgroundJob('UserLoginService', 'handle', ['user123']);
runBackgroundJob('UserLogoutService', 'handle', ['user123']);
```

This function builds the command and calls `run-job.php` in the background, allowing your application to offload processing without blocking the main thread.

## Configuration

The configuration file is located at `config/background-jobs.php`. Below is an example:

```php
<?php

return [
    // Only these classes & methods can be executed by the job runner.
    'allowed_jobs' => [
        'LessonCreatedService'   => ['handle'],
        'LessonStartedService'   => ['handle'],
        'LessonEndedService'     => ['handle'],
        'UserLoginService'       => ['handle'],
        'UserLogoutService'      => ['handle'],
    ],
    
    // Retry configuration: number of retry attempts and delay between retries (in seconds)
    'retry_attempts'       => 3,
    'retry_delay_seconds'  => 5,
    
    // Maximum allowed execution time for each job (in seconds)
    'max_execution_time'   => 300,
    
    // Optional: Job priorities (if you choose to implement them)
    'job_priority' => [
        'LessonCreatedService'   => 'high',
        'LessonStartedService'   => 'high',
        'LessonEndedService'     => 'medium',
        'UserLoginService'       => 'low',
        'UserLogoutService'      => 'low',
    ],
];
```

### Allowed Jobs

The `allowed_jobs` array defines which job classes (using their short names) and methods are permitted for execution. To add a new job, simply add a new key-value pair, for example:

```php
'allowed_jobs' => [
    // Existing services...
    'NewService' => ['handle', 'process'],   // Allow multiple methods if needed.
],
```

### Retry Attempts and Delays

- **`retry_attempts`:**  
  Define the maximum number of times the job runner should retry a failed job.
  
- **`retry_delay_seconds`:**  
  Define the delay (in seconds) between retry attempts.

These values are used by the job runner and may be applied in the future for enhanced error handling (e.g., requeuing jobs if they fail).

## Examples

### Example 1: Triggering Lesson Creation

Using the global helper:
```php
runBackgroundJob('LessonCreatedService', 'handle', ['English Grammar 101']);
```
This will log the job execution in `storage/logs/background_jobs.log` and record the lesson creation details in the dedicated log file.

### Example 2: Triggering User Login

```php
runBackgroundJob('UserLoginService', 'handle', ['user123']);
```
This job logs the user login time and customizes the dashboard via `UserDashboardService`.

### Example 3: Triggering User Logout

```php
runBackgroundJob('UserLogoutService', 'handle', ['user123']);
```
This job logs the user logout time and optionally resets the dashboard.

## Advanced Features

- **Web-Based Dashboard:** A Laravel web interface to visualize job executions, statuses, and error logs.
- **Job Delays and Priorities:** Implement delayed execution and priority queuing.
- **Chained Jobs:** Triggering multiple jobs sequentially, based on the output of a previous job.
- **Exception-based Retry Logic:** Different handling strategies for different types of exceptions.

Below is an example addition to your documentation that details how the dashboard interface works, how to run it, and how delayed jobs function:

---

## Advanced Features

- **Web-Based Dashboard:** A Laravel web interface to visualize job executions, statuses, and error logs.
- **Job Delays and Priorities:** Implement delayed execution and priority queuing.
- **Chained Jobs:** Triggering multiple jobs sequentially, based on the output of a previous job.
- **Exception-based Retry Logic:** Different handling strategies for different types of exceptions.

### Dashboard Interface Documentation

The dashboard interface is built into your Laravel application and provides a centralized view of your background jobs. It comprises several sections, each serving a specific purpose:

#### Components

1. **Running Jobs:**  
   - **Functionality:**  
     The dashboard scans the `storage/jobs` directory for lock files (e.g., `LessonCreatedService_handle.lock`) that indicate which jobs are currently in progress.  
   - **Displayed Information:**  
     Each entry shows the lock file name and a placeholder for the retry count (currently set to 0), along with an action button to cancel the running job.
   - **Cancel Action:**  
     Pressing the "Cancel Job" button sends a POST request to a dedicated cancellation route. This route deletes the lock file, effectively marking the job as canceled.  
   - **Usage:**  
     To check running jobs, navigate to your job dashboard at:  
     ```

     http://localhost:8000 This should redirect to /dashboard/jobs

     http://localhost:8000/dashboard/jobs

     ```

2. **Job Execution Log:**  
   - **Functionality:**  
     This section displays the job execution log generated in the `storage/logs/background_jobs.log` file.  
   - **Displayed Information:**  
     Each log entry includes a timestamp, the job class, method, and the status (e.g., INFO, SUCCESS, ERROR) detailing the job's progress or completion.
  
3. **Error Log:**  
   - **Functionality:**  
     Errors encountered during job execution are logged in `storage/logs/background_jobs_errors.log` and displayed in this section.  
   - **Displayed Information:**  
     You’ll see detailed error messages that can help you debug issues with job execution.

#### Running the Dashboard

1. **Access:**  
   - Start your Laravel development server (e.g., using `php artisan serve`).
   - Navigate to the home route ("/") or directly to `/dashboard/jobs`.  
     *Note: The home route automatically redirects to `/dashboard/jobs` if you have set this in your routes file.*

2. **Usage:**  
   - View running jobs and use the cancellation form for jobs that are currently in progress.
   - Scroll through the job execution and error logs to monitor system behavior.

---

### Delayed Jobs Documentation

Delayed job execution allows you to specify a delay (in seconds) before a job is processed. This is particularly useful when a job must wait for a given time period (for example, to allow a transaction to complete or to perform scheduled tasks).

#### How It Works

- **Command-Line Usage:** When launching a job from the CLI, you can provide an additional parameter to set the delay. For instance:
  ```bash
  php run-job.php LessonCreatedService handle "English Grammar 101" 25
  ```
  In this example, the job execution is delayed by 25 seconds before the job is started.

  This is ideal for testing running jobs functionality on the Dashboard.

- **Global Helper Function:**  
  The global helper function `runBackgroundJob()` has been enhanced to support a delay parameter.  
  **Example:**
  ```php
  runBackgroundJob('LessonCreatedService', 'handle', ['English Grammar 101'], 10);
  ```
  This call schedules the job to be executed with a 10-second delay.

#### Implementation Details

- **Delay Mechanism:**  
  In `run-job.php`, if a delay is provided, the script logs an informational message and then uses PHP’s `sleep()` function to pause execution for the specified number of seconds before proceeding with the job.

- **Configuration:**  
  You can optionally adjust or extend delay settings in the `config/background-jobs.php` configuration file. For example, you might add a default delay or adjust execution-related settings based on job priorities.

---

### Summary

- **Dashboard Interface:** Offers real-time visualization of running jobs (via lock files), comprehensive job execution logs, error logs, and job management actions (like cancellation).  
- **Delayed Jobs:** Enhance scheduling capabilities by allowing jobs to wait a specified number of seconds before execution. This is supported both when using the CLI runner and via the global helper function.


## Documentation & Support

For further details:
- **Project Issues:** Please open an issue on the GitHub repository if you encounter any problems.
- **Contribution Guidelines:** Refer to the `CONTRIBUTING.md` file for guidelines on submitting improvements.
- **License:** This project is licensed under the MIT License.

Happy background processing!
```
