<?php

namespace App\Http\Controllers;

use App\Models\Auction;
use Illuminate\Http\Request;
use App\Models\Bid;
use App\Events\AuctionBidUpdated;
use App\Models\SiteConfig;
use Illuminate\Support\Facades\Auth;

use App\Models\Category;
use App\Models\ReportAuction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Models\Advertisement;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

use Illuminate\Support\Facades\Gate;


use Carbon\Carbon;
use App\Models\AutoBid;

use Illuminate\Support\Facades\Log;
use App\Events\AuctionSendMessage;
use App\Models\Message;
use App\Models\User;
use App\Models\GeneralUser;
use App\Models\AdminChange;
use App\Events\UpdateAvailableBalance;

class AuctionController extends Controller
{

    public function show(int $id)
    {
        $auction = Auction::findOrFail($id);


        if (($auction->auction_state === 'Draft' || $auction->auction_state === 'Canceled' || $auction->auction_state === 'Finished without bids') && (!Auth::check() || Auth::user()->id != $auction->seller_id)) {
            return back()->with('error', 'Not valid auction');
        }


        if (($auction->auction_state === 'Shipped' || $auction->auction_state === 'Finished') && (!Auth::check() || !(Auth::user()->id == $auction->seller_id || Auth::user()->id == $auction->winner()->id))) {
            return back()->with('error', 'Not valid auction');
        }


        // Get the highest bid using the method defined in the Auction model
        $currentHighestBid = $auction->highestBidValue();

        // Get the minimum valid bid for this auction
        $minBidValid = $auction->minBidValid();


        // Get subscription value
        $adCost = SiteConfig::getSiteConfig()->ad_price;
        $adDiscountedCost = SiteConfig::getSiteConfig()->discounted_ad_price;


        $activeAutoBid = AutoBid::getActiveAutoBid($auction->id, Auth::id());

        $messages = $auction->messages()
            ->orderBy('timestamp', 'desc') // Order descending to get the most recent messages
            ->take(20)
            ->get()
            ->sortBy('timestamp');
        // Return the view with both the auction and the current bid data
        return view('auction.auction', compact('auction', 'currentHighestBid', 'minBidValid', 'activeAutoBid', 'messages', 'adCost', 'adDiscountedCost'));

    }

    public function list() // TODO Change this later
    {
        $auctions = Auction::all();

        return view('auction.list', compact('auctions'));
    }

