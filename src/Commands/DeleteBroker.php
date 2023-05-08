<?php

namespace Esyede\SSO\Commands;

use Illuminate\Console\Command;

class DeleteBroker extends Command
{
    protected $signature = 'sso:broker-delete {name}';
    protected $description = 'Delete broker with specified name.';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $brokerClass = app(config('sso.brokers_model'));
        $broker = $brokerClass::where('name', $this->argument('name'))->firstOrFail();
        $broker->delete();

        $this->info('Broker with name `' . $this->argument('name') . '` successfully deleted.');
    }
}
