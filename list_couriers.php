<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$couriers = \App\Models\User::where('account_type', 'logistics')->get();

echo "Logistics/Courier Users:\n";
echo str_repeat("=", 100) . "\n";

foreach ($couriers as $c) {
    echo sprintf(
        "ID: %-3s | Email: %-40s | Company: %-30s | Status: %s\n",
        $c->id,
        $c->email,
        $c->company_name ?? $c->business_name ?? 'N/A',
        $c->status
    );
}
