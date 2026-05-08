<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$conn = Illuminate\Support\Facades\DB::connection('mysql_secondary');
foreach ($conn->select('SHOW COLUMNS FROM wf_application_status') as $c) {
    echo $c->Field."\n";
}
