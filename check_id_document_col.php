<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Schema;

$col = collect(Schema::getColumns('users'))->firstWhere('name', 'id_document');
echo "id_document column:\n";
echo "  Type: " . $col['type_name'] . "\n";
echo "  Nullable: " . ($col['nullable'] ? 'YES' : 'NO') . "\n";
