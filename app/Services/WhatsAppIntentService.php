<?php

namespace App\Services;

use App\Models\CustomerCart;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;

class WhatsAppIntentService
{
    public const MARKETING_PURPOSES = [
        'new_product_launch' => 'New Product Launch',
        'limited_time_offer' => 'Limited-Time Offer',
        'coupon_offer' => 'Coupon Offer',
        'festival_offer' => 'Festival Offer',
        'back_in_stock' => 'Back-in-Stock Alert',
        'abandoned_cart_reminder' => 'Abandoned Cart Reminder',
        'repeat_purchase_reminder' => 'Repeat-Purchase Reminder',
        'thank_you' => 'Thank-You Message',
        'feedback_request' => 'Feedback Request',
        'product_recommendation' => 'Personalized Product Recommendation',
        'custom' => 'Custom Message',
    ];

    public function normalizeIndianPhone(?string $phone): ?string
    {
        if (! $phone) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone);

        if (strlen($digits) === 10 && preg_match('/^[6-9]\d{9}$/', $digits)) {
            return '91'.$digits;
        }

        if (strlen($digits) === 12 && str_starts_with($digits, '91') && preg_match('/^91[6-9]\d{9}$/', $digits)) {
            return $digits;
        }

        return null;
    }

    public function defaultMessage(User $customer, string $purpose, ?Order $order = null, ?CustomerCart $cart = null): string
    {
        $storeUrl = route('home');
        $name = trim($customer->name) ?: 'Customer';

        if ($cart && $purpose === 'abandoned_cart_reminder') {
            return "Hi {$name},\n\nYou still have products waiting in your Sushako Shopping cart.\n\nReview your cart:\n".$this->cartRecoveryUrl($cart)."\n\nReply here if you need assistance.\n\n- Sushako Shopping";
        }

        if ($order) {
            return "Hi {$name},\n\nYour Sushako order {$order->order_number} is currently ".str($order->status)->replace('_', ' ')->title().".\n\nTrack your order:\n".route('orders.track')."\n\nFor support, reply to this message.\n\n- Sushako Shopping";
        }

        return "Hi {$name},\n\nExplore available products from Sushako Shopping:\n\n{$storeUrl}\n\nReply here if you need assistance.\n\n- Sushako Shopping";
    }

    public function intentUrl(User $customer, string $message): string
    {
        $phone = $this->normalizeIndianPhone($customer->phone);

        if (! $phone) {
            throw ValidationException::withMessages(['phone' => 'This customer does not have a valid Indian WhatsApp number.']);
        }

        return 'https://wa.me/'.$phone.'?text='.rawurlencode($message);
    }

    public function assertMarketingAllowed(User $customer, string $purpose): void
    {
        if (! array_key_exists($purpose, self::MARKETING_PURPOSES)) {
            throw ValidationException::withMessages(['purpose' => 'Choose an approved WhatsApp marketing purpose.']);
        }

        if (! $customer->whatsapp_marketing_consent) {
            throw ValidationException::withMessages(['purpose' => 'Marketing Consent Not Available']);
        }
    }

    public function cartRecoveryUrl(CustomerCart $cart): string
    {
        return URL::temporarySignedRoute('cart.recover', $cart->recovery_token_expires_at ?? now()->addDays(14), [
            'token' => $cart->recovery_token,
        ]);
    }
}
