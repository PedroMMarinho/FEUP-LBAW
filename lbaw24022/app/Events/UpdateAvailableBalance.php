<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class UpdateAvailableBalance implements ShouldBroadcast
{
    use SerializesModels;

    public $bidderId;
    public $availableBalance;

    /**
     * Create a new event instance.
     *
     * @param int $bidderId
     * @param float $availableBalance
     */
    public function __construct(int $bidderId, float $availableBalance)
    {
        $this->bidderId = $bidderId;
        $this->availableBalance = $availableBalance;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array|PrivateChannel
     */
    public function broadcastOn()
    {
        return new PrivateChannel('user-balance.' . $this->bidderId);
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'bidderId' => $this->bidderId,
            'availableBalance' => $this->availableBalance,
        ];
    }
}
