<?php

return [
    'sample_rate' => (int) env('ANALYTICS_SAMPLE_RATE', 25), // % en prod
    'retention_days' => (int) env('ANALYTICS_RETENTION_DAYS', 90),
];
