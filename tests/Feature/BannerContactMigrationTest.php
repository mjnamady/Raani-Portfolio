<?php

use App\Models\BannerSection;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The migration that moves the banner's contact columns into the contact settings.
 */
function bannerContactMigration(): object
{
    return require database_path('migrations/2026_11_05_101310_drop_contact_columns_from_banner_sections_table.php');
}

/**
 * Roll the migration back and put a banner row with the given contact columns in place.
 */
function bannerBeforeMigration(array $contact): void
{
    bannerContactMigration()->down();
    DB::table('banner_sections')->insert(collect(BannerSection::defaults())->except('is_active')->all() + $contact + ['is_active' => true, 'image_alt' => 'x']);
}

test('valid banner contact details are copied to the settings and the columns are dropped', function () {
    bannerBeforeMigration(['address' => ' 12 Analytical Street ', 'email' => 'ada@example.test', 'phone' => '+44 20 7946 0000']);
    Cache::put(Setting::CACHE_KEY, ['stale' => true]);

    bannerContactMigration()->up();

    expect(Schema::hasColumns('banner_sections', ['address', 'email', 'phone']))->toBeFalse()
        ->and(Cache::has(Setting::CACHE_KEY))->toBeFalse()
        ->and(Setting::get('contact.address_lines'))->toBe(['12 Analytical Street'])
        ->and(Setting::get('contact.emails'))->toBe(['ada@example.test'])
        ->and(Setting::get('contact.phones'))->toBe(['+44 20 7946 0000']);
});

test('values the Contact page would refuse, or that are too long, are skipped rather than cut', function () {
    bannerBeforeMigration(['address' => str_repeat('a', 150), 'email' => 'hi?subject=x@example.com', 'phone' => '+()']);

    bannerContactMigration()->up();

    expect(Setting::query()->where('group', 'contact')->count())->toBe(0);
});

test('existing contact settings are never overwritten', function () {
    bannerBeforeMigration(['email' => 'banner@example.test']);
    Setting::put('contact.emails', ['mine@example.test']);

    bannerContactMigration()->up();

    expect(Setting::get('contact.emails'))->toBe(['mine@example.test']);
});

test('rolling back restores the first line of each contact setting to the banner', function () {
    Setting::put('contact.emails', ['ada@example.test', 'other@example.test']);
    Setting::put('contact.phones', ['+44 20 7946 0000']);
    DB::table('banner_sections')->insert(collect(BannerSection::defaults())->except('is_active')->all() + ['is_active' => true]);

    bannerContactMigration()->down();

    expect(DB::table('banner_sections')->first())
        ->email->toBe('ada@example.test')
        ->phone->toBe('+44 20 7946 0000')
        ->address->toBeNull();
});
