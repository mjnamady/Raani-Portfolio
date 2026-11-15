<?php

namespace App\Http\Requests\Admin;

use App\Models\ResumeEntry;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Move an education or experience entry one place up or down within its own list.
 */
class MoveResumeEntryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $entry = $this->route('entry');
        $type = $this->routeIs('admin.education.*') ? ResumeEntry::TYPE_EDUCATION : ResumeEntry::TYPE_EXPERIENCE;

        abort_unless($entry instanceof ResumeEntry && $entry->type === $type, 404);

        return (bool) $this->user()?->can('update', $entry);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'direction' => ['required', 'in:up,down'],
        ];
    }
}
