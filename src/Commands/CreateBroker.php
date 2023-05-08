<?php

namespace Esyede\SSO\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CreateBroker extends Command
{
    protected $signature = 'sso:broker-create {name}';
    protected $description = 'Creating new SSO broker.';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $brokerClass = app(config('sso.brokers_model'));
        $broker = new $brokerClass;

        $broker->name = $this->argument('name');
        $broker->secret = Str::random(40);

        $broker->save();

        $this->info('Broker with name `' . $this->argument('name') . '` successfully created.');
        $this->info('Secret: ' . $broker->secret);
    }
}
