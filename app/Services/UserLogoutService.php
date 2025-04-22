<?php

namespace App\Services;

class UserLogoutService
{
    public function handle($userId)
    {
        // Log the logout time with a formatted timestamp
        $logFile = __DIR__ . '/../../storage/logs/user_logouts.log';
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($logFile, "User $userId logged out at $timestamp.\n", FILE_APPEND);

        // Optionally perform additional tasks on logout.
        // For example, you might want to reset the dashboard.
        // \App\Services\UserDashboardService::reset($userId);
        // For now, we just record the logout event.
    }
}
