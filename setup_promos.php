<?php
require_once __DIR__ . '/src/Db.php';
require_once __DIR__ . '/src/DiscountService.php';

// Force display errors
ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
    echo "Setting up promos...\n";
    
    // 1. BLACKFRIDAY - 25% Off, Expires Dec 5th
    try {
        $id = DiscountService::create([
            'code' => 'BLACKFRIDAY',
            'type' => 'percent',
            'value' => 25.00,
            'expires_at' => '2025-12-05 23:59:59'
        ]);
        echo "Created BLACKFRIDAY code (ID: $id)\n";
    } catch (Exception $e) {
        echo "Note on BLACKFRIDAY: " . $e->getMessage() . "\n";
    }

    // 2. CYBERMONDAY - 25% Off, Expires Dec 5th
    try {
        $id = DiscountService::create([
            'code' => 'CYBERMONDAY',
            'type' => 'percent',
            'value' => 25.00,
            'expires_at' => '2025-12-05 23:59:59'
        ]);
        echo "Created CYBERMONDAY code (ID: $id)\n";
    } catch (Exception $e) {
        echo "Note on CYBERMONDAY: " . $e->getMessage() . "\n";
    }
    
    echo "Done.\n";

} catch (Exception $e) {
    echo "Fatal Error: " . $e->getMessage() . "\n";
}

