<?php

namespace App\Http\Requests\Admin;

/**
 * Edit a testimonial; the same rules as adding one.
 */
class UpdateTestimonialRequest extends StoreTestimonialRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('update', $this->route('testimonial'));
    }
}
