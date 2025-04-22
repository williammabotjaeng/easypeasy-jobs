<?php

namespace App\Services;

class NotificationService
{
    /**
     * A simple static method to simulate sending notifications.
     * In a real application, you might integrate with email, SMS, or push notification APIs.
     *
     * @param string $message The notification message.
     * @return void
     */
    public static function send($message)
    {
        // For demonstration, we log the notification to a file.
        file_put_contents(__DIR__ . '/../../storage/logs/notification.log', $message . "\n", FILE_APPEND);
    }
}
