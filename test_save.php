<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$row = Illuminate\Support\Facades\DB::table('cms_contents')->where('tab_key', 'students')->first();
$baseStudentsEncoded = $row->content ?? '';

$studentsInput = [
    'pages' => [
        'admissions' => [
            'links' => [
                'items' => [
                    [
                        'label' => 'Test Link',
                        'href' => 'http://test.com',
                        'description' => 'Test',
                        'category' => 'Applicants'
                    ]
                ]
            ]
        ]
    ]
];

$output = App\Support\StudentsCmsContent::encode(
    App\Support\StudentsCmsContent::fromPageInput('admissions', $studentsInput['pages']['admissions'], $baseStudentsEncoded)
);

$decoded = json_decode($output, true);
echo json_encode($decoded['pages']['admissions']['links']['items'] ?? null, JSON_PRETTY_PRINT);
