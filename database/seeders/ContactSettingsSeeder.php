<?php

namespace Database\Seeders;

use App\Models\Section;
use App\Models\Setting;
use App\Support\ContactDetails;
use Illuminate\Database\Seeder;

class ContactSettingsSeeder extends Seeder
{
    /**
     * Demo contact details (the ones the banner showed before it switched to the contact settings).
     *
     * @var array{address_lines: list<string>, emails: list<string>, phones: list<string>}
     */
    public const DEMO = [
        'address_lines' => ['1179 KFC Road, Lisbon, Portugal'],
        'emails' => ['info@example.com'],
        'phones' => ['(629) 555-0129'],
    ];

    /**
     * The contact details and the shared "Get in touch" text. Existing values are never overwritten.
     */
    public function run(): void
    {
        foreach (array_merge(ContactDetails::DEFAULTS, self::DEMO) as $key => $value) {
            if (! Setting::query()->where('key', "contact.{$key}")->exists()) {
                Setting::put("contact.{$key}", $value);
            }
        }

        Section::query()->firstOrCreate(['key' => Section::CONTACT_GET_IN_TOUCH], ContactDetails::GET_IN_TOUCH_DEFAULTS + ['is_active' => true]);

        // DatabaseSeeder disables model events, so clear the caches explicitly.
        Setting::forgetCache();
        Section::forgetCache();
    }
}
