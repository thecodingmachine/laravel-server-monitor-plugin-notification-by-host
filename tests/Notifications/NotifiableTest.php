<?php

namespace TheCodingMachine\ServerMonitorPluginNotificationByHost\Notifications;


use Spatie\ServerMonitor\Models\Check;
use Spatie\ServerMonitor\Models\Enums\CheckStatus;
use Spatie\ServerMonitor\Models\Host;
use TheCodingMachine\ServerMonitorPluginNotificationByHost\Notifications\Notifications\CheckSucceeded;
use TheCodingMachine\ServerMonitorPluginNotificationByHost\Test\TestCase;
use Spatie\ServerMonitor\Events\Event;

class NotifiableTest extends TestCase
{

    /**
     * @var \TheCodingMachine\ServerMonitorPluginNotificationByHost\Notifications\Notifiable
     */
    private $notifiable;

    public function setUp() {
        parent::setUp();
        $this->notifiable = new Notifiable();

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
    }

    /** @test */
    function route_notification_for_mail() {
       $mail = $this->notifiable->routeNotificationForMail();
       $this->assertSame('original@test.com', $mail[0]);
    }

    /** @test */
    function route_notification_for_mail_custom() {
        /* @var $host Host */
        $host = $this->notifiable->getEvent()->check->host()->first();
        $notifications =  [
            'TheCodingMachine\ServerMonitorPluginNotificationByHost\TheCodingMachine\ServerMonitorPluginNotificationByHost\Notifications\Notifications\CheckSucceeded' =>
                [
                    'channels' => ['mail']
                ],
            'configuration' => ['mail' => [ 'to' => ['test@test.com'] ]]
        ];

        $host->setCustomProperty('notifications', $notifications);

        $host->save();

        $mail = $this->notifiable->routeNotificationForMail();
        $this->assertSame('test@test.com', $mail[0]);
    }

    /** @test */
    function route_notification_no_custom_data() {
        /* @var $host Host */
        $host = $this->notifiable->getEvent()->check->host()->first();
        $notifications =  [
            'TheCodingMachine\ServerMonitorPluginNotificationByHost\TheCodingMachine\ServerMonitorPluginNotificationByHost\Notifications\Notifications\CheckFailed' =>
                [
                    'channels' => ['slack']
                ],
            'configuration' => ['slack' => [ 'webhook' => ['urlSlack'] ]]
        ];

        $host->setCustomProperty('notifications', $notifications);

        $host->save();

        $mail = $this->notifiable->routeNotificationForMail();
        $this->assertSame('original@test.com', $mail[0]);
    }

    /** @test */
    function route_notification_for_mail_custom_array() {
        /* @var $host Host */
        $host = $this->notifiable->getEvent()->check->host()->first();
        $notifications =  [
            'TheCodingMachine\ServerMonitorPluginNotificationByHost\Test\TheCodingMachine\ServerMonitorPluginNotificationByHost\Notifications\Notifications\CheckSucceeded' =>
                [
                    'channels' => ['mail']
                ],
            'configuration' => ['mail' => [ 'to' => ['test@test.com'], 'other' => 'otherData']]
        ];

        $host->setCustomProperty('notifications', $notifications);

        $host->save();

        $mail = $this->notifiable->routeNotificationForMail();
        $this->assertSame('test@test.com', $mail['to'][0]);
        $this->assertSame('otherData', $mail['other']);
    }

    /** @test */
    function route_notification_for_slack() {
        /* @var $host Host */
        $slack = $this->notifiable->routeNotificationForSlack();
        $this->assertSame('test', $slack);
    }

    /** @test */
    function route_notification_for_slack_custom() {
        /* @var $host Host */
        $host = $this->notifiable->getEvent()->check->host()->first();
        $notifications =  [
            'TheCodingMachine\ServerMonitorPluginNotificationByHost\Test\TheCodingMachine\ServerMonitorPluginNotificationByHost\Notifications\Notifications\CheckSucceeded' =>
                [
                    'channels' => ['slack']
                ],
            'configuration' => ['slack' => [ 'webhook_url' => 'urlSlack' ]]
        ];

        $host->setCustomProperty('notifications', $notifications);

        $host->save();

        $slack = $this->notifiable->routeNotificationForSlack();
        $this->assertSame('urlSlack', $slack);
    }
}
