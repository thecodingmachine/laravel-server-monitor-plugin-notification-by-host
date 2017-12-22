<?php

namespace TheCodingMachine\ServerMonitorPluginNotificationByHost\Test;


use Illuminate\Support\Facades\Artisan;
use Mockery as m;
use Spatie\ServerMonitor\Models\Host;
use TheCodingMachine\ServerMonitorPluginNotificationByHost\Commands\ListNotifications;
use TheCodingMachine\ServerMonitorPluginNotificationByHost\Test\TestCase;

class ListNotificationsTest extends TestCase
{
    /** @var \TheCodingMachine\ServerMonitorPluginNotificationByHost\Commands\ListNotifications|m\Mock */
    protected $command;

    public function setUp() {
        parent::setUp();
        $this->command = new class extends ListNotifications {

            private $answers = [];

            private $answer = 0;

            public function answers($answers) {
                $this->answer = 0;
                $this->answers = $answers;
            }

            public function choice($question, array $choices, $default = null, $attempts = null, $multiple = null) {
                echo $question . "\n";
                $result = $this->answers[$this->answer];
                if ($multiple) {
                    if (!is_array($this->answers[$this->answer])) {
                        $result = [$this->answers[$this->answer]];
                    }
                }
                $this->answer++;
                return $result;
            }

            public function confirm($question, $default = false) {
                echo $question . "\n";
                return $this->answers[$this->answer++];
            }

            public function ask($question, $default = null) {
                echo $question . "\n";
                return $this->answers[$this->answer++];
            }
        };

        $this->app->bind('command.server-monitor:list-notifications', function () {
            return $this->command;
        });
    }

    /** @test */
    public function it_check_no_data() {
        Artisan::call('server-monitor:list-notifications');

        $this->seeInConsoleOutput(['There are no hosts configured']);

//        var_dump($this->getArtisanOutput());
        //        $this->dontSeeInConsoleOutput(['wrong-host', 'wrong-check']);
        //
        //        $this->seeInConsoleOutput(['correct-check', 'correct-host']);
    }

    /** @test */
    public function it_check() {

        $notifications =  [
            'TheCodingMachine\ServerMonitorPluginNotificationByHost\Notifications\Notifications\CheckSucceeded' =>
                [
                    'channels' => ['mail']
                ],
            'TheCodingMachine\ServerMonitorPluginNotificationByHost\Notifications\Notifications\CheckWarning' =>
                [
                    'channels' => ['slack']
                ],
            'configuration' => ['mail' => [ 'to' => 'test@test.com' ]]
            ];

        $host = Host::create([
            'name' => 'test',
            'ssh_user' => 'user',
            'port' => 22]);
        $host->setCustomProperty('notifications', $notifications);
        $host->save();

        Artisan::call('server-monitor:list-notifications');

        $this->seeInConsoleOutput(['TheCodingMachine\ServerMonitorPluginNotificationBy | to:test@test.com']);
        $this->seeInConsoleOutput([' TheCodingMachine\ServerMonitorPluginNotificationBy |                  | Global']);
    }

    /** @test */
    public function it_check_many_host() {

        $host = Host::create([
            'name' => 'server',
            'ssh_user' => 'user',
            'port' => 22]);

        $host = Host::create([
            'name' => 'other',
            'ssh_user' => 'user',
            'port' => 22]);

        Artisan::call('server-monitor:list-notifications');

        $this->seeInConsoleOutput(['server', 'other']);
    }

    /** @test */
    public function specific_host() {

        $host1 = Host::create([
            'name' => 'server',
            'ssh_user' => 'user',
            'port' => 22]);

        $host2 = Host::create([
            'name' => 'other',
            'ssh_user' => 'user',
            'port' => 22]);

        Artisan::call('server-monitor:list-notifications', ['--host' => 'server']);

        $this->seeInConsoleOutput(['server']);
        $this->dontSeeInConsoleOutput(['other']);
    }

}
