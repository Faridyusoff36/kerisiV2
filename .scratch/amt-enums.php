<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

foreach (['amt_status', 'amt_maintenance_type'] as $col) {
    echo "=== $col ===\n";
    $rows = Illuminate\Support\Facades\DB::connection('mysql_secondary')
        ->select("SELECT $col AS v, COUNT(*) AS c FROM asset_maintenance_master GROUP BY $col ORDER BY c DESC LIMIT 30");
    foreach ($rows as $r) {
        echo ($r->v ?? 'NULL')." => ".$r->c."\n";
    }
}
