<?php

return [
    /*
    |--------------------------------------------------------------------------
    | POS Configuration
    |--------------------------------------------------------------------------
    |
    | Point of Sale system configuration including printer settings
    |
    */

    'kitchen_printer_ip' => env('KITCHEN_PRINTER_IP', '192.168.1.100'),
    'kitchen_printer_port' => env('KITCHEN_PRINTER_PORT', 9100),
    'receipt_printer_ip' => env('RECEIPT_PRINTER_IP', '192.168.1.101'),
    'receipt_printer_port' => env('RECEIPT_PRINTER_PORT', 9100),
    
    // Printer settings
    'printer_settings' => [
        'character_per_line' => 42,
        'line_spacing' => 30,
        'font_size' => 'normal', // small, normal, large
        'auto_cut' => true,
        'drawer_pulse' => 100, // milliseconds
    ],
    
    // KOT settings
    'kot' => [
        'auto_print' => env('KOT_AUTO_PRINT', true),
        'print_void_notices' => env('KOT_PRINT_VOID_NOTICES', true),
        'duplicate_copies' => env('KOT_DUPLICATE_COPIES', 1),
    ],
    
    // Receipt settings
    'receipt' => [
        'header_text' => env('RECEIPT_HEADER', 'RESTAURANT NAME'),
        'footer_text' => env('RECEIPT_FOOTER', 'Thank you for visiting!'),
        'show_customer_info' => true,
        'show_order_details' => true,
        'show_payment_details' => true,
    ],
];
