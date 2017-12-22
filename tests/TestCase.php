<?php
namespace TheCodingMachine\ServerMonitorPluginNotificationByHost\Test;

use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\ServerMonitor\Models\Host;
use TheCodingMachine\ServerMonitorPluginNotificationByHost\ServerMonitorPluginNotificationByHostServiceProvider;

abstract class TestCase extends Orchestra {
    /** @var Server */
    public $server;

    /** @var ?string */
    protected $consoleOutputCache;

    public function setUp()
    {
        Carbon::setTestNow(Carbon::create(2016, 1, 1, 00, 00, 00));

        $this->consoleOutputCache = null;

        parent::setUp();

    }

    /**
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            ServerMonitorPluginNotificationByHostServiceProvider::class,
        ];
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');

        $app['config']->set('mail.driver', 'log');

        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'prefix' => '',
            'database' => ':memory:',
        ]);

        /* @var $app['config'] Repository */
        $app['config']->set(['server-monitor' => include(__DIR__ . '/../vendor/spatie/laravel-server-monitor/config/server-monitor.php')]);

        $app['config']->set('server-monitor.notifications.notifications', [
            \TheCodingMachine\ServerMonitorPluginNotificationByHost\Notifications\Notifications\CheckSucceeded::class => [],
            \TheCodingMachine\ServerMonitorPluginNotificationByHost\Notifications\Notifications\CheckRestored::class => [],
            \TheCodingMachine\ServerMonitorPluginNotificationByHost\Notifications\Notifications\CheckWarning::class => ['mail'],
            \TheCodingMachine\ServerMonitorPluginNotificationByHost\Notifications\Notifications\CheckFailed::class => ['slack'],
        ]);

        $app['config']->set('server-monitor.notifications.mail', ['to' => 'original@test.com']);
        $app['config']->set('server-monitor.notifications.slack', ['webhook_url' => 'test']);
        $app['config']->set('server-monitor.notifications.notifiable', TheCodingMachine\ServerMonitorPluginNotificationByHost\Notifications\Notifiable::class);

        $app['config']->set(['server-monitor-plugin-notification-by-host' => include(__DIR__ . '/../config/server-monitor-plugin-notification-by-host.php')]);

        $this->setUpDatabase();
    }

    protected function setUpDatabase()
    {
        include_once __DIR__.'/../vendor/spatie/laravel-server-monitor/database/migrations/create_hosts_table.php.stub';
        (new \CreateHostsTable())->up();

        include_once __DIR__.'/../vendor/spatie/laravel-server-monitor/database/migrations/create_checks_table.php.stub';
        (new \CreateChecksTable())->up();
    }


    /**
     * @param string|array $searchStrings
     */
    protected function seeInConsoleOutput($searchStrings)
    {
        if (! is_array($searchStrings)) {
            $searchStrings = [$searchStrings];
        }
        $output = $this->getArtisanOutput();
        foreach ($searchStrings as $searchString) {
            $this->assertContains((string) $searchString, $output);
        }
    }

    /**
     * @param string|array $searchStrings
     */
    protected function dontSeeInConsoleOutput($searchStrings)
    {
        if (! is_array($searchStrings)) {
            $searchStrings = [$searchStrings];
        }
        $output = $this->getArtisanOutput();
        foreach ($searchStrings as $searchString) {
            $this->assertNotContains((string) $searchString, $output);
        }
    }

    protected function getArtisanOutput(): string
    {
        $this->consoleOutputCache .= Artisan::output();

        return $this->consoleOutputCache;
    }


    public function addHosts($name, array $checks = [])
    {
        Host::create([
            'name' => $name,
            'ssh_user' => 'user',
            'port' => 22]);
/*
        if (! is_array($names)) {
            $names = [$names];
        }
        /*
                collect($names)->each(function ($name) use ($checks) {
                    Host::create([
                        'name' => $name,
                        'ssh_user' => 'user',
                        'port' => 22,
                    ])->checks()->saveMany(collect($checks)->map(function (string $checkName) {
                        return new Check([
                            'type' => $checkName,
                            'status' => CheckStatus::NOT_YET_CHECKED,
                            'custom_properties' => [],
                        ]);
                    }));
                });
        */
    }





















}