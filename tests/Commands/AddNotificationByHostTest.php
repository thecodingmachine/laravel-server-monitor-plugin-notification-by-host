<?php

namespace TheCodingMachine\ServerMonitorPluginNotificationByHost\Test\Commands;

use Illuminate\Support\Facades\Artisan;
use Mockery as m;
use Spatie\ServerMonitor\HostRepository;
use Spatie\ServerMonitor\Models\Host;
use TheCodingMachine\ServerMonitorPluginNotificationByHost\Commands\AddNotificationByHost;
use TheCodingMachine\ServerMonitorPluginNotificationByHost\Test\TestCase;

class AddNotificationByHostTest extends TestCase
{
    protected $command;

    public function setUp()
    {
        parent::setUp();

        $this->command = new class extends AddNotificationByHost {
            private $answers = [];
            private $answer = 0;
            public function answers($answers) {
                $this->answer = 0;
                $this->answers = $answers;
            }
            public function choice($question, array $choices, $default = null, $attempts = null, $multiple = null)
            {
                echo $question."\n";
                $result = $this->answers[$this->answer];
                if($multiple) {
                    if(!is_array($this->answers[$this->answer])) {
                        $result = [$this->answers[$this->answer]];
                    }
                }
                $this->answer ++;
                return $result;
            }
            public function confirm($question, $default = false)
            {
                echo $question."\n";
                return $this->answers[$this->answer ++];
            }

            public function ask($question, $default = null) {
                echo $question."\n";
                return $this->answers[$this->answer ++];
            }
        };

        $this->app->bind('command.server-monitor:add-notification-host', function () {
            return $this->command;
        });
    }

    /** @test */
    public function it_can_notify_host_empty()
    {
        $host = Host::create([
            'name' => 'test',
            'ssh_user' => 'user',
            'port' => 22]);

        $this->command->answers([false, "test", 'No channel', 'No channel', 'No channel', 'No channel']);
        Artisan::call('server-monitor:add-notification-host');
        $this->assertNull($host->getCustomProperty('notifications'));

        $this->command->answers([true, "wrongTest", true, 'test', 'No channel', 'No channel', 'No channel', 'No channel']);
        Artisan::call('server-monitor:add-notification-host');
        $this->assertNull($host->getCustomProperty('notifications'));
    }

    /** @test */
    public function it_can_notify_host_check_mail()
    {
        $host = Host::create([
            'name' => 'test',
            'ssh_user' => 'user',
            'port' => 22]);
        $this->command->answers([false, 'test', 'mail', 'No channel', 'No channel', 'No channel', 'test@test.com', false]);
        Artisan::call('server-monitor:add-notification-host');

        $host = HostRepository::determineHostModel()::where('name', 'test')->first();

        $notifications = $host->getCustomProperty('notifications');

        $checkSucceeded = $notifications['TheCodingMachine\ServerMonitorPluginNotificationByHost\Notifications\Notifications\CheckSucceeded'];

        $this->assertContains('mail', $checkSucceeded['channels']);
    }

    /** @test */
    public function it_can_notify_host_check_keep_old_parameter()
    {
        $host = Host::create([
            'name' => 'test',
            'ssh_user' => 'user',
            'port' => 22]);
        $host->setCustomProperty('test', 'test');
        $host->save();

        $this->command->answers([false, 'test', 'mail', 'No channel', 'No channel', 'No channel', true, 'test@test.com', '']);
        Artisan::call('server-monitor:add-notification-host');

        $host = HostRepository::determineHostModel()::where('name', 'test')->first();

        $test = $host->getCustomProperty('test');

        $this->assertSame($test, 'test');
    }

    /** @test */
    public function error_channel()
    {
        $host = Host::create([
            'name' => 'test',
            'ssh_user' => 'user',
            'port' => 22]);

        $this->command->answers([false, "test", 'test', 'No channel', 'No channel', 'No channel']);
        Artisan::call('server-monitor:add-notification-host');
    }
}