    public function placeMaxBid(Request $request, $auctionId)
    {

        Log::info('ANTES AUCTON');

        $auction = Auction::findOrFail($auctionId);
        // Minimal valid bid
        $minBidValue = $auction->minBidValid();

        Log::info('DEPISN AUCIO');

        // Get the current highest bid
        $currentHighestBid = Bid::where('auction', $auctionId)
            ->orderBy('value', 'desc')
            ->first();

        $initialBidCount = $auction->bids()->count();

        $siteConfig = SiteConfig::getSiteConfig();


        $bidValue = $request->input('value');

        Log::info('ANTES USER');

        $user = Auth::user()->user;

        Log::info('DEPOIS USER');


        if (Auth::user()->id == $auction->seller_id) {
            return response()->json(['success' => false, 'reply' => 'You cannot bid on your own auction']);
        }

        if ($auction->auction_state != 'Active') {
            return response()->json(['success' => false, 'reply' => 'Auction already finished']);
        }

        if (Auth::user()->role != 'Regular User') {
            return response()->json(['success' => false, 'reply' => 'Only regular users can bid']);
        }

        if ($user->blocked) {
            return response()->json(['success' => false, 'reply' => 'You have been blocked! Bid cannot be placed']);
        }

        if (!$user->subscribed) {
            return response()->json(['success' => false, 'reply' => 'Only subscribers can AutoBid']);
        }

        if ($user) {
            $useravailable_balance = $user->available_balance;
        } else {
            return response()->json(['error' => 'User not found'], 404);
        }

        if ($bidValue < $minBidValue) {
            // Return a custom error message for bid value validation
            return response()->json([
                'success' => false,
                'reply' => 'The bid must be higher than €' . number_format($minBidValue, 2),
            ]);
        }

        $myAutobid = AutoBid::where('auction', $auctionId)
            ->where('bidder', Auth::id())
            ->where('active', true)
            ->first();

        $highestBid = Bid::where('auction', $auctionId)
            ->orderBy('value', 'desc')
            ->first();

        if (
            ($myAutobid && $useravailable_balance < $bidValue - $myAutobid->max) ||
            (!$myAutobid && (($highestBid && (($highestBid->bidder === Auth::id() && $useravailable_balance < $bidValue - $highestBid->value) || ($highestBid->bidder !== Auth::id() && $useravailable_balance < $bidValue))) || (!$highestBid && $useravailable_balance < $bidValue)))
        ) {
            // If insufficient funds, return an error early
            return response()->json(['success' => false, 'reply' => 'Insufficient funds']);
        }

        try{
        // Create a new bid record using the validated value
        $createdAutobid = new AutoBid([
            'max' => $bidValue,
            'auction' => $auctionId,
            'bidder' => $request->user()->id, // Assuming the user is authenticated
        ]);

        $createdAutobid->save();

        }
        catch (\Throwable $e) {
            return response()->json(['success' => false, 'reply' => 'Auction already finished']);
        }

        $currentStatus = AutoBid::where('auction', $auctionId)
            ->where('bidder', Auth::user()->id)
            ->where('active', true)
            ->exists();
        $finalBidCount = $auction->bids()->count();

        $newBidsAdded = $finalBidCount - $initialBidCount;

        if (!($currentHighestBid && $currentHighestBid->bidder == Auth::user()->id)) {


            $newBids = $auction->bids()
                ->orderBy('value', 'desc') // Order by highest bid amount
                ->take($newBidsAdded)
                ->get();
            $newBidsUpdated = [];

            foreach ($newBids as $bid) {

                $activeAutoBid = AutoBid::where('auction', $auctionId)
                    ->where('bidder', $bid->bidder)
                    ->where('active', true)
                    ->first();

                if ($activeAutoBid) {
                    $bid->is_auto_bid_active = true;
                    $bid->auto_bid_max = $activeAutoBid->max; // Add the max value of the auto-bid
                } else {
                    $bid->is_auto_bid_active = false;
                    $bid->auto_bid_max = null; // Set to null if no active auto-bid exists
                }

                $newBidsUpdated[] = [
                    'username' => $bid->user->generalUser->username,
                    'timestamp' => $bid->timestamp->translatedFormat('d F, H:i'),
                    'bidderId' => $bid->bidder,
                    'value' => $bid->value,
                    'isAutoBidActive' => $bid->is_auto_bid_active,
                    'maxBid' => $bid->auto_bid_max,
                    'auctionId' => $auctionId,
                    'availableBalance' => $bid->user->available_balance,
                ];
            }

            if ($newBidsAdded !== 0) {
                $surprassedBidder = [];
                if ($currentHighestBid !== NULL) {
                    $surprassedBidder[] = [
                        'bidderId' => $currentHighestBid->bidder,
                        'availableBalance' => $currentHighestBid->user->available_balance,
                    ];
                }
                event(new AuctionBidUpdated($newBidsUpdated, $siteConfig->minimal_bid_interval, $surprassedBidder));
            }
        }
        $noAddedBids = $newBidsAdded == 0;
        Log::info($noAddedBids);
        $user->refresh();
        Log::info($user->available_balance);
        return response()->json(['success' => true, 'reply' => 'Max Bid Successfully placed ', 'bid' => $createdAutobid, 'isAutoBidActive' => $currentStatus, 'noAddedBids' => $noAddedBids, 'availableBalance' => $user->available_balance]);
    }


