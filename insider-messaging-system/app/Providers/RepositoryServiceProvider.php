<?php

namespace App\Providers;

use App\Repositories\Contracts\MessageLogRepositoryInterface;
use App\Repositories\Contracts\MessageRepositoryInterface;
use App\Repositories\MessageLogRepository;
use App\Repositories\MessageRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(MessageRepositoryInterface::class, MessageRepository::class);
        $this->app->bind(MessageLogRepositoryInterface::class, MessageLogRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
