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
    | How long product data from the source is reused before the source is
    | read again: five minutes. A failed read is never kept.
    */
    'cache_seconds' => (int) env('CATALOGUE_CACHE_SECONDS', 300),

    /*
    | The sales inbox: the one mailbox that receives every enquiry. Locally it
    | is read in Mailpit at http://localhost:8025.
    */
    'sales_inbox' => env('SALES_INBOX_EMAIL', 'sales@catalogue.test'),

    /*
    | The Fake Store API, the current product source (ADR 0001). Only
    | App\Catalogue\FakeStoreProductSource reads these.
    */
    'fake_store' => [
        'url' => env('FAKE_STORE_URL', 'https://fakestoreapi.com'),
        'timeout' => (int) env('FAKE_STORE_TIMEOUT', 5),
    ],

];
