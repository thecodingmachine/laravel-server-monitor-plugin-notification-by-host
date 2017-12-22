<?php

namespace TheCodingMachine\ServerMonitorPluginNotificationByHost\Commands;

use Illuminate\Support\Collection;
use Spatie\ServerMonitor\Commands\BaseCommand;
use Spatie\ServerMonitor\Models\Check;
use Spatie\ServerMonitor\Models\Host;
use Symfony\Component\Console\Helper\TableSeparator;

class ListNotifications extends BaseCommand
{
    protected $signature = 'server-monitor:list-notifications
                            {--host= : Only show checks for certain host}';

    protected $description = 'List all notifications for host(s)';

    private $channels = [];
    private $notifications = [];

    public function handle()
    {
        if ($this->determineHostModelClass()::count() === 0) {
            return $this->info('There are no hosts configured');
        }

        $this->channels = config('server-monitor-plugin-notification-by-host.notifications.channels');
        $this->notifications = config('server-monitor.notifications.notifications');
        $titles = ['Host'];
        foreach ($this->channels as $type => $channel) {
            $titles[] = $type;
        }

        $hosts = $this->determineHostModelClass()::all();
        if ($hostName = $this->option('host')) {
            $hosts = $hosts->filter(function (Host $host) use ($hostName) {
                return $host->name === $hostName;
            });
        }

        $this->tableWithTitle(
            'Custom notification by host',
            $titles,
            $this->getTableRows($hosts)
        );
    }

    protected function getTableRows(Collection $hosts): array
    {
        $rows = [];
        $first = true;
        foreach ($hosts as $host) {
            if(!$first) {
                $rows[] = new TableSeparator();
            }
            /* @var $host Host */
            $rows[] = [$host->name];

            $hostNotifications = $host->getCustomProperty('notifications');
            if($hostNotifications) {
                $rows = $this->displayCustomNotification($hostNotifications, $rows);
            }
            else {
                $rows = $this->displayGlobalNotification($rows);
            }
            $first = false;
        }
        return $rows;
    }

    private function displayCustomNotification(array $hostNotifications, array $rows): array {
        foreach ($this->notifications as $notification => $global) {
            $rows[] = [''];
            $temp = [implode("\n", str_split($notification, 50))];
            foreach ($this->channels as $type => $channel) {
                if(isset($hostNotifications[$notification]) && array_search($type, $hostNotifications[$notification]['channels']) !== false) {
                    if(isset($hostNotifications['configuration'][$type])) {
                        $configurationList = [];
                        foreach ($hostNotifications['configuration'][$type] as $configurationAttribut => $configurationValue) {
                            $configurationList[] = $configurationAttribut.':'.(is_array($configurationValue)?implode(',', $configurationValue):$configurationValue);
                        }
                        $temp[] = implode("\n", $configurationList);
                    }
                    else {
                        $temp[] = 'Global';
                    }
                }
                else {
                    $temp[] = '';
                }
            }
            $rows[] = $temp;
        }
        return $rows;
    }

    private function displayGlobalNotification(array $rows): array{

        foreach ($this->notifications as $notification => $global) {
            $rows[] = [''];
            $temp = [implode("\n", str_split($notification, 50))];
            foreach ($this->channels as $type => $channel) {
                if (array_search($type, $global) !== false) {
                    $temp[] = 'Global';
                }
                else {
                    $temp[] = '';
                }
            }
            $rows[] = $temp;
        }
        return $rows;
    }

    protected function tableWithTitle(string $title, array $header, array $rows)
    {
        $this->info($title);
        $this->info(str_repeat('=', strlen($title)));
        $this->table($header, $rows);
        $this->comment('');
    }
}
