<?php

namespace App\Services;

class CertificateService
{
    /**
     * Generate a certificate for a completed lesson.
     *
     * @param mixed $userId
     * @param mixed $lessonId
     */
    public static function generate($userId, $lessonId)
    {
        // For demonstration, we simply log the certificate generation.
        $logFile = __DIR__ . '/../../storage/logs/certificate.log';
        file_put_contents($logFile, "Certificate generated for user $userId for lesson $lessonId.\n", FILE_APPEND);
    }
}
