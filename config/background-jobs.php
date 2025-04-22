<?php

return [
    'allowed_jobs' => [
        'App\Services\LessonCreatedService' => ['handle'],
        'App\Services\LessonStartedService' => ['handle'],
        'App\Services\LessonEndedService' => ['handle'],
        'App\Services\UserLoginService' => ['handle'],
    ],
    'retry_attempts' => 3, 
    'retry_delay_seconds' => 5,
    'max_execution_time' => 300, 
    'job_priority' => [
        'LessonCreatedService' => 'high',
        'LessonStartedService' => 'high',
        'LessonEndedService' => 'medium',
        'UserLoginService' => 'low',
    ],
];
