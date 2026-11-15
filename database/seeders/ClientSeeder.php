<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Section;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    /**
     * Recreate the theme's eight demo logos (served from the theme folder). Existing logos are never overwritten.
     */
    public function run(): void
    {
        Section::query()->firstOrCreate(['key' => Section::HOME_CLIENTS], ['is_active' => true]);

        if (! Client::query()->exists()) {
            foreach (range(1, 8) as $number) {
                $client = new Client([
                    'name' => "Partner company {$number}",
                    'url' => null,
                    'is_active' => true,
                ]);
                $client->logo = Client::THEME_LOGO_DIRECTORY."company-logo-{$number}.svg";
                $client->sort_order = $number;
                $client->save();
            }
        }

        // DatabaseSeeder disables model events, so clear the home page caches explicitly.
        Section::forgetCache();
        Client::forgetCache();
    }
}