    public function placeBid(Request $request, $auctionId)
    {
        $auction = Auction::findOrFail($auctionId);
        // Minimal valid bid
        $minBidValue = $auction->minBidValid();

        // Get the current highest bid
        $currentHighestBid = Bid::where('auction', $auctionId)
            ->orderBy('value', 'desc')
            ->first();

        $initialBidCount = $auction->bids()->count();

        $siteConfig = SiteConfig::getSiteConfig();

        $bidValue = $request->input('value');



        $user = Auth::user()->user;

        if (Auth::user()->id == $auction->seller_id) {
            return response()->json(['success' => false, 'reply' => 'You cannot bid on your own auction']);
        }

        if ($auction->auction_state != 'Active') {
            return response()->json(['success' => false, 'reply' => 'Auction already finished']);
        }

        if ($user->blocked) {
            return response()->json(['success' => false, 'reply' => 'You have been blocked! Bid cannot be placed']);
        }

        if (Auth::user()->role != 'Regular User') {
            return response()->json(['success' => false, 'reply' => 'Only regular users can bid']);
        }

        if ($currentHighestBid && $currentHighestBid->bidder == Auth::user()->id) {
            // If the user placed the highest bid, return an error
            return response()->json(['success' => false, 'reply' => 'You are already the highest bidder']);
        }


        if ($user) {
            $useravailable_balance = $user->available_balance;
        } else {
            return response()->json(['error' => 'User not found'], 404);
        }

        if ($bidValue < $minBidValue) {
            // Return a custom error message for bid value validation
            return response()->json([
                'success' => false,
                'reply' => 'The bid must be higher than €' . number_format($minBidValue, 2),
            ]);
        }

        if ($useravailable_balance < $bidValue) {
            // If insufficient funds, return an error early
            return response()->json(['success' => false, 'reply' => 'Insufficient funds']);
        }

        try{
            $bid = new Bid([
                'value' => $bidValue,
                'auction' => $auctionId,
                'bidder' => $request->user()->id,
            ]);
    
            $bid->save();
    
        }
        catch (\Throwable $e) {
            return response()->json(['success' => false, 'reply' => 'Auction already finished']);
        }

        $finalBidCount = $auction->bids()->count();

        $newBidsAdded = $finalBidCount - $initialBidCount;

        $newBids = $auction->bids()
            ->orderBy('value', 'desc') // Order by highest bid amount
            ->take($newBidsAdded)
            ->get();

        $newBidsUpdated = [];

        foreach ($newBids as $bid) {

            $activeAutoBid = AutoBid::where('auction', $auctionId)
                ->where('bidder', $bid->bidder) // Use the bidder ID from the current bid
                ->where('active', true)
                ->first();

            if ($activeAutoBid) {
                $bid->is_auto_bid_active = true;
                $bid->auto_bid_max = $activeAutoBid->max; // Add the max value of the auto-bid
            } else {
                $bid->is_auto_bid_active = false;
                $bid->auto_bid_max = null; // Set to null if no active auto-bid exists
            }

            $newBidsUpdated[] = [
                'username' => $bid->user->generalUser->username,
                'timestamp' => $bid->timestamp->translatedFormat('d F, H:i'),
                'bidderId' => $bid->bidder,
                'value' => $bid->value,
                'isAutoBidActive' => $bid->is_auto_bid_active,
                'maxBid' => $bid->auto_bid_max,
                'auctionId' => $auctionId,
                'availableBalance' => $bid->user->available_balance,
            ];
        }
        $surprassedBidders = [];
        if ($currentHighestBid !== NULL) {
            $surprassedBidders[] = [
                'bidderId' => $currentHighestBid->bidder,
                'availableBalance' => $currentHighestBid->user->available_balance,
            ];
        }

        event(new AuctionBidUpdated($newBidsUpdated, $siteConfig->minimal_bid_interval, $surprassedBidders));

        if (count($newBidsUpdated) == 2) {
            $surprassedBidders[] = [
                'bidderId' => $newBidsUpdated[0]['bidderId'],
                'availableBalance' => $newBidsUpdated[0]['availableBalance'],
            ];
        }

        foreach ($surprassedBidders as $bidder) {
            event(new UpdateAvailableBalance($bidder['bidderId'], $bidder['availableBalance']));
        }

        return response()->json(['success' => true, 'reply' => 'Bid Successfully Placed']);
    }

