{{--
    Show/hide, move, edit and delete buttons for one education or experience entry.
    $entry   the ResumeEntry
    $config  the controller config (route prefix, singular label)
    $first   whether this is the first entry (no "move up")
    $last    whether this is the last entry (no "move down")
--}}
@php
    $name = $entry->title ?: $entry->organisation;
@endphp

<div class="rs-actions">
    <form method="POST" action="{{ route($config['route'].'.toggle', $entry) }}">
        @csrf
        @method('PATCH')
        <button type="submit" class="rs-action {{ $entry->is_active ? '' : 'is-off' }}" title="{{ $entry->is_active ? 'Hide from the site' : 'Show on the site' }}" aria-label="{{ $entry->is_active ? 'Hide' : 'Show' }} {{ $name }}">
            @if ($entry->is_active)
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"/><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6"/></svg>
            @else
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.585 10.587a2 2 0 0 0 2.829 2.828"/><path d="M16.681 16.673a8.717 8.717 0 0 1 -4.681 1.327c-3.6 0 -6.6 -2 -9 -6c1.272 -2.12 2.712 -3.678 4.32 -4.674m2.86 -1.146a9.055 9.055 0 0 1 1.82 -.18c3.6 0 6.6 2 9 6c-.666 1.11 -1.379 2.067 -2.138 2.87"/><path d="M3 3l18 18"/></svg>
            @endif
        </button>
    </form>

    <form method="POST" action="{{ route($config['route'].'.move', $entry) }}">
        @csrf
        @method('PATCH')
        <input type="hidden" name="direction" value="up">
        <button type="submit" class="rs-action" title="Move up" aria-label="Move {{ $name }} up" @disabled($first)>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 15l6 -6l6 6"/></svg>
        </button>
    </form>

    <form method="POST" action="{{ route($config['route'].'.move', $entry) }}">
        @csrf
        @method('PATCH')
        <input type="hidden" name="direction" value="down">
        <button type="submit" class="rs-action" title="Move down" aria-label="Move {{ $name }} down" @disabled($last)>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9l6 6l6 -6"/></svg>
        </button>
    </form>

    <a href="{{ route($config['route'].'.edit', $entry) }}" class="rs-action" title="Edit" aria-label="Edit {{ $name }}">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 20h4l10.5 -10.5a2.828 2.828 0 1 0 -4 -4l-10.5 10.5v4"/><path d="M13.5 6.5l4 4"/></svg>
    </a>

    <form method="POST" action="{{ route($config['route'].'.destroy', $entry) }}" data-confirm="Delete &quot;{{ $name }}&quot;? This cannot be undone.">
        @csrf
        @method('DELETE')
        <button type="submit" class="rs-action rs-delete" title="Delete" aria-label="Delete {{ $name }}">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 7l16 0"/><path d="M10 11l0 6"/><path d="M14 11l0 6"/><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12"/><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3"/></svg>
        </button>
    </form>
</div>
