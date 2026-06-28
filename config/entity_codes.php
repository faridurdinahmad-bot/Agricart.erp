<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Entity code prefixes
    |--------------------------------------------------------------------------
    |
    | Agricart uses simple sequential codes: {PREFIX}-{NUMBER}
    | Examples: CAT-1, BR-87, PRD-45821
    |
    | Numbers are monotonic and never reused after deletion.
    |
    */

    'prefixes' => [
        'category' => 'CAT',
        'brand' => 'BR',
        'product' => 'PRD',
        'supplier' => 'SUP',
        'customer' => 'CUS',
    ],

];
