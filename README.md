[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/thecodingmachine/laravel-server-monitor-plugin-notification-by-host/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/thecodingmachine/laravel-server-monitor-plugin-notification-by-host/?branch=master)
[![Build Status](https://travis-ci.org/thecodingmachine/laravel-server-monitor-plugin-notification-by-host.svg?branch=master)](https://travis-ci.org/thecodingmachine/laravel-server-monitor-plugin-notification-by-host)
[![Coverage Status](https://coveralls.io/repos/github/thecodingmachine/laravel-server-monitor-plugin-notification-by-host/badge.svg?branch=master)](https://coveralls.io/github/thecodingmachine/laravel-server-monitor-plugin-notification-by-host?branch=master)

# Plugin to Laravel server Monitor
This plugin is to use a different notification way by host for the [Laravel server monitor](https://github.com/spatie/laravel-server-monitor) developed by spatie


## Installation

You can install this package via composer using this command:

```bash
composer require spatie/laravel-server-monitor
```

Next, you must install the service provider:

```php
// config/app.php
'providers' => [
    ...
    TheCodingMachine\ServerMonitorPluginNotificationbyHost\ServerMonitorPluginNotificationByHostServiceProvider::class,
];
```

You must publish the config-file with:
```bash
php artisan vendor:publish --provider="TheCodingMachine\ServerMonitorPluginNotificationbyHost\ServerMonitorPluginNotificationByHostServiceProvider" --tag="config"
```

This is the contents of the published config file:

```php
return [
    'notifications' => [
        /* List of each channel you can be used */
        /* This contain the detail of parameter mandatory to use it */
        'channels' => 
			['mail' => 
				['to' => 'array']],
			['slack' => 
				['webhook_url' => 'string']],
			
    ]
];
```

To use the plugin, you must change the server-monitor.php with the next values:
```php
    ...

    'notifications' => [

        'notifications' => [
            TheCodingMachine\ServerMonitorPluginNotificationByHost\Notifications\Notifications\CheckSucceeded::class => [],
            TheCodingMachine\ServerMonitorPluginNotificationByHost\Notifications\Notifications\CheckRestored::class => ['slack'],
            TheCodingMachine\ServerMonitorPluginNotificationByHost\Notifications\Notifications\CheckWarning::class => ['slack'],
            TheCodingMachine\ServerMonitorPluginNotificationByHost\Notifications\Notifications\CheckFailed::class => ['slack'],
        ],
        ...
        'notifiable' => TheCodingMachine\ServerMonitorPluginNotificationByHost\Notifications\Notifiable::class,
        ...
    ]
    ...
```

## Use it

By default, if no custom configuration was configured, this is the global parameters set in server-monitor.php which will used.
You can change the configuration by host (mail receiver, slack channel ...), and the channel by error type by host.

To apply this, there is 2 new commands add to artisan:
- Add notification for a specific host: php artisan server-monitor:add-notification-host
- List all notification by host: php artisan server-monitor:list-notifications
