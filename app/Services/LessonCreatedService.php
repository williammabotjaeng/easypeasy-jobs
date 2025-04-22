<?php

namespace App\Services;

class LessonCreatedService
{
    public function handle($lessonData)
    {
        // If a non-array is passed, assume it's the lesson title.
        if (!is_array($lessonData)) {
            $lessonData = ['title' => $lessonData];
        }

        // Log the lesson creation (logs the JSON representation).
        file_put_contents(__DIR__ . '/../../storage/logs/lesson_created.log', json_encode($lessonData) . "\n", FILE_APPEND);

        // Send a notification to enrolled students.
        NotificationService::send("New lesson created: " . $lessonData['title']);
    }
}
