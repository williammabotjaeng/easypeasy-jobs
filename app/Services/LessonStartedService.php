<?php

namespace App\Services;

class LessonStartedService
{
    public function handle($userId, $lessonId)
    {
        // Track student progress
        file_put_contents(__DIR__ . '/../../storage/logs/lesson_started.log', "User $userId started lesson $lessonId.\n", FILE_APPEND);

        // Validate prerequisites (pseudo-code)
        if (!LessonPrerequisiteService::validate($userId, $lessonId)) {
            throw new \Exception("User $userId hasn't completed required lessons.");
        }
    }
}
