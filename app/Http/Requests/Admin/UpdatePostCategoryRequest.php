<?php

namespace App\Http\Requests\Admin;

/**
 * Rename a blog category; the same rules as adding one (the slug check ignores this category).
 */
class UpdatePostCategoryRequest extends StorePostCategoryRequest
{
    /**
     * Rename errors go to their own bag, so they are not shown under the "Add a category" form
     * (the toast still reports them).
     *
     * @var string
     */
    protected $errorBag = 'updateCategory';

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('update', $this->route('category'));
    }
}
