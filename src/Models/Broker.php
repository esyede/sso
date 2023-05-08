<?php

namespace Esyede\SSO\Models;

use Illuminate\Database\Eloquent\Model;

class Broker extends Model
{
    public function getTable()
    {
        return config('sso.brokers_table', 'brokers');
    }
}
