<?php

namespace App\Support;

class ErrorPageContent
{
    /**
     * @return array{headline:string,message:string}
     */
    public static function forStatus(int $status): array
    {
        return match ($status) {
            401 => [
                'headline' => 'Authentication Required',
                'message' => 'You need to sign in to access this page.',
            ],
            403 => [
                'headline' => 'Access Restricted',
                'message' => 'You do not have permission to access this page.',
            ],
            404 => [
                'headline' => 'Page Not Found',
                'message' => 'Page not found.',
            ],
            419 => [
                'headline' => 'Session Expired',
                'message' => 'Your session has expired. Please log in again.',
            ],
            429 => [
                'headline' => 'Too Many Requests',
                'message' => 'Too many requests. Please wait a moment and try again.',
            ],
            500 => [
                'headline' => 'Something Went Wrong',
                'message' => 'Something went wrong on our side. Please try again later.',
            ],
            503 => [
                'headline' => 'Service Unavailable',
                'message' => 'This service is temporarily unavailable. Please try again later.',
            ],
            default => $status >= 500
                ? [
                    'headline' => 'Something Went Wrong',
                    'message' => 'Something went wrong on our side. Please try again later.',
                ]
                : [
                    'headline' => 'Request Unavailable',
                    'message' => 'We could not complete your request. Please try again.',
                ],
        };
    }
}
