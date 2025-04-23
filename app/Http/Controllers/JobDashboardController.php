<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class JobDashboardController extends Controller
{
    /**
     * Display the dashboard with job logs, error logs, and running jobs.
     */
    public function index()
    {
        // Get the paths to our logs.
        $jobLogPath = storage_path('logs/background_jobs.log');
        $errorLogPath = storage_path('logs/background_jobs_errors.log');
        
        // Read the log files if they exist.
        $jobLogs = file_exists($jobLogPath) ? file($jobLogPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
        $errorLogs = file_exists($errorLogPath) ? file($errorLogPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
        
        // Scan the jobs directory for lock files (indicating running jobs).
        $jobsDir = storage_path('jobs');
        $runningJobs = [];
        if (File::exists($jobsDir) && File::isDirectory($jobsDir)) {
            $files = File::files($jobsDir);
            foreach ($files as $file) {
                if ($file->getExtension() === 'lock') {
                    $runningJobs[] = [
                        'filename'   => $file->getFilename(),
                        // For now, we hard-code retry count as 0. (This could be extended in the future.)
                        'retryCount' => 0,
                    ];
                }
            }
        }
        
        return view('dashboard.jobs', compact('jobLogs', 'errorLogs', 'runningJobs'));
    }

    /**
     * Cancel a running job by deleting its lock file.
     */
    public function cancel(Request $request)
    {
        $lockFile = $request->input('lock_file');
        $jobsDir = storage_path('jobs');
        $filePath = $jobsDir . DIRECTORY_SEPARATOR . $lockFile;
        
        if (file_exists($filePath)) {
            // For safety, you might want to validate here that the file is indeed a lock file.
            unlink($filePath);
            return redirect()->back()->with('success', "Job cancelled: $lockFile");
        }
        
        return redirect()->back()->with('error', "Lock file not found: $lockFile");
    }
}
