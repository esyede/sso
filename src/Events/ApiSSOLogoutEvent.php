<?php

namespace Esyede\SSO\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ApiSSOLogoutEvent
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
