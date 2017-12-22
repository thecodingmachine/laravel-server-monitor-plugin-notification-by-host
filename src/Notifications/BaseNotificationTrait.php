<?php
namespace TheCodingMachine\ServerMonitorPluginNotificationByHost\Notifications;

trait BaseNotificationTrait
{
    /**
     * Get the notification's delivery channels by host or global if not exist.
     *
     * @param  mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        /* @var $event \Spatie\ServerMonitor\Events\Event */
        $event = $notifiable->getEvent();
        /* @var $host \Spatie\ServerMonitor\Models\Host */
        $host = $event->check->host()->first();
        $notifications = $host->getCustomProperty('notifications');
        if(!$notifications) {
            return config('server-monitor.notifications.notifications.'.static::class);
        }
        var_dump($notifications);
        var_dump(static::class);
        if(isset($notifications[static::class]) && isset($notifications[static::class]['channels'])) {
            return $notifications[static::class]['channels'];
        }
        return [];
    }
}
