<?php
namespace TheCodingMachine\ServerMonitorPluginNotificationByHost\Notifications;

use TheCodingMachine\ServerMonitorPluginNotificationByHost\Notifications\Notifications\CheckFailed;
use TheCodingMachine\ServerMonitorPluginNotificationByHost\Notifications\Notifications\CheckSucceeded;
use TheCodingMachine\ServerMonitorPluginNotificationByHost\Test\TestCase;
use Spatie\ServerMonitor\Models\Check;
use Spatie\ServerMonitor\Models\Enums\CheckStatus;
use Spatie\ServerMonitor\Models\Host;

class BaseNotificationTraitTest extends TestCase
{


    /**
     * @var \TheCodingMachine\ServerMonitorPluginNotificationByHost\Notifications\Notifiable
     */
    private $notifiable;

    public function setUp() {
        parent::setUp();
        $this->notifiable = new Notifiable();

    }

    /** @test */
    function via() {

        $checks = ['diskspace'];

        $host = Host::create([
            'name' => 'test',
            'port' => 22,
        ]);

        $host->checks()->saveMany(collect($checks)->map(function (string $checkName) {
            return new Check([
                'type' => $checkName,
                'status' => CheckStatus::NOT_YET_CHECKED,
            ]);
        }));

        $event = new \Spatie\ServerMonitor\Events\CheckSucceeded($host->checks()->first());
        $this->notifiable->setEvent($event);

        $check = new CheckFailed();
        $result = $check->via($this->notifiable);
        $this->assertSame('slack', $result[0]);
    }

    /** @test */
    function via_custom() {

        $checks = ['diskspace'];

        $host = Host::create([
            'name' => 'test2',
            'port' => 22,
        ]);

        $host->checks()->saveMany(collect($checks)->map(function (string $checkName) {
            return new Check([
                'type' => $checkName,
                'status' => CheckStatus::NOT_YET_CHECKED,
            ]);
        }));

        $event = new \Spatie\ServerMonitor\Events\CheckSucceeded($host->checks()->first());
        $this->notifiable->setEvent($event);

        $host = $this->notifiable->getEvent()->check->host()->first();
        $notifications =  [
            'TheCodingMachine\ServerMonitorPluginNotificationByHost\Notifications\Notifications\CheckSucceeded' =>
                [
                    'channels' => ['mail']
                ],
            'configuration' => ['mail' => [ 'to' => ['test@test.com'] ]]
        ];

        $host->setCustomProperty('notifications', $notifications);

        $host->save();

        $check = new CheckFailed();
        $result = $check->via($this->notifiable);
        $this->assertCount(0, $result);
    }

    /** @test */
    function via_custom_notifiation_exist() {

        $checks = ['diskspace'];

        $host = Host::create([
            'name' => 'test',
            'port' => 22,
        ]);

        $host->checks()->saveMany(collect($checks)->map(function (string $checkName) {
            return new Check([
                'type' => $checkName,
                'status' => CheckStatus::NOT_YET_CHECKED,
            ]);
        }));

        $event = new \Spatie\ServerMonitor\Events\CheckSucceeded($host->checks()->first());
        $this->notifiable->setEvent($event);

        $host = $this->notifiable->getEvent()->check->host()->first();
        $notifications =  [
            'TheCodingMachine\ServerMonitorPluginNotificationByHost\Notifications\Notifications\CheckFailed' =>
                [
                    'channels' => ['mail']
                ],
            'configuration' => ['mail' => [ 'to' => ['test@test.com'] ]]
        ];

        $host->setCustomProperty('notifications', $notifications);

        $host->save();

        $check = new CheckFailed();
        $result = $check->via($this->notifiable);
        $this->assertSame('mail', $result[0]);
    }
}
