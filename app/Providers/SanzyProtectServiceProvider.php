<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class SanzyProtectServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Load routes
        if (file_exists(__DIR__ . '/../../routes/sanzy-protect.php')) {
            $this->loadRoutesFrom(__DIR__ . '/../../routes/sanzy-protect.php');
        }
        
        // Load views
        if (is_dir(__DIR__ . '/../../resources/views')) {
            $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'sanzy-protect');
        }
        
        // Load config
        if (file_exists(__DIR__ . '/../../config/sanzy-protect.php')) {
            $this->mergeConfigFrom(__DIR__ . '/../../config/sanzy-protect.php', 'sanzy-protect');
        }
    }
}
