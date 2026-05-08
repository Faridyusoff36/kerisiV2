<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$conn = Illuminate\Support\Facades\DB::connection('mysql_secondary');

foreach (['asset_damage_master', 'asset_damage_detail', 'asset_damage_line', 'asset_damage_trans'] as $t) {
    echo "=== {$t} ===\n";
    try {
        $r = $conn->select('SHOW COLUMNS FROM '.$t);
        foreach ($r as $c) {
            echo $c->Field."\n";
        }
    } catch (Throwable $e) {
        echo 'ERR: '.$e->getMessage()."\n";
    }
}

echo "=== TABLES %damage% ===\n";
$tbls = $conn->select("SHOW TABLES LIKE '%damage%'");
foreach ($tbls as $row) {
    echo json_encode($row)."\n";
}

echo "=== asset_damage_details columns ===\n";
try {
    $r = $conn->select('SHOW COLUMNS FROM asset_damage_details');
    foreach ($r as $c) {
        echo $c->Field."\n";
    }
} catch (Throwable $e) {
    echo 'ERR: '.$e->getMessage()."\n";
}
