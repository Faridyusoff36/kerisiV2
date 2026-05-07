<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$rows = Illuminate\Support\Facades\DB::connection('mysql_secondary')
    ->select('SELECT drm_status, COUNT(*) AS c FROM asset_damage_master GROUP BY drm_status ORDER BY c DESC LIMIT 40');
foreach ($rows as $r) {
    echo ($r->drm_status ?? '')." => ".$r->c."\n";
}
