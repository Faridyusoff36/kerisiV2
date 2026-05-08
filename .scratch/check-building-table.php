<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$conn = Illuminate\Support\Facades\DB::connection('mysql_secondary');
foreach (['building_main', 'building_master', 'bdm_main'] as $t) {
    try {
        $n = $conn->selectOne("SELECT COUNT(*) AS c FROM {$t}");
        echo "{$t}: ok rows ".($n->c ?? '?')."\n";
    } catch (Throwable $e) {
        echo "{$t}: missing\n";
    }
}
