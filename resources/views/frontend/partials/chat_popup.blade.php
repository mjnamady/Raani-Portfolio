{{--
    Floating "chat with me" popup (CON-2): an email form, not live chat. Saved to the admin inbox as a
    "chat" message; it opens again after a plain (no-JavaScript) send so its answer is visible.
--}}
@php
    $chatBag = $errors->getBag('message_chat');
    $chatStatus = session('message_status_chat');
    $chatOld = fn (string $field) => $chatBag->any() && is_string(old($field)) ? old($field) : '';
@endphp
<!-- ready chatting option via email -->
<div class="ready-chatting-option tmp-ready-chat">
    <input type="checkbox" id="click" aria-label="Open the chat form" @checked($chatBag->any() || $chatStatus)>
    <label for="click">
        <i class="fab fa-facebook-messenger" aria-hidden="true"></i>
        <i class="fas fa-times" aria-hidden="true"></i>
    </label>
    <div class="wrapper">
        <div class="head-text">
            Let's chat with me?
        </div>
        <div class="chat-box">
            <div class="desc-text">
                Leave your name, email and message and I will reply by email.
            </div>
            <div class="message-form-status {{ $chatStatus ? 'success' : ($chatBag->any() ? 'error' : '') }}" role="status" aria-live="polite" data-form-status>{{ $chatStatus ?? ($chatBag->any() ? 'Please check the highlighted fields.' : '') }}</div>
            <form class="tmp-dynamic-form" id="chat-form" method="POST" action="{{ route('messages.store') }}" data-message-form novalidate>
                @csrf
                <input type="hidden" name="source" value="{{ \App\Models\Message::SOURCE_CHAT }}">
                {{-- Honeypot (CON-4); a name and label that browsers and password managers do not autofill. --}}
                <div class="form-hp" aria-hidden="true">
                    <label for="chat-hp">Leave this field empty</label>
                    <input type="text" id="chat-hp" name="{{ \App\Http\Requests\StoreMessageRequest::HONEYPOT }}" value="" tabindex="-1" autocomplete="off" data-lpignore="true" data-1p-ignore="true" data-bwignore="true" data-form-type="other">
                </div>
                <div class="field">
                    <input class="input-field @if ($chatBag->has('name')) is-invalid @endif" name="name" placeholder="Your Name" type="text" required maxlength="80" autocomplete="name" aria-label="Your name" value="{{ $chatOld('name') }}">
                </div>
                <span class="field-error" data-error-for="name">{{ $chatBag->first('name') }}</span>
                <div class="field">
                    <input class="input-field @if ($chatBag->has('email')) is-invalid @endif" name="email" placeholder="Your Email" type="email" required maxlength="150" autocomplete="email" aria-label="Your email" value="{{ $chatOld('email') }}">
                </div>
                <span class="field-error" data-error-for="email">{{ $chatBag->first('email') }}</span>
                <div class="field textarea">
                    <textarea class="input-field @if ($chatBag->has('body')) is-invalid @endif" placeholder="Your Message" name="body" required maxlength="{{ \App\Models\Message::MAX_BODY_LENGTH }}" aria-label="Your message">{{ $chatOld('body') }}</textarea>
                </div>
                <span class="field-error" data-error-for="body">{{ $chatBag->first('body') }}</span>
                <div class="field">
                    <button type="submit">Send Message</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- ready chatting option via email end -->
