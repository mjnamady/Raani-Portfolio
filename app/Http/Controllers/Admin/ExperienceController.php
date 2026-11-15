<?php

namespace App\Http\Controllers\Admin;

use App\Models\ResumeEntry;
use App\Models\Section;

class ExperienceController extends ResumeEntryController
{
    protected function config(): array
    {
        return [
            'type' => ResumeEntry::TYPE_EXPERIENCE,
            'route' => 'admin.experience',
            'section' => Section::HOME_EXPERIENCE,
            'label' => 'Experience',
            'singular' => 'experience entry',
            'view' => 'admin.resume.experience_index',
            'default_title' => 'Experiences',
        ];
    }
}
