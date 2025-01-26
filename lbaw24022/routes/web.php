<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\AuctionController;
use App\Http\Controllers\BuyerController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\PartialsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\SellerController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\ManagementController;
use App\Http\Controllers\SocialLoginController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\EvaluationsController;
use App\Http\Middleware\GuestOrVerified;
use App\Http\Controllers\PayPalController;
use App\Models\Auction;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\NotificationController;

Route::middleware(GuestOrVerified::class)->group(function () {
    Route::get('/', [WelcomeController::class, 'index'])->name('welcome');
    Route::get('/about', [AboutController::class, 'index'])->name('about');
    Route::get('/contact', [ContactController::class, 'index'])->name('contact');
    Route::get('/auctions', [AuctionController::class, 'search'])->name('auctions');
    Route::get('/auctions/{id}', [AuctionController::class, 'show'])->name('auction.show');
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
});

Route::middleware('verified')->group(function () {


    Route::get('/profile/{userId}', [ProfileController::class, 'show'])->name('profile.show');

    Route::post('/profile/{userId}/report', [ProfileController::class, 'report'])->name('user.report');

    Route::post('/profile/{userId}/rate', [ProfileController::class, 'rate'])->name('user.rate');

    Route::post('/appeal', [WelcomeController::class, 'appeal'])->name('user.appeal');

    Route::delete('/settings/profile/{userId}/image', [ImageController::class, 'destroy'])->name('profileImage.destroy');
    Route::patch('/settings/profile/{userId}/image', [ImageController::class, 'uploadProfileImage'])->name('profileImage.upload');

    Route::get('/settings/{section}/{userId}', [SettingsController::class, 'show'])->name('settings.show');
    Route::patch('/settings/{section}/{userId}', [SettingsController::class, 'update'])->name('settings.update');
    Route::delete('/settings/{userId}', [SettingsController::class, 'destroy'])->name('profile.destroy');

    Route::post('/settings/block/{userId}/block', [SettingsController::class, 'block'])->name('user.block');
    Route::post('/settings/block/{userId}/unblock', [SettingsController::class, 'unblock'])->name('user.unblock');
    Route::post('/settings/block/{userId}/rejectAppeal', [SettingsController::class, 'rejectAppeal'])->name('user.rejectAppeal');

    Route::post('/followUser/{user}', [FollowController::class, 'followUser']);

    Route::post('/followAuction/{auction}', [FollowController::class, 'followAuction']);

    Route::get('/seller-dashboard', [SellerController::class, 'index'])->name('seller-dashboard');
    Route::post('/shipAuction/{auction}', [SellerController::class, 'shipAuction']);
    Route::get('/buyer-dashboard', [BuyerController::class, 'index'])->name('buyer-dashboard');
    Route::post('/addDeliveryLocation/{auctionId}', [BuyerController::class, 'addDeliveryLocation']);

    Route::post('/auctions/{id}/cancel', [AuctionController::class, 'cancelAuction']);
    Route::post('management/createAccounts', [RegisteredUserController::class, 'store'])->name('management.newAccount');
    Route::get('/management/{section}', [ManagementController::class, 'show'])->name('management.show');
    Route::get('/management/categories/{categoryId}', [CategoryController::class, 'show'])->name('management.editCategory');
    Route::patch('/categories/{categoryId}', [CategoryController::class, 'update']);
    Route::delete('/categories/{categoryId}', [CategoryController::class, 'destroy']);
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::post('/management/systemSettings', [ManagementController::class, 'updateSystemSettings'])->name('management.systemSettings');


    Route::post('/notifications', [NotificationController::class, 'getNotifications']);
    Route::post('/notifications/mark-as-viewed', [NotificationController::class, 'markAsViewed']);
    Route::post('/notifications/mark-all-viewed', [NotificationController::class, 'markAllNotificationsAsViewed']);
    Route::post('/notifications/{notificationId}/clear', [NotificationController::class, 'markAsViewed']);
    Route::post('/notifications/unread-count', [NotificationController::class, 'getUnreadNotificationsCount']);

    Route::get('/auction/create', [AuctionController::class, 'showCreate'])->name('auction.create.show');
    Route::post('/auction/create', [AuctionController::class, 'create'])->name('auction.create');
    Route::post('/send-message', [AuctionController::class, 'sendMessage'])->name('send.message');
    Route::post('/auctions/{auctionId}/loadMoreMessages', [AuctionController::class, 'loadMoreMessages']);
    Route::get('/auction/{id}/edit', [AuctionController::class, 'showEdit'])->name('auction.edit.show');
    Route::post('/auction/{id}/edit', [AuctionController::class, 'edit'])->name('auction.edit');
    Route::post('/auctions/{auctionId}/place-bid', [AuctionController::class, 'placeBid']);

    Route::get('/auctions/{auctionId}/more-bids', [AuctionController::class, 'loadMoreBids'])->name('auctions.moreBids');

    Route::post('/auction/{id}/advertise', [AuctionController::class, 'advertise'])->name('auction.advertise');
    Route::post('/auction/{id}/report', [AuctionController::class, 'report'])->name('auction.report');

    Route::get('/subscription', [SubscriptionController::class, 'show'])->name('subscription.show');
    Route::post('/subscription', [SubscriptionController::class, 'subscribe'])->name('subscription.sub');

    Route::get('/wallet', [WalletController::class, 'show'])->name('wallet.show');
    Route::post('/wallet/withdraw', [WalletController::class, 'withdraw'])->name('wallet.withdraw');

    Route::post('/auctions/{auctionId}/place-max-bid', [AuctionController::class, 'placeMaxBid']);
    Route::get('/auctions/{auctionId}/checkAutoBidStatus', [AuctionController::class, 'checkStatusAutoBid']);
    Route::delete('/auctions/{auctionId}/cancel-autobid', [AuctionController::class, 'cancelAutoBid']);


    Route::get('/evaluations', [EvaluationsController::class, 'show'])->name('expert.evaluationsRequested');
    Route::post('/auctions/{auctionId}/evaluate', [AuctionController::class, 'evaluate'])->name('auction.evaluate');
    Route::post('/auctions/{auctionId}/requestEvaluation', [AuctionController::class, 'requestEvaluation'])->name('auction.requestEvaluation');



    Route::get('/partials/new-attribute', [PartialsController::class, 'newAttribute']);
    Route::get('/partials/shipping-forms/{id}', [PartialsController::class, 'shippingForms']);


    // Paypal routes

    Route::post('/api/orders', [PayPalController::class, 'createOrder']);
    Route::post('/api/orders/{id}/capture', [PayPalController::class, 'captureOrder']);


});

Route::get('/socialite/{driver}', [SocialLoginController::class, 'toProvider'])->where('driver', 'google');
Route::get('/auth/{driver}/login', [SocialLoginController::class, 'handleCallback']);

require __DIR__ . '/auth.php';
