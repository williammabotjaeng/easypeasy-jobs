<?php

namespace App\Services;

class UserLoginService
{
    public function handle($userId)
    {
        // Log login time
        file_put_contents(__DIR__ . '/../../storage/logs/user_logins.log', "User $userId logged in at " . now() . ".\n", FILE_APPEND);

        // Personalize dashboard experience
        UserDashboardService::customize($userId);
    }
}
