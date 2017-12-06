<?php

namespace TheCodingMachine\ServerMonitorPluginNotificationbyHost;

use Spatie\Blink\Blink;
use Illuminate\Support\ServiceProvider;
use TheCodingMachine\ServerMonitorPluginNotificationByHost\Commands\AddNotificationByHost;
use TheCodingMachine\ServerMonitorPluginNotificationByHost\Commands\ListNotifications;

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
        $this->app->bind('command.server-monitor:list-notification-host', ListNotifications::class);

        $this->commands([
            'command.server-monitor:add-notification-host',
            'command.server-monitor:list-notification-host',
        ]);
    }
}
