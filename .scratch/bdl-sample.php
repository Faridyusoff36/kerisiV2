<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$rows = Illuminate\Support\Facades\DB::connection('mysql_secondary')
    ->select('SELECT bdl_status, COUNT(*) c FROM building_location GROUP BY bdl_status');
foreach ($rows as $r) {
    echo json_encode($r->bdl_status)." => ".$r->c."\n";
}
$one = Illuminate\Support\Facades\DB::connection('mysql_secondary')->table('building_location')->first();
var_export($one ? (array) $one : null);
