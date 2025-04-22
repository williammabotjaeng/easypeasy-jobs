<?php

namespace App\Services;

class UserLoginService
{
    public function handle($userId)
    {
        // Log the login time with a formatted timestamp
        $logFile = __DIR__ . '/../../storage/logs/user_logins.log';
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($logFile, "User $userId logged in at $timestamp.\n", FILE_APPEND);

        // Personalize the dashboard experience (ensure UserDashboardService exists and is autoloaded)
        \App\Services\UserDashboardService::customize($userId);
    }
}
