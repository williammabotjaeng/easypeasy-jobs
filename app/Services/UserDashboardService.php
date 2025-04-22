<?php

namespace App\Services;

class UserDashboardService
{
    /**
     * Customize the dashboard for the given user.
     *
     * @param mixed $userId
     * @return void
     */
    public static function customize($userId)
    {
        $logFile = __DIR__ . '/../../storage/logs/dashboard.log';
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($logFile, "Dashboard customized for user $userId at $timestamp.\n", FILE_APPEND);
    }

    /**
     * Reset the dashboard for the given user (optional).
     *
     * @param mixed $userId
     * @return void
     */
    public static function reset($userId)
    {
        $logFile = __DIR__ . '/../../storage/logs/dashboard.log';
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($logFile, "Dashboard reset for user $userId at $timestamp.\n", FILE_APPEND);
    }
}
