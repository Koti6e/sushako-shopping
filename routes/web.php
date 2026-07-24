<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OperationsController;
use App\Http\Controllers\Admin\OrderWorkflowController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\AdminCategoryController;
use App\Http\Controllers\AdminProductController;
use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\Auth\CustomerAuthController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CookieConsentController;
use App\Http\Controllers\StorefrontController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StorefrontController::class, 'home'])->name('home');
Route::get('/shop', [StorefrontController::class, 'shop'])->name('shop');
Route::get('/search', [StorefrontController::class, 'search'])->name('search');
Route::get('/departments/{slug}', [StorefrontController::class, 'department'])->name('department.show');
Route::get('/category/{slug}', [StorefrontController::class, 'category'])->name('category.show');
Route::get('/products/{slug}', [StorefrontController::class, 'product'])->name('products.show');
Route::view('/return-refund-policy', 'policies.return-refund')->name('policies.return-refund');
Route::view('/cookie-policy', 'policies.cookie')->name('policies.cookie');
Route::post('/cookie-consent/essential', [CookieConsentController::class, 'store'])->name('cookie-consent.essential.store');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [CustomerAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [CustomerAuthController::class, 'login'])->name('login.store');
    Route::get('/register', [CustomerAuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [CustomerAuthController::class, 'register'])->name('register.store');
    Route::get('/admin/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
    Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('admin.login.store');
});

Route::get('/login/google', [GoogleAuthController::class, 'redirect'])->middleware('guest')->name('login.google');
Route::get('/login/google/callback', [GoogleAuthController::class, 'callback'])->middleware('guest')->name('login.google.callback');
Route::get('/cart', [CartController::class, 'show'])->name('cart.empty');
Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
Route::patch('/cart/{key}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/{key}', [CartController::class, 'destroy'])->name('cart.destroy');
Route::post('/buy-now', [CartController::class, 'buyNow'])->name('cart.buy-now');
Route::post('/wishlist', [CartController::class, 'wishlist'])->name('wishlist.toggle');

Route::middleware(['auth', 'active', 'role:customer'])->group(function (): void {
    Route::post('/logout', [CustomerAuthController::class, 'logout'])->name('logout');
    Route::get('/complete-profile', [CustomerAuthController::class, 'completeProfile'])->name('profile.complete');
    Route::put('/complete-profile', [CustomerAuthController::class, 'updateProfile'])->name('profile.complete.update');

    Route::middleware('mobile.required')->group(function (): void {
        Route::get('/account', [CustomerAuthController::class, 'account'])->name('account.show');
        Route::put('/account/profile', [CustomerAuthController::class, 'updateProfile'])->name('account.profile.update');
        Route::put('/account/password', [CustomerAuthController::class, 'updatePassword'])->name('account.password.update');
        Route::post('/account/addresses', [CustomerAuthController::class, 'storeAddress'])->name('account.addresses.store');
        Route::put('/account/addresses/{address}/default', [CustomerAuthController::class, 'setDefaultAddress'])->name('account.addresses.default');
        Route::delete('/account/addresses/{address}', [CustomerAuthController::class, 'deleteAddress'])->name('account.addresses.destroy');
        Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout');
        Route::post('/checkout', [CheckoutController::class, 'place'])->name('checkout.place');
        Route::get('/orders/{order:order_number}/payment', [CheckoutController::class, 'payment'])->name('order.payment');
        Route::post('/orders/{order:order_number}/payment/cod', [CheckoutController::class, 'chooseCod'])->name('order.payment.cod');
        Route::get('/orders/success/{order:order_number}', [CheckoutController::class, 'success'])->name('order.success');
        Route::get('/orders/{order:order_number}/invoice', [CheckoutController::class, 'invoice'])->name('order.invoice');
        Route::post('/orders/{order:order_number}/payment/razorpay-test', [CheckoutController::class, 'confirmRazorpayPayment'])->name('order.payment.razorpay-test');
        Route::get('/track-order', [CheckoutController::class, 'track'])->name('orders.track');
        Route::post('/track-order', [CheckoutController::class, 'lookup'])->name('orders.track.lookup');
    });
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'active', 'role:super_admin'])->group(function (): void {
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/categories', [OperationsController::class, 'categories'])->name('categories.index');
    Route::get('/categories/create', [OperationsController::class, 'createCategory'])->name('categories.create');
    Route::post('/categories', [AdminCategoryController::class, 'store'])->name('categories.store');
    Route::delete('/categories/{category}', [AdminCategoryController::class, 'destroy'])->name('categories.destroy');
    Route::get('/products', [AdminProductController::class, 'index'])->name('products.index');
    Route::get('/products/create', [AdminProductController::class, 'create'])->name('products.create');
    Route::post('/products', [AdminProductController::class, 'store'])->name('products.store');
    Route::get('/products/{slug}/edit', [AdminProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{slug}', [AdminProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{slug}', [AdminProductController::class, 'destroy'])->name('products.destroy');
    Route::delete('/product-images/{image}', [AdminProductController::class, 'destroyImage'])->name('product-images.destroy');
    Route::get('/inventory', [OperationsController::class, 'inventory'])->name('inventory.index');
    Route::get('/orders', [OperationsController::class, 'orders'])->name('orders.index');
    Route::get('/orders/{order:order_number}', [OperationsController::class, 'orderShow'])->name('orders.show');
    Route::get('/orders/{order:order_number}/invoice', [CheckoutController::class, 'adminInvoice'])->name('orders.invoice');
    Route::put('/orders/{order:order_number}/workflow', [OrderWorkflowController::class, 'update'])->name('orders.workflow.update');
    Route::get('/customers', [OperationsController::class, 'customers'])->name('customers.index');
    Route::get('/settings/company', [SettingsController::class, 'company'])->name('settings.company');
    Route::put('/settings/company', [SettingsController::class, 'updateCompany'])->name('settings.company.update');
    Route::get('/settings/invoice', [SettingsController::class, 'invoice'])->name('settings.invoice');
    Route::put('/settings/invoice', [SettingsController::class, 'updateInvoice'])->name('settings.invoice.update');
    Route::get('/settings/tax', [SettingsController::class, 'tax'])->name('settings.tax');
    Route::post('/settings/tax/slabs', [SettingsController::class, 'storeTaxSlab'])->name('settings.tax-slabs.store');
    Route::put('/settings/tax/assignments', [SettingsController::class, 'updateTaxAssignments'])->name('settings.tax.assignments.update');
    Route::get('/settings/shipping', [SettingsController::class, 'shipping'])->name('settings.shipping');
    Route::put('/settings/shipping', [SettingsController::class, 'updateShipping'])->name('settings.shipping.update');
    Route::get('/settings/payments', [SettingsController::class, 'payments'])->name('settings.payments');
    Route::put('/settings/payments', [SettingsController::class, 'updatePayments'])->name('settings.payments.update');
    Route::get('/settings/{section}', [SettingsController::class, 'placeholder'])->name('settings.placeholder');
});
