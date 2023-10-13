<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CsvUploadEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(private int $user_id, public int $status)
    {
    }

    public function broadcastAs()
    {
        return 'csv-upload.created';
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel('csv-upload.'.$this->user_id)];
    }
}
