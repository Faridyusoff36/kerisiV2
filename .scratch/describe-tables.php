<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

foreach (['room_main', 'organization_unit', 'goods_receive_master', 'asset_damage_master', 'asset_maintenance_master', 'asset_schedule_maintenance'] as $t) {
    echo "=== $t ===\n";
    try {
        $r = Illuminate\Support\Facades\DB::connection('mysql_secondary')->select('SHOW COLUMNS FROM '.$t);
        foreach ($r as $col) {
            echo $col->Field."\n";
        }
    } catch (Throwable $e) {
        echo 'ERR: '.$e->getMessage()."\n";
    }
}
