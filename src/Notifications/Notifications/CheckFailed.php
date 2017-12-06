<?php
namespace TheCodingMachine\ServerMonitorPluginNotificationByHost\Notifications\Notifications;

use TheCodingMachine\ServerMonitorPluginNotificationByHost\Notifications\BaseNotificationTrait;

class CheckFailed extends \Spatie\ServerMonitor\Notifications\Notifications\CheckFailed
{
    use BaseNotificationTrait;
}
