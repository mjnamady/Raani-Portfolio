{{--
    The contact form (CON-1, CON-3, CON-4), used by the "Get in touch" section and project pages.
    Posts to the inbox; custom.js sends it in the background and shows the answer inline, and it
    works the same without JavaScript (errors and the thank-you come back on the page).
    $source   "contact" (default) or "project"
    $project  the project a project page's form is about (optional)
--}}
@php
    $source = $source ?? \App\Models\Message::SOURCE_CONTACT;
    $bag = $errors->getBag('message_'.$source);
    $status = session('message_status_'.$source);
    // Old input is only reused when it is text (a crafted array must not break the page).
    $old = fn (string $field, string $default = '') => $bag->any() && is_string(old($field)) ? old($field) : $default;
@endphp
<div id="form-messages" class="message-form-status {{ $status ? 'success' : ($bag->any() ? 'error' : '') }}" role="status" aria-live="polite">{{ $status ?? ($bag->any() ? 'Please check the highlighted fields.' : '') }}</div>
<form class="tmp-dynamic-form" id="contact-form" method="POST" action="{{ route('messages.store') }}" data-message-form novalidate>
    @csrf
    <input type="hidden" name="source" value="{{ $source }}">
    @isset($project)
        <input type="hidden" name="project_id" value="{{ $project->id }}">
    @endisset
    {{-- Honeypot (CON-4): hidden from people and screen readers; bots tend to fill it in. Its name and
         label are ones browsers and password managers do not autofill, and they are told to skip it. --}}
    <div class="form-hp" aria-hidden="true">
        <label for="contact-hp">Leave this field empty</label>
        <input type="text" id="contact-hp" name="{{ \App\Http\Requests\StoreMessageRequest::HONEYPOT }}" value="" tabindex="-1" autocomplete="off" data-lpignore="true" data-1p-ignore="true" data-bwignore="true" data-form-type="other">
    </div>
    <div class="contact-form-wrapper row">
        <div class="col-lg-6">
            <div class="form-group">
                <input class="input-field @if ($bag->has('name')) is-invalid @endif" name="name" id="contact-name" placeholder="Your Name" type="text" required maxlength="80" autocomplete="name" aria-label="Your name" value="{{ $old('name') }}">
                <span class="field-error" data-error-for="name">{{ $bag->first('name') }}</span>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="form-group">
                <input class="input-field @if ($bag->has('phone')) is-invalid @endif" name="phone" id="contact-phone" placeholder="Phone Number (optional)" type="tel" maxlength="40" autocomplete="tel" aria-label="Phone number (optional)" value="{{ $old('phone') }}">
                <span class="field-error" data-error-for="phone">{{ $bag->first('phone') }}</span>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="form-group">
                <input class="input-field @if ($bag->has('email')) is-invalid @endif" id="contact-email" name="email" placeholder="Your Email" type="email" required maxlength="150" autocomplete="email" aria-label="Your email" value="{{ $old('email') }}">
                <span class="field-error" data-error-for="email">{{ $bag->first('email') }}</span>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="form-group">
                <input class="input-field @if ($bag->has('subject')) is-invalid @endif" type="text" id="subject" name="subject" placeholder="Subject (optional)" maxlength="150" aria-label="Subject (optional)" value="{{ $old('subject', isset($project) ? 'About: '.$project->title : '') }}">
                <span class="field-error" data-error-for="subject">{{ $bag->first('subject') }}</span>
            </div>
        </div>

        <div class="col-lg-12">
            <div class="form-group">
                <textarea class="input-field @if ($bag->has('body')) is-invalid @endif" placeholder="Your Message" name="body" id="contact-message" required maxlength="{{ \App\Models\Message::MAX_BODY_LENGTH }}" aria-label="Your message">{{ $old('body') }}</textarea>
                <span class="field-error" data-error-for="body">{{ $bag->first('body') }}</span>
            </div>
        </div>

        <div class="col-lg-12">
            <div class="tmp-button-here">
                <button class="tmp-btn hover-icon-reverse radius-round w-100" type="submit" id="submit">
                    <span class="icon-reverse-wrapper">
                        <span class="btn-text">Send Message</span>
                        <span class="btn-icon"><i class="fa-sharp fa-regular fa-arrow-right"></i></span>
                        <span class="btn-icon"><i class="fa-sharp fa-regular fa-arrow-right"></i></span>
                    </span>
                </button>
            </div>
        </div>
    </div>
</form>
