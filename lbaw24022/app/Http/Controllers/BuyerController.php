<?php

namespace App\Http\Controllers;

use App\Models\Auction;
use App\Models\Bid;
use App\Models\Category;
use App\Models\GeneralUser;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BuyerController extends Controller
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

        $counts['following-users'] = GeneralUser::with('image', 'user.followers')
            ->whereHas('user.followers', function ($query) use ($userId) {
                // Check if the user is following this GeneralUser
                $query->where('follower_user', $userId);
            })->count();
        $counts['following-auctions'] = Auction::with(['followers'])
            ->where('auction_state', 'Active')
            ->whereHas('followers', function ($query) use ($userId) {
                // Check if the user is following this auction
                $query->where('follower', $userId);
            })
            ->count();
        $counts['won-auctions'] = Auction::leftJoin('bid', 'auction.id', '=', 'bid.auction')
            ->select('auction.*', DB::raw('MAX(bid.value) AS highest_bid'))
            ->where('auction.auction_state', 'Finished')
            ->groupBy('auction.id')
            ->havingRaw('MAX(bid.value) = (SELECT MAX(bid.value) FROM bid WHERE bid.auction = auction.id AND bid.bidder = ?)', [$userId])
            ->get()
            ->count();
        $counts['shipped-auctions'] = Auction::leftJoin('bid', 'auction.id', '=', 'bid.auction')
            ->select('auction.*', DB::raw('MAX(bid.value) AS highest_bid'))
            ->where('auction.auction_state', 'Shipped')
            ->groupBy('auction.id')
            ->havingRaw('MAX(bid.value) = (SELECT MAX(bid.value) FROM bid WHERE bid.auction = auction.id AND bid.bidder = ?)', [$userId])
            ->get()
            ->count();
        $counts['active-bids'] = Auction::leftJoin('bid', 'auction.id', '=', 'bid.auction')
            ->select('auction.*', DB::raw('MAX(bid.value) AS highest_bid'))
            ->where('auction.auction_state', 'Active')
            ->groupBy('auction.id')
            ->havingRaw('MAX(bid.value) = (SELECT MAX(bid.value) FROM bid WHERE bid.auction = auction.id AND bid.bidder = ?)', [$userId])
            ->get()
            ->count();

        if ($section == 'following-users') {
            // Get the search term from the request
            $search = $request->input('search');

            $users = GeneralUser::with('image', 'user.followers')
                ->whereHas('user.followers', function ($query) use ($userId) {
                    // Check if the user is following this GeneralUser
                    $query->where('follower_user', $userId);
                })
                ->search($search)
                ->paginate(12);

            $users->getCollection()->map(function ($user) {
                $user->following = true;
                return $user;
            });

            // Get the total number of users
            $totalUsers = $users->total();

            $categories = [];

            return view('buyer-dashboard', compact('userId', 'section', 'counts', 'users', 'totalUsers', 'search', 'categories'));
        } else {
            $auctionState = match ($section) {
                'won-auctions' => 'Finished',
                'shipped-auctions' => 'Shipped',
                default => 'Active',
            };

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

                if ($section == 'following-auctions') {
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
                        ->whereHas('followers', function ($query) use ($userId) {
                            // Check if the user is following this auction
                            $query->where('follower', $userId);
                        })
                        ->where('auction_state', 'Active')
                        ->search($search, $location, $category, $fromBid, $toBid, $attributes)
                        ->orderBy('end_time')
                        ->paginate(12);
                } else {
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
                        ->leftJoin('bid', 'auction.id', '=', 'bid.auction')
                        ->select('auction.*', DB::raw('MAX(bid.value) AS highest_bid'))
                        ->where('auction.auction_state', $auctionState)
                        ->groupBy('auction.id')
                        ->havingRaw('MAX(bid.value) = (SELECT MAX(bid.value) FROM bid WHERE bid.auction = auction.id AND bid.bidder = ?)', [$userId])
                        ->search($search, $location, $category, $fromBid, $toBid, $attributes)
                        ->orderBy('end_time')
                        ->paginate(12);
                }

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

            return view('buyer-dashboard', compact('userId', 'section', 'counts', 'auctions', 'categories', 'totalAuctions', 'selectedCategory', 'search', 'location', 'fromBid', 'toBid'));
        }
    }

    /**
     * Add a delivery location to the auction.
     */
    public function addDeliveryLocation(Request $request, $auctionId)
    {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'deliveryLocation' => 'required|string|max:255',  // Assuming delivery location is a string with max 255 characters
        ]);

        // Retrieve the auction by auctionId
        $auction = Auction::find($auctionId);

        if (!$auction) {
            return response()->json(['success' => false, 'reply' => 'Auction not found'], 404);
        }

        // Check if the auction already has a delivery location
        if (!empty($auction->delivery_location)) {
            return response()->json(['success' => false, 'reply' => 'Delivery location already exists'], 400);
        }

        // Set the delivery location
        $auction->delivery_location = $validatedData['deliveryLocation'];
        $auction->save();

        // Return a success response
        return response()->json(['success' => true, 'reply' => 'Delivery location successfully added']);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
