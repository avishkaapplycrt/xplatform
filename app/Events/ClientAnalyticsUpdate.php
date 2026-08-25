<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ClientAnalyticsUpdate implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $clientId,
        public string $metric,
        public array $data
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('client.' . $this->clientId . '.analytics'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'metric.update';
    }

    public function broadcastWith(): array
    {
        return [
            'metric' => $this->metric,
            'value' => $this->data['value'] ?? null,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}