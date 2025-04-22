<?php

namespace App\Services;

class LessonEndedService
{
    public function handle($userId, $lessonId)
    {
        // Log completion
        file_put_contents(__DIR__ . '/../../storage/logs/lesson_ended.log', "User $userId completed lesson $lessonId.\n", FILE_APPEND);

        // Update progress tracker
        ProgressService::updateProgress($userId, $lessonId);
        
        // Send certificate
        CertificateService::generate($userId, $lessonId);
    }
}
