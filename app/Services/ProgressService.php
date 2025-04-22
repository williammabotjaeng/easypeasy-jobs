<?php

namespace App\Services;

class ProgressService
{
    /**
     * Update a user's progress for a given lesson.
     *
     * @param mixed $userId
     * @param mixed $lessonId
     */
    public static function updateProgress($userId, $lessonId)
    {
        // For demonstration, we simply log the progress update.
        $logFile = __DIR__ . '/../../storage/logs/progress.log';
        file_put_contents($logFile, "Progress updated for user $userId on lesson $lessonId.\n", FILE_APPEND);
    }
}