    public function loadMoreBids($auctionId)
    {
        $auction = Auction::findOrFail($auctionId);

        // Fetch all the bids sorted by the highest value first (from highest to lowest)
        $bids = $auction->bids()
            ->orderBy('value', 'desc')  // Sort by bid value in descending order
            ->skip(3)                   // Skip the first 3 bids already displayed
            ->get();
        return response()->json([
            'bids' => $bids->map(function ($bid) {
                return [
                    'username' => $bid->user->generalUser->username,
                    'timestamp' => $bid->timestamp->translatedFormat('d F, H:i'),
                    'value' => number_format($bid->value, 0, '.', ','),
                    'bidderId' => $bid->bidder,
                ];
            }),
        ]);
    }

    public function cancelAuction(int $id)
    {
        $auction = Auction::findOrFail($id);

        if (Auth::user()->id !== $auction->seller_id && Auth::user()->role !== 'Admin') {
            return response()->json(['success' => false, 'error' => 'Unauthorized action'], 403);
        }

        if (Auth::user()->id === $auction->seller_id) {
            $bidCount = $auction->bids()->count();

            if ($bidCount === 0) {

                $auction->update([
                    'auction_state' => 'Canceled'
                ]);

                return response()->json(['success' => true, 'reply' => 'Auction Successfully Canceled']);
            } else {
                return response()->json(['success' => false, 'reply' => 'Cannot cancel the auction, there are bids placed']);
            }
        } elseif (Auth::user()->role === 'Admin') {

            $auction->update([
                'auction_state' => 'Canceled'
            ]);

            AdminChange::create([
                'description' => 'Canceled auction of' . $auction->name,
                'admin' => Auth::id(),
            ]);

            return response()->json(['success' => true, 'reply' => 'Auction Successfully Canceled']);

        }
    }

    /**
     * Search auctions based on a search query.
     */
    public function search(Request $request)
    {
        // Initialize $auctions as an empty array
        $auctions = [];

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
                ->where('auction_state', 'Active')
                ->search($search, $location, $category, $fromBid, $toBid, $attributes)
                ->orderBy('end_time')
                ->paginate(12);

        } catch (\Exception $e) {
            // Return an empty paginated result in case of error
            dd($e);

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

        // Return the view with auctions (paginated) and categories
        return view('auctions', compact('auctions', 'categories', 'totalAuctions', 'selectedCategory', 'search', 'location', 'fromBid', 'toBid'));
    }

    public function advertise(Request $request)
    {
        session()->flash('settings', true);

        $auction = Auction::findOrFail($request['auctionId']);

        if (Gate::denies('advertise', $auction)) {
            return redirect('/')->with('error', 'You cannot advertise this auction');
        }

        $user = $request->user()->specificRole();

        $minimumEndTime = now();
        $maximumEndTime = $auction->end_time;


        if ($auction->advertised)
            $minimumEndTime = $auction->activeLastAdvertisement()->end_time;

        $minFormated = $minimumEndTime->format('d/m/y');
        $maxFormated = $maximumEndTime->format('d/m/y');

        $request->validate([
            'endDate' => "required|date|after:$minimumEndTime|before:$maximumEndTime",
        ], [
            'endDate.required' => 'Required date.',
            'endDate.date' => 'Invalid date.',
            'endDate.after' => "Needs to be greater than $minFormated",
            'endDate.before' => "Needs to be smaller than $maxFormated",
        ]);

        $endTime = Carbon::parse($request['endDate']);
        $numberDays = ceil($minimumEndTime->diffInDays($endTime, true));
        $adPrice = SiteConfig::getSiteConfig()->ad_price;
        $discountedAdPrice = SiteConfig::getSiteConfig()->discounted_ad_price;

        $dailyPrice = $user->subscribed ? $discountedAdPrice : $adPrice;
        $totalPrice = $dailyPrice * $numberDays;

        // Check wallet balance
        if ($user->available_balance < $totalPrice) {
            return back()->with([
                'error' => 'Insufficient funds',
            ]);
        }

        // Create the advertisement
        Advertisement::create([
            'start_time' => $minimumEndTime,
            'end_time' => $endTime,
            'cost' => $totalPrice,
            'auction_id' => $auction->id,
        ]);

        // Always return the session with 'settings' => true
        return back()->with([
            'success' => 'Auction successfully advertised',
        ]);
    }

