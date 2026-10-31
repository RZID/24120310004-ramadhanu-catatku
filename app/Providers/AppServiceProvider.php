<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // A lazily loaded relation outside production is an N+1 bug; fail loudly
        // instead of letting it reach the dashboard.
        Model::preventLazyLoading(! $this->app->isProduction());
    }
}
