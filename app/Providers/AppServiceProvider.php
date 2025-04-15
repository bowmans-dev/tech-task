<?php

namespace App\Providers;

use App\Domains\Core\Repositories\UserRepositoryInterface;
use App\Domains\Supporting\ImageUpload\ImageService;
use App\Infrastructures\Persistence\Repositories\EloquentUserRepository;
use App\Services\UserService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register the UserService
        $this->app->singleton(UserService::class, function ($app) {
            return new UserService($app->make(ImageService::class)); // Pass ImageService to UserService
        });

        $this->app->singleton(ImageService::class, function ($app) {
            return new ImageService;
        });

        // Bind the interface to the concrete implementation
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
