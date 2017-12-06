<?php

namespace TheCodingMachine\ServerMonitorPluginNotificationByHost\Notifications;


class Notifiable extends \Spatie\ServerMonitor\Notifications\Notifiable
{
    public function routeNotificationForMail(): ?array
    {
        return parent::routeNotificationForMail();

        if($notification = $this->getSpecificConfiguration('mail')) {
            return $notification;
        }
    }

    public function routeNotificationForSlack(): ?string
    {
        if($notification = $this->getSpecificConfiguration('slack')) {
            return $notification;
        }
        return parent::routeNotificationForSlack();
    }

    /**
     * @return \Spatie\ServerMonitor\Models\Host
     */
    protected function getHost() {
        return $this->event->check->host()->first();
    }

    private function getSpecificConfiguration($type) {
        $notifications = $this->getHost()->getCustomProperty('notifications');
        if($notifications) {
            if(!isset($notifications['configuration'][$type])) {
                return null;
            }
            if(count($notifications['configuration'][$type]) == 1) {
                $configuration = config('server-monitor-plugin-notification-by-host.notifications.channels.'.$type);
                return $notifications['configuration'][$type][array_keys($configuration)[0]];
            }
            return $notifications['configuration'][$type];
        }
        return null;
    }
}
