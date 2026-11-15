<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

/**
 * The banner's contact block now reads the site-wide contact details (GEN-2), so its own
 * address, email and phone columns go. Values are copied to the contact settings first,
 * unless those are already set, so nothing the owner typed is lost.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $banner = DB::table('banner_sections')->first(['address', 'email', 'phone']);

        // The Contact page's own rules: a value that fails them (or is too long) is skipped, not trimmed.
        $rules = [
            'address_lines' => ['string', 'max:120'],
            'emails' => ['string', 'email', 'not_regex:/[?&#%]/', 'max:150'],
            'phones' => ['string', 'max:40', 'regex:/^[0-9+().\-\s]+$/', 'regex:/\d/'],
        ];

        if ($banner) {
            foreach (['address_lines' => $banner->address, 'emails' => $banner->email, 'phones' => $banner->phone] as $key => $value) {
                $value = is_string($value) ? trim($value) : null;
                $valid = $value !== null && $value !== '' && mb_check_encoding($value, 'UTF-8')
                    && Validator::make(['value' => $value], ['value' => $rules[$key]])->passes();

                if ($valid && ! DB::table('settings')->where('key', "contact.{$key}")->exists()) {
                    DB::table('settings')->insert([
                        'key' => "contact.{$key}",
                        'value' => json_encode([$value]),
                        'group' => 'contact',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        Schema::table('banner_sections', function (Blueprint $table) {
            $table->dropColumn(['address', 'email', 'phone']);
        });

        // Rows were written without model events, so clear the cached settings and banner here.
        Cache::forget('settings.v1');
        Cache::forget('home.banner.v2');
    }

    /**
     * Reverse the migrations: the columns come back holding the first line of each contact setting.
     */
    public function down(): void
    {
        Schema::table('banner_sections', function (Blueprint $table) {
            $table->string('address', 160)->nullable()->after('contact_heading');
            $table->string('email', 150)->nullable()->after('address');
            $table->string('phone', 40)->nullable()->after('email');
        });

        $first = function (string $key, int $max): ?string {
            $lines = json_decode((string) DB::table('settings')->where('key', "contact.{$key}")->value('value'), true);
            $line = is_array($lines) ? ($lines[0] ?? null) : null;

            return is_string($line) && mb_strlen($line) <= $max ? $line : null;
        };

        DB::table('banner_sections')->update([
            'address' => $first('address_lines', 160),
            'email' => $first('emails', 150),
            'phone' => $first('phones', 40),
        ]);

        Cache::forget('home.banner.v2');
    }
};
