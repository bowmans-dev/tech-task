<?php

namespace App\Providers;

use App\Domains\Core\Repositories\UserRepositoryInterface;
use App\Domains\Supporting\ImageUpload\ImageService;
use App\Infrastructures\Persistence\Repositories\EloquentUserRepository;
use App\Domains\Core\Services\UserService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {

        $this->app->singleton(UserService::class, function ($app) {
            return new UserService(
                $app->make(ImageService::class), 
                $app->make(UserRepositoryInterface::class) 
            );
        });

        $this->app->singleton(ImageService::class, function ($app) {
            return new ImageService;
        });

        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
    }

    public function boot(): void
    {
        
    }
}