    public function showCreate(Request $request): View|RedirectResponse
    {
        if (!((Auth::user()->role === "Regular User") && (!Auth::user()->user->blocked))) {
            return redirect('/')->with('error', 'You cannot create auctions');
        }

        $categories = Category::all();

        return view('auction.create', [
            'user' => $request->user(),
            'categories' => $categories,
        ]);
    }


    public function showEdit(Request $request, int $auctionId): View|RedirectResponse
    {
        session()->flash('settings', 'true');
        $auction = Auction::findOrFail($auctionId);
        if($request->user()->role !== 'Admin' && $request->user()->id !== $auction->seller_id)
        {
            return back()->with('error', 'Cannot edit this auction');
        }

        if(($auction->bids->count() != 0) && $request->user()->id === $auction->seller_id)
        {
            return back()->with('error', 'Auction has bids. Cannot be edited');
        }

        if($auction->auction_state != 'Active')
        {
            return redirect('/')->with('error', 'Cannot edit finished auctions');
        }

        $categories = Category::all();

        return view('auction.create', [
            'user' => $request->user(),
            'categories' => $categories,
            'auction' => $auction,
        ]);
    }


    public function create(Request $request): View|RedirectResponse
    {
        if (!((Auth::user()->role === "Regular User") && (!Auth::user()->user->blocked))) {
            return redirect('/')->with('error', 'You cannot create auctions');
        }

        $minimumEndTime = now()->addHours(2);
        $maxEndTime = now()->addDays(31);

        $formatedMin = $minimumEndTime->format('d/m/y H:i');
        $formatedMax = $maxEndTime->format('d/m/y H:i');



        $request->validate([
            'title' => 'required|string|max:40',
            'description' => 'required|string|max:500',
            'minimumBid' => 'required|numeric',
            'endTime' => "required|date|after:$minimumEndTime|before:$maxEndTime",
            'location' => "required|string",
            'category' => "required|numeric|min:0",
            'photos' => "required|array|min:2|max:7",
            'photos.*' => "required|image|mimes:jpg,png,jpeg|max:2048",
        ], [
            'required' => 'The :attribute is required.',
            'photos.required' => 'Photos are required.',

            'title.max' => 'Title too big',
            'description.max' => 'Description too big',

            'minimumBid.numeric' => 'Invalid format.',

            'endTime.date' => 'Invalid date format.',
            'endTime.after' => "Must be after $formatedMin.",
            'endTime.before' => "Must be before $formatedMax.",

            'category.numeric' => 'Invalid format.',
            'category.min' => 'Category is required',

            'photos.min' => 'Minimum of 2 photos',
            'photos.max' => 'Maximum of 7 photos',

            'photos.*.image' => 'No image files uploaded',
            'photos.*.mimes' => 'Only JPG, PNG, or JPEG file types are accepted',
            'photos.*.max' => 'Each photo must not exceed 2MB in size.',

        ]);


        $categoryId = $request['category'];
        $category = Category::find($categoryId);
        if (!$category) {
            return back()->withErrors(['category' => 'The selected category is invalid.']);
        }

        // Validate Attributes
        $data = $request->all();
        $attributeValues = [];
        foreach ($data as $key => $value) {
            $attributeName = null;
            // Enum attribute
            if (preg_match('/^enum(\d+)-([a-z]+)-attribute$/', $key, $matches)) {

                $attributeCategoryId = (int) $matches[1];
                $attributeName = $matches[2];
            }
            // Other type attribute
            elseif (preg_match('/^c(\d+)-([a-z]+)-attribute$/', $key, $matches)) {
                $attributeCategoryId = (int) $matches[1];
                $attributeName = $matches[2];
            }

            // Validate attribute
            if ($attributeName) {
                if ($categoryId != $attributeCategoryId) {
                    return back()->withErrors(['attributes' => 'Invalid attributes.']);
                }

                if ($value != NULL)
                {
                    $value = $category->checkAttribute($attributeName, $value);
                    if ($value == null) {
                        return back()->withErrors(['attributes' => 'Invalid attribute selection.']);
                    }
                    $attributeValues[$attributeName] = $value;
                }
            }
        }

        $newAuction = new Auction([
            'name' => $request['title'],
            'description' => $request['description'],
            'location' => $request['location'],
            'end_time' => $request['endTime'],
            'minimum_bid' => $request['minimumBid'],
            'auction_state' => 'Active',
            'category_id' => $request['category'],
            'attribute_values' => $attributeValues,
            'location' => $request['location'],
        ]);
        $newAuction->seller_id = Auth::user()->id;
        $newAuction->save();


        // Save photos
        ImageController::auctionPhotos($newAuction, [], $request['photos'] ?? []);

        return redirect('/auctions/' . $newAuction->id)->with('success', 'Auction successfully created');
    }


