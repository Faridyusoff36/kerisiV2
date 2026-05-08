<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

foreach (['building_main'] as $t) {
    echo "=== $t ===\n";
    $r = Illuminate\Support\Facades\DB::connection('mysql_secondary')->select('SHOW COLUMNS FROM '.$t);
    foreach ($r as $col) {
        echo $col->Field."\n";
    }
}
