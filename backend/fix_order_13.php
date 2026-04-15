<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$svc = app(App\Services\MercadoPagoOrderService::class);
$svc->processPaymentNotification(["type"=>"payment","action"=>"payment.created","data"=>["id"=>"154588487376"]]);
$o = App\Models\Order::find(13);
echo "order 13 payment_status: {$o->payment_status}, status: {$o->status}\n";