    public function edit(Request $request, int $id): View|RedirectResponse
    {
        $auction = Auction::findOrFail($id);

        if ($request->user()->role !== 'Admin' && $request->user()->id !== $auction->seller_id) {
            return redirect('/')->with('error', 'Cannot edit this auction');
        }

        if(($auction->bids->count() != 0) && $request->user()->id === $auction->seller_id)
        {
            return redirect('/')->with('error', 'Auction has bids. Cannot be edited');
        }

        if($auction->auction_state != 'Active')
        {
            return redirect('/')->with('error', 'Cannot edit finished auctions');
        }


        // change minimum time
        $minimumEndTime = now()->addHours(2);
        $maxEndTime = now()->addDays(31);

        $formatedMin = $minimumEndTime->format('d/m/y H:i');
        $formatedMax = $maxEndTime->format('d/m/y H:i');


        $request->validate([
            'title' => 'required|string|max:40',
            'description' => 'required|string|max:500',
            'minimumBid' => 'numeric|min:1',
            'endTime' => "required|date|after:$minimumEndTime|before:$maxEndTime",
            'location' => "required|string",
            'category' => "required|numeric",
            'photos' => "array|max:7",
            'photos.*' => "image|mimes:jpg,png,jpeg|max:2048",
            'oldPhotos' => "array"
        ], [
            'required' => 'The :attribute is required.',
            'photos.required' => 'Photos are required.',

            'title.max' => 'Title too big',
            'description.max' => 'Description too big',

            'minimumBid.numeric' => 'Invalid format.',
            'minimumBid.min' => 'Invalid format',

            'endTime.date' => 'Invalid date format.',
            'endTime.after' => "Must be after $formatedMin.",
            'endTime.before' => "Must be before $formatedMax.",

            'category.numeric' => 'Invalid format.',

            'photos.min' => 'Minimum of 2 photos',
            'photos.max' => 'Maximum of 7 photos',

            'photos.*.image' => 'No image files uploaded',
            'photos.*.mimes' => 'Only JPG, PNG, or JPEG file types are accepted',
            'photos.*.max' => 'Each photo must not exceed 2MB in size.',

        ]);


        $photoCount = count($request['photos'] ?? []);
        $oldPhotoCount = count($request['oldPhotos'] ?? []);

        if ($photoCount + $oldPhotoCount > 7)
            return back()->withErrors(['photos' => 'Maximum of 7 photos.']);
        if ($photoCount + $oldPhotoCount < 2)
            return back()->withErrors(['photos' => 'Minimum of 2 photos']);




        $categoryId = $request['category'];
        $category = Category::find($categoryId);

        if (!$category) {

            return back()->withErrors(['category' => 'The selected category is invalid.']);
        }

        // Validate Attributes
        $data = $request->all();
        $attributeValues = [];
        foreach ($data as $key => $value) {
            $attributeName = null;
            // Enum attribute
            if (preg_match('/^enum(\d+)-([a-z]+)-attribute$/', $key, $matches)) {

                $attributeCategoryId = (int) $matches[1];
                $attributeName = $matches[2];
            }
            // Other type attribute
            elseif (preg_match('/^c(\d+)-([a-z]+)-attribute$/', $key, $matches)) {
                $attributeCategoryId = (int) $matches[1];
                $attributeName = $matches[2];
            }

            // Validate attribute
            if ($attributeName) {
                if ($categoryId != $attributeCategoryId) {
                    return back()->withErrors(['attributes' => 'Invalid attribute selection.']);
                }

                if ($value != NULL)
                {
                    $value = $category->checkAttribute($attributeName, $value);
                    if ($value == null) {
                        return back()->withErrors(['attributes' => 'Invalid attribute selection.']);
                    }
                    $attributeValues[$attributeName] = $value;
                }

            }
        }

        $auction->update([
            'name' => $request['title'],
            'description' => $request['description'],
            'location' => $request['location'],
            'end_time' => $request['endTime'],
            'minimum_bid' => $request['minimumBid'],
            'auction_state' => 'Active',
            'category_id' => $request['category'],
            'attribute_values' => $attributeValues,
        ]);

        // Save photos
        ImageController::auctionPhotos($auction, $request['oldPhotos'] ?? [], $request['photos'] ?? []);

        if ($request->user()->role === 'Admin') {
            AdminChange::create([
                'description' => 'Edited auction ' . $auction->name,
                'admin' => $request->user()->id,
            ]);
        }


        return redirect('/auctions/' . $auction->id)->with('success', 'Auction successfully edited');
    }

