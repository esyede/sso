<?php

namespace Esyede\SSO\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ApiSSOLoginEvent
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public $user;
    public $flag;

    public function __construct($user, $flag)
    {
        $this->user = $user;
        $this->flag = $flag;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
