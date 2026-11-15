<?php

namespace App\Http\Requests\Admin;

use App\Models\ResumeEntry;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Create or update an education or experience entry; the type comes from the route.
 */
class ResumeEntryRequest extends FormRequest
{
    /**
     * The entry type handled by the current route (admin.education.* or admin.experience.*).
     */
    public function entryType(): string
    {
        return $this->routeIs('admin.education.*') ? ResumeEntry::TYPE_EDUCATION : ResumeEntry::TYPE_EXPERIENCE;
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $entry = $this->route('entry');

        if (! $entry instanceof ResumeEntry) {
            return (bool) $this->user()?->can('create', ResumeEntry::class);
        }

        // An experience entry is not reachable through education URLs, and the other way round.
        abort_unless($entry->type === $this->entryType(), 404);

        return (bool) $this->user()?->can('update', $entry);
    }

    /**
     * Normalise the checkbox.
     */
    protected function prepareForValidation(): void
    {
        $this->merge(['is_active' => $this->boolean('is_active')]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $shared = [
            'description' => ['required', 'string', 'max:400'],
            'is_active' => ['boolean'],
        ];

        if ($this->entryType() === ResumeEntry::TYPE_EDUCATION) {
            return $shared + [
                'title' => ['required', 'string', 'max:80'],
                'organisation' => ['nullable', 'string', 'max:120'],
                'period' => ['required', 'string', 'max:40'],
            ];
        }

        return $shared + [
            'organisation' => ['required', 'string', 'max:120'],
            'role' => ['required', 'string', 'max:80'],
            'period' => ['nullable', 'string', 'max:40'],
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return $this->entryType() === ResumeEntry::TYPE_EDUCATION
            ? ['title' => 'title', 'organisation' => 'institution', 'period' => 'years']
            : ['organisation' => 'name', 'role' => 'role', 'period' => 'label'];
    }
}