    public function cancelAutoBid(int $auction)
    {
        $userId = Auth::id();

        // Find the active autobid for this user and auction
        $autoBid = AutoBid::where('auction', $auction)
            ->where('bidder', $userId)
            ->where('active', true)
            ->first();


        if ($autoBid) {
            $autoBid->delete(); // Remove the AutoBid entry from the database
            $userBalance = Auth::user()->user->available_balance;
            Log::info($userBalance);
            return response()->json([
                'success' => true,
                'reply' => 'Your AutoBid has been cancelled',
                'availableBalance' => $userBalance,
            ]);
        }

        return response()->json([
            'success' => false,
            'reply' => 'No active AutoBid found'
        ]);
    }

    public function sendMessage(Request $request)
    {

        // Validate the inputs
        $validated = $request->validate([
            'auction_id' => 'required|integer',
            'message' => 'required|string',
        ]);

        // Create the message record in the database
        $now = now();
        try{
            $message = new Message([
                'content' => $validated['message'],
                'auction' => $validated['auction_id'],
                'general_user_id' => Auth::id(), // Get the current authenticated user's ID
                'timestamp' => $now, // Get the current timestamp
            ]);
    
            $message->save();
        }
        catch (\Throwable $e) {
            return response()->json(['success' => false, 'reply' => 'Auction already finished']);
        }

        // Prepare the message with additional details
        $messageData = [
            'username' => Auth::user()->username,
            'text' => $validated['message'],
            'timestamp' => $now->format('Y-m-d H:i:s.u'),  // Get the current timestamp
            'auction' => $validated['auction_id'],
            'general_user_id' => Auth::id(),
            'formatted_date' => $now->format('d/m/Y'),
        ];

        $roleUserIds = GeneralUser::whereIn('role', ['Admin', 'Expert'])->pluck('id')->toArray();

        $subscribedUserIdsAndNotBlocked = User::where('subscribed', true)
            ->where('blocked', '!=', true)
            ->pluck('id')
            ->toArray();

        $allUserIds = array_merge($roleUserIds, $subscribedUserIdsAndNotBlocked);

        // Exclude the authenticated user
        $filteredUserIds = array_filter($allUserIds, fn($id) => $id !== Auth::user()->id);

        $user = GeneralUser::find(Auth::id());

        $userImage = $user->getProfileImage();
            

        // Broadcast the message with auctionId
        broadcast(new AuctionSendMessage($messageData, $validated['auction_id'], $filteredUserIds, $userImage));

        return response()->json(['success' => true, 'message' => $messageData]);
    }

