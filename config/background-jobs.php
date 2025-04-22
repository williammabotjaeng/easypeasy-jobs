<?php

use App\Services;

return [
    'allowed_jobs' => [
        'LessonCreatedService' => ['handle'],
        'LessonStartedService' => ['handle'],
        'LessonEndedService' => ['handle'],
        'UserLoginService' => ['handle'],
        'UserLogoutService' => ['handle'],
    ],
    'retry_attempts' => 3, 
    'retry_delay_seconds' => 5,
    'max_execution_time' => 300, 
    'job_priority' => [
        'LessonCreatedService' => 'high',
        'LessonStartedService' => 'high',
        'LessonEndedService' => 'medium',
        'UserLoginService' => 'low',
        'UserLogoutService' => 'low',
    ],
];
