<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$ctrl = $app->make(App\Http\Controllers\Api\AssetDamageApplicationController::class);
$res = $ctrl->show(1);
echo $res->getContent()."\n";