    public function loadMoreMessages(Request $request, $auctionId)
    {

        $timestamp = $request->input('timestamp'); // Get the timestamp from the request body
        $currentUserId = Auth::id(); // Get the current user's ID
        $auction = Auction::findOrFail($auctionId); // Find the auction by ID

        // Fetch messages older than the provided timestamp, ordered by descending timestamp
        $messages = $auction->messages()
            ->where('timestamp', '<', $timestamp)
            ->orderBy('timestamp', 'desc')
            ->limit(20)
            ->get()->map(function ($message) use ($currentUserId) {
                return [
                    'content' => $message->content,
                    'timestamp' => $message->timestamp,
                    'userId' => $message->generalUser->id,
                    'username' => $message->generalUser->username,
                    'isMine' => $message->generalUser->id === $currentUserId, // Check ownership
                    'formattedDate' => \Carbon\Carbon::parse($message->timestamp)->format('d/m/Y'),
                    'formattedTime' => \Carbon\Carbon::parse($message->timestamp)->format('H:i'),
                    'profileImage' => $message->generalUser->getProfileImage(),
                ];
            });
        // Check if there are more messages
        $hasMore = $auction->messages()
            ->where('timestamp', '<', $messages->last()->timestamp ?? $timestamp)
            ->exists();

        return response()->json([
            'messages' => $messages,
            'hasMore' => $hasMore
        ]);
    }

    public function checkStatusAutoBid($auctionId)
    {
        // Retrieve current auction and user info to check auto-bid status
        $userId = Auth::id();  // Get the current user's ID

        // Assuming there's a method to check if the user has an active auto-bid
        $isActive = AutoBid::where('bidder', $userId)
            ->where('auction', $auctionId)
            ->where('active', true)  // Check the condition for active auto-bid
            ->exists();

        return response()->json(['isActive' => $isActive]);
    }


    public function report(Request $request, int $auctionId): RedirectResponse
    {
        $reportedAuction = Auction::findOrFail($auctionId);

        if (Gate::denies('report', $reportedAuction)) {
            return back()->with('error', 'You are not authorized to report this auction');
        }

        if ($reportedAuction->isReportedBy($request->user()->id)) {
            return back()->with('error', 'You have already report this auction');
        }

        $request->validateWithBag('auctionReport', ([
            'motive' => ['required', 'string', 'max:100'],
        ]));

        ReportAuction::create([
            'description' => $request->motive,
            'reporter' => $request->user()->id,
            'auction' => $auctionId,
        ]);

        return Redirect::to("/auctions/{$auctionId}")->with('success', 'Auction Sucessfully Reported');

    }

    public function requestEvaluation(Request $request, int $auctionId): RedirectResponse
    {
        $auction = Auction::findOrFail($auctionId);

        if ($request->user()->role !== 'Regular User' || !$request->user()->user->subscribed || $request->user()->user->blocked) {
            return back()->with('error', 'You cannot request an evaluation');
        }

        if ($auction->evaluation_requested) {
            return back()->with('error', 'Evaluation Request Already Made');
        }
        
        if($auction->end_time <= now()){
            return back()->with('error', 'Auction already Finished');
        }

        $auction->update([
            'evaluation_requested' => true,
        ]);

        return Redirect::to("/auctions/{$auctionId}")->with('success', 'Evaluation Successfully Requested');

    }

    public function evaluate(Request $request, int $auctionId): RedirectResponse
    {

        $auction = Auction::findOrFail($auctionId);

        //alerta erro
        if ($request->user()->role !== 'Expert') {
            return back()->with('error', 'Only experts can make evaluations');
        }

        if (!$auction->evaluation_requested) {
            return back()->with('error', 'Evaluation not requested');
        }

        if ($auction->evaluation !== null) {
            return back()->with('error', 'Auction already evaluated');
        }


        $val = $request->validateWithBag('auctionEvaluation', ([
            'evaluation' => ['required', 'numeric', 'gt:0'],
        ]));


        $auction->update([
            'evaluation' => $request->evaluation,
            'expert' => $request->user()->id,
        ]);


        return Redirect::to("/auctions/{$auctionId}")->with('success', 'Successfully evaluated');

    }



}
