{{--
    Sticky save bar for a form tracked by admin-form-tracker.js (add data-track-changes to the form).
    $label       text of the submit button
    $discardUrl  page reloaded by "Discard" (defaults to the current path; relative, so it never echoes the Host header)
--}}
<div class="form-save-bar" data-save-bar>
    <span class="form-save-state" aria-live="polite">
        <span class="form-save-dot" aria-hidden="true"></span>
        <span data-save-text>All changes saved</span>
    </span>
    <div class="d-flex gap-2">
        <a href="{{ $discardUrl ?? request()->getBaseUrl().'/'.ltrim(request()->path(), '/') }}" class="btn btn-light-dark btn-sm d-none" data-save-discard>Discard</a>
        <button type="submit" class="btn btn-primary">{{ $label }}</button>
    </div>
</div>
