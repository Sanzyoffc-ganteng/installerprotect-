<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Protection Level
    |--------------------------------------------------------------------------
    |
    | Set the protection level from 0-9. Higher levels include all lower 
    | level protections. Recommended: 9 for production.
    |
    */
    'level' => env('SANZY_PROTECT_LEVEL', 9),
    
    /*
    |--------------------------------------------------------------------------
    | Main Admin IDs
    |--------------------------------------------------------------------------
    |
    | Array of admin user IDs that should be protected from modification.
    | These admins bypass all protection checks.
    |
    */
    'main_admin_ids' => explode(',', env('SANZY_MAIN_ADMINS', '1')),
    
    /*
    |--------------------------------------------------------------------------
    | Cache TTL
    |--------------------------------------------------------------------------
    |
    | Time-to-live for cached data (server ownership, whitelist) in seconds.
    |
    */
    'cache_ttl' => 300,
];
