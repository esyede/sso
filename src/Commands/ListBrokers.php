<?php

namespace Esyede\SSO\Commands;

use Illuminate\Console\Command;

class ListBrokers extends Command
{
    protected $signature = 'sso:broker-list';
    protected $description = 'List all created brokers.';
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $model = app(config('sso.brokers_model'));
        $brokers = $model::all(['id', 'name', 'secret'])->toArray();

        $this->table(['ID', 'Name', 'Secret'], $brokers);
    }
}
