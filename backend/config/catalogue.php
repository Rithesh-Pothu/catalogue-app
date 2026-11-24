<?php

return [

    /*
    | The products shown as featured, in this order. Change them with the
    | FEATURED_PRODUCT_IDS environment variable (comma separated ids).
    */
    'featured_product_ids' => array_map(
        'intval',
        array_filter(explode(',', env('FEATURED_PRODUCT_IDS', '3,5,11,12,18')), 'strlen'),
    ),

    /*
    | The Fake Store API, the current product source (ADR 0001). Only
    | App\Catalogue\FakeStoreProductSource reads these.
    */
    'fake_store' => [
        'url' => env('FAKE_STORE_URL', 'https://fakestoreapi.com'),
        'timeout' => (int) env('FAKE_STORE_TIMEOUT', 5),
    ],

];
