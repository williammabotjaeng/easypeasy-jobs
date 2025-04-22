<?php

namespace App\Services;

class LessonEndedService
{
    public function handle($userId, $lessonId)
    {
        // Log lesson completion
        $logFile = __DIR__ . '/../../storage/logs/lesson_ended.log';
        file_put_contents($logFile, "User $userId completed lesson $lessonId.\n", FILE_APPEND);

        // Update progress tracker
        \App\Services\ProgressService::updateProgress($userId, $lessonId);
        
        // Send certificate to the user
        \App\Services\CertificateService::generate($userId, $lessonId);
    }
}
