<?php

namespace App\Support;

use App\Models\Setting;

/**
 * The owner's contact details (GEN-2, "contact" settings) and the cards built from them for
 * the Contact page. Header, off-canvas menu, banner and footer switch to these in M3.
 */
class ContactDetails
{
    /**
     * Lines per card, matching the theme's layout.
     */
    public const MAX_LINES = 2;

    /**
     * The settings and their values until they are saved.
     *
     * @var array{address_title: string, address_lines: list<string>, email_title: string, emails: list<string>, phone_title: string, phones: list<string>}
     */
    public const DEFAULTS = [
        'address_title' => 'Address',
        'address_lines' => [],
        'email_title' => 'E-mail',
        'emails' => [],
        'phone_title' => 'Call Me',
        'phones' => [],
    ];

    /**
     * The "Get in touch" text next to the contact form, used until the section is saved.
     *
     * @var array{eyebrow: string, title: string, subtitle: string}
     */
    public const GET_IN_TOUCH_DEFAULTS = [
        'eyebrow' => 'Get in touch',
        'title' => 'Elevate your brand with Me',
        'subtitle' => 'Have a project in mind or just want to say hello? Send me a message and I will get back to you within a day or two.',
    ];

    /**
     * The saved contact settings, with defaults for anything not saved yet.
     *
     * @return array{address_title: string, address_lines: list<string>, email_title: string, emails: list<string>, phone_title: string, phones: list<string>}
     */
    public static function all(): array
    {
        $saved = Setting::group('contact');
        $contact = [];

        // Stored values are checked for type, so a hand-edited row can not break the pages.
        foreach (self::DEFAULTS as $key => $default) {
            $value = $saved[$key] ?? $default;

            $contact[$key] = is_array($default)
                ? (is_array($value) ? array_values(array_filter($value, 'is_string')) : [])
                : (is_string($value) ? $value : $default);
        }

        return $contact;
    }

    /**
     * A "mailto:" link with the address encoded, so stray ? & # characters can not add mail parameters.
     */
    public static function mailtoHref(string $email): string
    {
        return 'mailto:'.str_replace('%40', '@', rawurlencode($email));
    }

    /**
     * The Contact page cards that have at least one line. Each line has its text and an optional link.
     *
     * @return list<array{icon: string, title: string, lines: list<array{text: string, href: ?string}>}>
     */
    public static function cards(): array
    {
        $contact = self::all();

        $cards = [
            [
                'icon' => 'fa-location-dot',
                'title' => $contact['address_title'],
                'lines' => array_map(fn (string $line) => ['text' => $line, 'href' => null], $contact['address_lines']),
            ],
            [
                'icon' => 'fa-envelope',
                'title' => $contact['email_title'],
                'lines' => array_map(fn (string $email) => ['text' => $email, 'href' => self::mailtoHref($email)], $contact['emails']),
            ],
            [
                'icon' => 'fa-phone',
                'title' => $contact['phone_title'],
                'lines' => array_map(fn (string $phone) => ['text' => $phone, 'href' => self::telHref($phone)], $contact['phones']),
            ],
        ];

        return array_values(array_filter($cards, fn (array $card) => $card['lines'] !== []));
    }

    /**
     * A "tel:" link with only digits and a leading "+", or null when the number has no digits.
     */
    public static function telHref(string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', $phone);

        if ($digits === '') {
            return null;
        }

        return 'tel:'.(str_starts_with(ltrim($phone), '+') ? '+' : '').$digits;
    }

    /**
     * The main contact lines for the compact places (banner, menu panel, footer): the address on one
     * line and the first email and phone with their links. Each is null when not set.
     *
     * @return array{address: ?string, email: ?string, email_href: ?string, phone: ?string, phone_href: ?string}
     */
    public static function primary(): array
    {
        $contact = self::all();
        $email = $contact['emails'][0] ?? null;
        $phone = $contact['phones'][0] ?? null;

        return [
            'address' => $contact['address_lines'] !== [] ? implode(', ', $contact['address_lines']) : null,
            'email' => $email,
            'email_href' => $email !== null ? self::mailtoHref($email) : null,
            'phone' => $phone,
            'phone_href' => $phone !== null ? self::telHref($phone) : null,
        ];
    }

    /**
     * The first saved email, offered while the contact form is not working yet.
     */
    public static function primaryEmail(): ?string
    {
        return self::all()['emails'][0] ?? null;
    }
}
