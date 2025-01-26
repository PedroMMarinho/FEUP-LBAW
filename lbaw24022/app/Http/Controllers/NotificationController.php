<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
class NotificationController extends Controller
{
    // This method will return all unread notifications for the logged-in user
    public function getUnreadNotificationsCount()
    {   
        $notificationsCount = Notification::where('general_user_id', Auth::user()->id)  // Adjust with the appropriate user identifier
                                      ->where('viewed', false)                // Only unread notifications
                                      ->count();                          // Retrieve the notifications


        // Return the notifications as a JSON response
        return response()->json([
            'unread_count' => $notificationsCount
        ]);
    }

    public function getNotifications(Request $request)
{
    // Get the page from the request (default to 1 if not provided)
    $page = $request->get('page', 1);

    // Fetch paginated notifications where 'viewed' is false for the authenticated user
    $notifications = Notification::where('general_user_id', Auth::user()->id)
        ->where('viewed', false) // Filter for unviewed notifications
        ->orderBy('timestamp', 'desc')
        ->paginate(10, ['*'], 'page', $page);  // Paginate with 10 items per page

    $notificationsCount = Notification::where('general_user_id', Auth::user()->id)  // Adjust with the appropriate user identifier
        ->where('viewed', false)                // Only unread notifications
        ->count();
        
        foreach ($notifications as $notification) {
            $notification->path = $this->getNotificationPath($notification);
        }

    return response()->json(['notifications' => $notifications, 'notificationCount' => $notificationsCount]);
}


private function getNotificationPath($notification)
{
    switch ($notification->notification_type) {
        case 'New Bid':
            return '/auctions/'. $notification->auction;
        case 'New Message':
            return '/auctions/'. $notification->auction;
        case 'New Auction':
            return '/auctions/'. $notification->auction;
        case 'Evaluation':
            return null;
        case 'Auction Closed':
            return null;
        case 'Auction Canceled':
            return null;
        case 'Auction Ending':
            return '/auctions/'. $notification->auction;
        case 'Auction Reported':
            return '/auctions/'. $notification->auction;
        case 'User Reported':
            return '/profile/'. $notification->report;
        case 'Rating':
            return '/profile/'. $notification->rate_user;
        case 'Shipping':
            return null;
        case 'Blocked':
            return null;
        case 'Unblocked':
            return null;
        case 'Subscription End':
            return '/subscription';
        case 'Advertisement End':
            return '/auctions/'. $notification->auction;
        case 'Auto Bid':
            return '/auctions/'. $notification->auction;
        case 'User Follow':
            return '/profile/'. $notification->follower_id;
        case 'User Unfollow':
            return null;
        case 'Auction Follow':
            return '/auctions/'. $notification->auction;
        case 'Auction Unfollow':
            return null;
        default:
            return null; // If no path is defined for this type
    }
}


    public function markAsViewed($id)
    {
        $notification = Notification::where('id', $id)
        ->where('general_user_id', Auth::id())
        ->first();
    
        // Find the notification that matches the ID and belongs to the current user
        if ($notification) {
            $notification->update(['viewed' => true]);
    
            return response()->json(['success' => true, 'message' => 'Notification cleared successfully']);
        }
    
        // If the notification doesn't exist or doesn't belong to the user
        return response()->json(['success' => false, 'message' => 'Notification not found or unauthorized'], 404);
    }

    public function markAllNotificationsAsViewed()
    {
    Notification::where('general_user_id', Auth::user()->id)
                ->where('viewed', false)
                ->update(['viewed' => true]);

    return response()->json(['message' => 'All notifications marked as viewed']);
    }


}
