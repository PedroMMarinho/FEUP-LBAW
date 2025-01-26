<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\GeneralUser;
class AuctionSendMessage implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $messageData;
    public $auctionId;
    public $userIds;
    public $userImage;

    public function __construct($messageData, $auctionId, $userIds, $userImage)
    {
        $this->messageData = $messageData;
        $this->auctionId = $auctionId;
        $this->userIds = $userIds;
        $this->userImage = $userImage;
    }

    public function broadcastOn()
    {
        return ['auction-chat.' . $this->auctionId];
    }

    public function broadcastAs()
    {
        return 'message.sent';
    }

    public function broadcastWith()
    {
    
        return [
            'message' => $this->messageData,
            'userIds' => $this->userIds,
            'userImage' => $this->userImage
        ];
    }
}
