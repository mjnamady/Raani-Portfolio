@php
    $errorBags = collect($errors->getBags());
    $hasErrors = $errorBags->contains(fn ($bag) => $bag->isNotEmpty());

    $toasts = array_filter([
        session('status') ? ['type' => 'success', 'title' => 'Saved', 'message' => session('status')] : null,
        session('error') ? ['type' => 'danger', 'title' => 'Something went wrong', 'message' => session('error')] : null,
        $hasErrors ? [
            'type' => 'danger',
            'title' => 'Not saved',
            'message' => $errors->first('services') ?: 'Please correct the highlighted fields and save again.',
        ] : null,
    ]);
@endphp

{{-- Flash messages as toasts; admin-toasts.js shows and dismisses them. --}}
<div class="admin-toast-container" id="adminToastContainer">
    @foreach ($toasts as $toast)
        <div class="toast admin-toast admin-toast-{{ $toast['type'] }}"
             role="{{ $toast['type'] === 'danger' ? 'alert' : 'status' }}"
             aria-live="{{ $toast['type'] === 'danger' ? 'assertive' : 'polite' }}"
             aria-atomic="true">
            <div class="admin-toast-icon" aria-hidden="true">
                @if ($toast['type'] === 'success')
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                @else
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line><circle cx="12" cy="12" r="10"></circle></svg>
                @endif
            </div>
            <div class="admin-toast-body">
                <p class="admin-toast-title">{{ $toast['title'] }}</p>
                <p class="admin-toast-message">{{ $toast['message'] }}</p>
            </div>
            <button type="button" class="btn-close admin-toast-close" aria-label="Close"></button>
            <div class="admin-toast-progress" aria-hidden="true"></div>
        </div>
    @endforeach
</div>
