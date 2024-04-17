<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Juego;

class estadoPartida
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $juego;
    
    public function __construct(Juego $juego)
    {
        $this->juego = $juego;
    }
    public function broadcastOn()
    {
        return new PrivateChannel('channel-juego');
    }

    public function broadcastAs()
    {
        return 'partidaIniciada';
    }
}
