<?php

namespace Database\Seeders;

use App\Models\ResumeEntry;
use App\Models\Section;
use Illuminate\Database\Seeder;

class ResumeEntrySeeder extends Seeder
{
    /**
     * Recreate the theme's Education and Experience content. Existing content is never overwritten.
     */
    public function run(): void
    {
        Section::query()->firstOrCreate(['key' => Section::HOME_EDUCATION], [
            'title' => 'Education',
            'is_active' => true,
        ]);

        Section::query()->firstOrCreate(['key' => Section::HOME_EXPERIENCE], [
            'title' => 'Experiences',
            'image_alt' => 'Working at the desk',
            'is_active' => true,
        ]);

        if (! ResumeEntry::query()->education()->exists()) {
            $this->createEntries(ResumeEntry::TYPE_EDUCATION, [
                ['title' => 'Trainer Marketing', 'period' => '2005-2009', 'description' => "A personal portfolio is a curated collection of an individual's professional work, showcasing their skills and experience."],
                ['title' => 'Assistant Director', 'period' => '2010-2014', 'description' => 'Each project here showcases my commitment to excellence and adaptability, tailored to meet each client’s unique needs.'],
                ['title' => 'Design Assistant', 'period' => '2008-2012', 'description' => 'I’ve had the privilege of working with various clients, from startups to established companies, helping bring their visions to life.'],
                ['title' => 'Design Assistant', 'period' => '2008-2012', 'description' => 'Each project here showcases my commitment to excellence and adaptability, tailored to meet each client’s unique needs.'],
            ]);
        }

        if (! ResumeEntry::query()->experience()->exists()) {
            $this->createEntries(ResumeEntry::TYPE_EXPERIENCE, [
                ['period' => 'Experience', 'organisation' => 'Fatima Asrafy', 'role' => 'UI/UX Designer', 'description' => 'Designing clear, friendly interfaces and turning research into products people enjoy using.'],
                ['period' => 'Experience', 'organisation' => 'Fatima Asrafy', 'role' => 'UI/UX Designer', 'description' => 'Interested in working together? Let’s bring your ideas to life! Contact me, and let’s start building something.'],
            ]);
        }

        // DatabaseSeeder disables model events, so clear the home page caches explicitly.
        Section::forgetCache();
        ResumeEntry::forgetCache();
    }

    /**
     * @param  list<array<string, string>>  $entries
     */
    protected function createEntries(string $type, array $entries): void
    {
        foreach ($entries as $order => $attributes) {
            $entry = new ResumeEntry($attributes + ['is_active' => true]);
            $entry->type = $type;
            $entry->sort_order = $order + 1;
            $entry->save();
        }
    }
}
