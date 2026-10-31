<?php

declare(strict_types=1);

return [

    // The application dispatches no background work, so the sync driver runs
    // listeners inline and no jobs or failed_jobs table is needed.
    'default' => env('QUEUE_CONNECTION', 'sync'),

    'connections' => [

        'sync' => [
            'driver' => 'sync',
        ],

    ],

    'batching' => [
        'database' => env('DB_CONNECTION', 'mysql'),
        'table' => 'job_batches',
    ],

    'failed' => [
        'driver' => env('QUEUE_FAILED_DRIVER', 'null'),
    ],

];
