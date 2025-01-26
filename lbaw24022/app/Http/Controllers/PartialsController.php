<?php

namespace App\Http\Controllers;

use App\Models\Auction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PartialsController extends Controller
{
    public function newAttribute(Request $request)
    {
        $validated = $request->validate([
            'attributeName' => 'required|string|max:255',
        ]);

        $attributeName = $validated['attributeName'];

        // You can return the HTML for the new attribute, perhaps a partial view
        return view('partials.new-attribute', compact('attributeName'));
    }

    public function shippingForms(Request $request, int $id)
    {
        $auction = Auction::findOrFail($id);
        $user = Auth::user();

        if ($user->id !== $auction->seller->id) {
            return response()->json([
                'success' => false,
                'reply' => 'Cannot ship an auction that is not yours',
            ], 403);
        }

        if ($auction->auction_state !== 'Shipped') {
            return response()->json([
                'success' => false,
                'reply' => 'Cannot get the shipping forms of an auction yet to be shipped',
            ], 400);
        }

        $winner = $auction->winner()->generalUser;

        $senderName = $user->username;
        $senderEmail = $user->email;
        $auctionLocation = $auction->location;
        $auctionName = $auction->name;
        $auctionId = $id;
        $recipientName = $winner->username;
        $recipientEmail = $winner->email;
        $deliveryLocation = $auction->delivery_location;

        // You can return the HTML for the new attribute, perhaps a partial view
        return view('partials.shipping-forms', compact('senderName', 'senderEmail', 'auctionLocation', 'auctionName', 'auctionId', 'recipientName', 'recipientEmail', 'deliveryLocation'));
    }
}
