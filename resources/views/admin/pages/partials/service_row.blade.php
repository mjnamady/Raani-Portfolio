{{--
    One editable service card tile.
    $key  row key used in the input names (existing row index, or "__KEY__" in the JS template)
    $row  array with id, title, icon, project_count, project_count_label, is_active
--}}
@php
    $isTemplate = $key === '__KEY__';
    $field = fn (string $name) => "services[{$key}][{$name}]";
    $error = fn (string $name) => $isTemplate ? null : $errors->first("services.{$key}.{$name}");
    $isActive = filter_var($row['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN);
    $hasError = ! $isTemplate && collect(['title', 'icon', 'project_count', 'project_count_label'])->contains(fn ($name) => $error($name));
@endphp

<div class="sv-tile-col" data-service-row data-existing="{{ filled($row['id'] ?? null) ? '1' : '0' }}">
    <div class="sv-tile {{ $isActive ? '' : 'is-hidden-on-site' }} {{ $hasError ? 'has-error' : '' }}">
        @if (filled($row['id'] ?? null))
            <input type="hidden" name="{{ $field('id') }}" value="{{ $row['id'] }}">
        @endif

        <!-- Toolbar -->
        <div class="sv-tile-toolbar">
            <span class="sv-tile-position">#<span data-row-number>1</span></span>
            <div class="sv-tile-actions">
                <button type="button" class="sv-icon-btn" data-move="up" aria-label="Move card left" title="Move left">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 6l-6 6l6 6"/></svg>
                </button>
                <button type="button" class="sv-icon-btn" data-move="down" aria-label="Move card right" title="Move right">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6l-6 6"/></svg>
                </button>
                <label class="sv-icon-btn sv-visibility" title="Show on site">
                    <input type="hidden" name="{{ $field('is_active') }}" value="0">
                    <input class="visually-hidden" type="checkbox" id="service_{{ $key }}_active" name="{{ $field('is_active') }}" value="1" @checked($isActive) data-preview="active">
                    <svg class="sv-eye-on" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"/><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6"/></svg>
                    <svg class="sv-eye-off" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.585 10.587a2 2 0 0 0 2.829 2.828"/><path d="M16.681 16.673a8.717 8.717 0 0 1 -4.681 1.327c-3.6 0 -6.6 -2 -9 -6c1.272 -2.12 2.712 -3.678 4.32 -4.674m2.86 -1.146a9.055 9.055 0 0 1 1.82 -.18c3.6 0 6.6 2 9 6c-.666 1.11 -1.379 2.067 -2.138 2.87"/><path d="M3 3l18 18"/></svg>
                    <span class="visually-hidden">Show on site</span>
                </label>
                <button type="button" class="sv-icon-btn sv-remove" data-remove aria-label="Remove card" title="Remove">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 7l16 0"/><path d="M10 11l0 6"/><path d="M14 11l0 6"/><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12"/><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3"/></svg>
                </button>
            </div>
        </div>

        <!-- The card as visitors see it -->
        <div class="sv-card-face" aria-hidden="true">
            <span class="sv-card-icon"><i class="{{ $row['icon'] ?? 'fa-light fa-pen-ruler' }}" data-card-icon></i></span>
            <div class="sv-card-title" data-card-title>{{ $row['title'] ?? '' }}</div>
            <div class="sv-card-count" data-card-count>{{ $row['project_count'] ?? 0 }} {{ $row['project_count_label'] ?? 'Projects' }}</div>
            <span class="sv-card-hidden">Hidden on the site</span>
        </div>

        <!-- Fields -->
        <div class="sv-tile-fields">
            <div class="mb-2">
                <label for="service_{{ $key }}_title">Title</label>
                <input type="text" class="form-control form-control-sm {{ $error('title') ? 'is-invalid' : '' }}" id="service_{{ $key }}_title" name="{{ $field('title') }}" value="{{ $row['title'] ?? '' }}" maxlength="80" required data-preview="title">
                @if ($error('title'))
                    <div class="invalid-feedback">{{ $error('title') }}</div>
                @endif
            </div>

            <div class="mb-2">
                <label for="service_{{ $key }}_icon">Icon</label>
                <div class="input-group input-group-sm icon-input-group has-validation">
                    <span class="input-group-text" aria-hidden="true"><i class="{{ $row['icon'] ?? 'fa-light fa-pen-ruler' }}" data-icon-preview></i></span>
                    <input type="text" class="form-control {{ $error('icon') ? 'is-invalid' : '' }}" id="service_{{ $key }}_icon" name="{{ $field('icon') }}" value="{{ $row['icon'] ?? 'fa-light fa-pen-ruler' }}" maxlength="60" required list="icon-suggestions" autocomplete="off" spellcheck="false" data-preview="icon">
                    @if ($error('icon'))
                        <div class="invalid-feedback">{{ $error('icon') }}</div>
                    @endif
                </div>
            </div>

            <div class="row g-2">
                <div class="col-5">
                    <label for="service_{{ $key }}_count">Count</label>
                    <input type="number" class="form-control form-control-sm {{ $error('project_count') ? 'is-invalid' : '' }}" id="service_{{ $key }}_count" name="{{ $field('project_count') }}" value="{{ $row['project_count'] ?? 0 }}" min="0" max="9999999" step="1" required data-preview="count">
                    @if ($error('project_count'))
                        <div class="invalid-feedback">{{ $error('project_count') }}</div>
                    @endif
                </div>
                <div class="col-7">
                    <label for="service_{{ $key }}_label">Count label</label>
                    <input type="text" class="form-control form-control-sm {{ $error('project_count_label') ? 'is-invalid' : '' }}" id="service_{{ $key }}_label" name="{{ $field('project_count_label') }}" value="{{ $row['project_count_label'] ?? 'Projects' }}" maxlength="30" required data-preview="label">
                    @if ($error('project_count_label'))
                        <div class="invalid-feedback">{{ $error('project_count_label') }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
