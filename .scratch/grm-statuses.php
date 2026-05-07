<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$rows = Illuminate\Support\Facades\DB::connection('mysql_secondary')
    ->select('SELECT grm_status, COUNT(*) AS c FROM goods_receive_master GROUP BY grm_status ORDER BY c DESC LIMIT 40');
foreach ($rows as $r) {
    echo $r->grm_status." => ".$r->c."\n";
}
