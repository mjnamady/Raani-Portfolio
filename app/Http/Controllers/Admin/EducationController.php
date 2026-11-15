<?php

namespace App\Http\Controllers\Admin;

use App\Models\ResumeEntry;
use App\Models\Section;

class EducationController extends ResumeEntryController
{
    protected function config(): array
    {
        return [
            'type' => ResumeEntry::TYPE_EDUCATION,
            'route' => 'admin.education',
            'section' => Section::HOME_EDUCATION,
            'label' => 'Education',
            'singular' => 'education entry',
            'view' => 'admin.resume.education_index',
            'default_title' => 'Education',
        ];
    }
}
