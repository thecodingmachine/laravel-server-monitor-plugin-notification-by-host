<?php

namespace TheCodingMachine\ServerMonitorPluginNotificationbyHost;

use Spatie\Blink\Blink;
use Illuminate\Support\ServiceProvider;
use Spatie\ServerMonitor\Commands\ListNotifications;
use TheCodingMachine\ServerMonitorPluginNotificationByHost\Commands\AddNotificationByHost;

class ServerMonitorPluginNotificationByHostServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/server-monitor-plugin-notification-by-host.php' => config_path('server-monitor-plugin-notification-by-host.php'),
            ], 'config');

        }
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/server-monitor-plugin-notification-by-host.php', 'server-monitor-plugin-notification-by-host');

        $this->app->bind('command.server-monitor:add-notification-host', AddNotificationByHost::class);
        $this->app->bind('command.server-monitor:list-notifications', ListNotifications::class);

        $this->commands([
            'command.server-monitor:add-notification-host',
            'command.server-monitor:list-notifications',
        ]);
    }
}
