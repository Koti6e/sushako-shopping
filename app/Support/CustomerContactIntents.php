<?php

namespace App\Support;

class CustomerContactIntents
{
    public static function tel(?string $phone): ?string
    {
        $number = self::normalizedIndianMobile($phone);

        return $number ? 'tel:+'.$number : null;
    }

    public static function whatsApp(?string $phone, bool $allowed = true): ?string
    {
        if (! $allowed) {
            return null;
        }

        $number = self::normalizedIndianMobile($phone);

        return $number ? 'https://wa.me/'.$number : null;
    }

    public static function email(?string $email): ?string
    {
        $email = trim((string) $email);

        return filter_var($email, FILTER_VALIDATE_EMAIL) ? 'mailto:'.$email : null;
    }

    public static function maps(?string $url): ?string
    {
        $url = trim((string) $url);

        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        $parts = parse_url($url);
        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = strtolower((string) ($parts['host'] ?? ''));

        if (! in_array($scheme, ['http', 'https'], true)) {
            return null;
        }

        $allowedHosts = ['google.com', 'www.google.com', 'maps.google.com', 'maps.app.goo.gl', 'goo.gl'];

        return in_array($host, $allowedHosts, true) || str_ends_with($host, '.google.com') ? $url : null;
    }

    public static function addressFromOrder($order): string
    {
        return collect([
            $order->address_line_1 ?? null,
            $order->address_line_2 ?? null,
            $order->landmark ? 'Landmark: '.$order->landmark : null,
            $order->city ?? null,
            $order->pincode ?? null,
        ])->filter(fn ($part) => filled($part))->join(', ');
    }

    public static function normalizedIndianMobile(?string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);

        if (strlen($digits) === 10 && preg_match('/^[6-9]\d{9}$/', $digits)) {
            return '91'.$digits;
        }

        if (strlen($digits) === 12 && str_starts_with($digits, '91') && preg_match('/^91[6-9]\d{9}$/', $digits)) {
            return $digits;
        }

        return null;
    }
}
