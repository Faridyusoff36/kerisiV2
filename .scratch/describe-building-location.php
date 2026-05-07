<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    $r = Illuminate\Support\Facades\DB::connection('mysql_secondary')->select('SHOW FULL COLUMNS FROM building_location');
    foreach ($r as $col) {
        echo $col->Field.' '.$col->Type."\n";
    }
} catch (Throwable $e) {
    echo 'ERR: '.$e->getMessage();
}
