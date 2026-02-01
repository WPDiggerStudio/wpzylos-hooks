<?php

declare(strict_types=1);

namespace WPZylos\Framework\Hooks;

use WPZylos\Framework\Core\Contracts\ApplicationInterface;
use WPZylos\Framework\Core\ServiceProvider;

/**
 * Hook service provider.
 *
 * Registers the HookManager with the container.
 *
 * @package WPZylos\Framework\Hooks
 */
class HookServiceProvider extends ServiceProvider
{
    /**
     * {@inheritDoc}
     */
    public function register(ApplicationInterface $app): void
    {
        parent::register($app);

        $this->singleton(HookManager::class, function () use ($app) {
            return new HookManager($app->context());
        });

        $this->singleton('hooks', fn() => $this->make(HookManager::class));
    }
}
