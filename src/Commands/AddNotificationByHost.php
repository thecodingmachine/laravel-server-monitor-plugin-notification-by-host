<?php

namespace TheCodingMachine\ServerMonitorPluginNotificationByHost\Commands;

use InvalidArgumentException;
use Spatie\ServerMonitor\Commands\BaseCommand;
use Spatie\ServerMonitor\Models\Enums\CheckStatus;

class AddNotificationByHost extends BaseCommand
{
    protected $signature = 'server-monitor:add-notification-host';

    protected $description = 'Add notification by host';

    public function handle()
    {
        $this->info("Let's add notification by host!");

        $host = $this->hostChoice();
        $notificationsParameters = $this->channelChoice();
        $notificationsParameters = $this->configuration($host, $notificationsParameters);

        $host->setCustomProperty('notifications', $notificationsParameters);
        $host->save();

        $this->info("Host notification `{$host->name}` saved");
    }

    private function getAllHostNames() {
        $hosts = $this->determineHostModelClass()::all();
        $hostList = [];
        foreach ($hosts as $host) {
            $hostList[$host->id] = $host->name;
        }
        return $hostList;
    }

    protected function determineChecks(array $chosenChecks, array $checkNames): array
    {
        if (in_array(static::$allChecksLabel, $chosenChecks)) {
            return $this->getAllCheckNames();
        }

        return array_diff($chosenChecks, [static::$allChecksLabel]);
    }

    protected function getAllCheckNames(): array
    {
        return array_keys(config('server-monitor.checks'));
    }

    private function askConfiguration($notifications) {
        $configure = [];
        foreach ($notifications as $channel) {
            $attributes = config('server-monitor-plugin-notification-by-host.notifications.channels.'.$channel);
            foreach ($attributes as $attribute => $type) {
                config('server-monitor.notifications.channels.'.$channel);
                $configure[$channel][$attribute] = $this->ask("For channel `$channel` attribute `$attribute`?".($type == 'array'?" (values separate by ,)":""), false);
                if($type == 'array') {
                    $configure[$channel][$attribute] = explode(',', $configure[$channel][$attribute]);
                }
            }
        }
        return $configure;
    }

    private function hostChoice() {
        $this->line("----- Host choice -----");
        $hostList = $this->getAllHostNames();
        // Get the host object
        do {
            $hostName = $this->confirm('Do you know the host name?')
                ? $this->ask('Which host name?')
                : $this->choice('Which host?', $hostList);

            $host = $this->determineHostModelClass()::where('name', $hostName)->first();
            if(!$host) {
                $this->warn("This host `{$hostName}` doesn't exist");
            }
        } while(!$host);

        $this->info("Host: {$host->name}");
        return $host;
    }

    private function channelChoice() {
        $this->line("----- Channel configuration -----");

        $notifications = ['No channel'];
        foreach (config('server-monitor-plugin-notification-by-host.notifications.channels') as $channel => $config) {
            $notifications[] = $channel;
        }

        $notificationsParameters = [];
        foreach(config('server-monitor.notifications.notifications') as $class => $notification) {
            $this->line("Notification for ".$class);

            $channels = $this->choice('Which channel? (values separate by ,)', $notifications, 0, null, true);
            if($channels[0] != 'No channel') {
                $notificationsParameters[$class]['channels'] = $channels;
            }
        }
        return $notificationsParameters;
    }

    private function configuration($host, $notificationsParameters) {
        if(!$notificationsParameters) {
            $this->warn("The host {$host->name} has no notification configure !");
        }
        else {

            $this->line("----- Notification configuration -----");

            $this->line('Leave empty question, if you want to use the global configuration stored in server-monitor');

            $notificationsUsed = [];
            foreach ($notificationsParameters as $check) {
                $notificationsUsed = array_unique(array_merge($notificationsUsed, $check['channels']), SORT_REGULAR);
            }

            $configure = $this->askConfiguration($notificationsUsed);
            $notificationsParameters['configuration'] = $configure;
        }
        return $notificationsParameters;
    }
}
