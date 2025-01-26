<?php

namespace App\Http\Controllers;

use App\Models\Auction;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SellerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $queryParams = $request->query();

        $section = isset($queryParams['section']) ? $queryParams['section'] : null;
        $userId = Auth::id();

        $user = Auth::user();

        if ($user->role !== 'Regular User') {
            return redirect('/')->with('error', 'Only regular users are allowed to this page');
        }

        try {
            // Define the validation rules for the query parameters
            $validatedData = $request->validate([
                'search' => 'nullable|string|max:255',
                'location' => 'nullable|string|max:255',
                'category' => 'nullable|exists:category,id',
                'bid.from' => 'nullable|numeric|min:0',
                'bid.to' => [
                    'nullable',
                    'numeric',
                    'min:0',
                    function ($attribute, $value, $fail) use ($request) {
                        $fromBid = $request->input('from-bid');
                        if (!is_null($value) && !is_null($fromBid) && $value < $fromBid) {
                            $fail('The to-bid must be greater than or equal to from-bid.');
                        }
                    }
                ],
            ]);

            // Get the validated values from the request
            $search = $validatedData['search'] ?? '';
            $location = $validatedData['location'] ?? '';
            $categoryId = $validatedData['category'] ?? null;
            $fromBid = $validatedData['bid']['from'] ?? null;
            $toBid = $validatedData['bid']['to'] ?? null;

            // Get category and its attributes for filtering
            $category = Category::find($categoryId);
            $attributes = ($category && isset($request['attributes'][$categoryId])) ? $request['attributes'][$categoryId] : [];

            // Determine auction state based on section
            $auctionState = match ($section) {
                'to-ship' => 'Finished',
                'shipped' => 'Shipped',
                'canceled' => 'Canceled',
                'finished-without-bids' => 'Finished without bids',
                default => 'Active',
            };

            // Build the auction query with pagination using the scopeSearch
            $auctions = Auction::with([
                'bids' => function ($query) {
                    $query->orderBy('value', 'desc')->limit(1);
                },
                'images' => function ($query) {
                    $query->limit(1);
                },
                'followers',
                'seller'
            ])
                ->where('seller_id', $userId)
                ->where('auction_state', $auctionState)
                ->search($search, $location, $category, $fromBid, $toBid, $attributes)
                ->orderBy('end_time')
                ->paginate(12);

        } catch (\Exception $e) {
            // Return an empty paginated result in case of error
            $auctions = new \Illuminate\Pagination\LengthAwarePaginator(
                [],
                0,
                12,
                1,
                ['path' => \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPath()]
            );
        }

        // Get all categories for the view
        $categories = Category::all();

        $totalAuctions = $auctions->total();

        $request = request()->query();

        $selectedCategory = $category ?? new Category();
        $search = $search ?? null;
        $location = $location ?? null;
        $fromBid = $fromBid ?? null;
        $toBid = $toBid ?? null;

        $auctionStates = ['Active', 'Canceled', 'Finished', 'Shipped', 'Finished without bids'];

        $auctionCounts = [];

        foreach ($auctionStates as $state) {
            $auctionCounts[$state] = Auction::where('seller_id', $userId)
                ->where('auction_state', $state)
                ->count();
        }

        return view('seller-dashboard', compact('userId', 'section', 'auctionCounts', 'auctions', 'categories', 'totalAuctions', 'selectedCategory', 'search', 'location', 'fromBid', 'toBid'));
    }

    public function shipAuction(int $id)
    {
        $auction = Auction::findOrFail($id);
        $user = Auth::user();

        if ($user->id !== $auction->seller->id) {
            return response()->json([
                'success' => false,
                'reply' => 'Cannot ship an auction that is not yours',
            ], 403);
        }

        if ($auction->auction_state === 'Shipped') {
            return response()->json([
                'success' => false,
                'reply' => 'Cannot ship an auction already shipped',
            ], 400);
        }

        if ($auction->auction_state === 'Finished without bids') {
            return response()->json([
                'success' => false,
                'reply' => 'Cannot ship an auction with no winners',
            ], 400);
        }

        if ($auction->auction_state !== 'Finished') {
            return response()->json([
                'success' => false,
                'reply' => 'Cannot ship an auction that is not finished',
            ], 400);
        }

        if ($auction->delivery_location == null) {
            return response()->json([
                'success' => false,
                'reply' => 'Delivery location pending from winner',
            ], 400);
        }

        $auction->auction_state = 'Shipped';
        $auction->save();

        return response()->json([
            'success' => true,
            'reply' => 'Auction successfully shipped',
        ]);
    }

}
