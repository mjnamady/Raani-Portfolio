{{--
    The social icons set in Site Settings (GEN-3); nothing is shown when none are set.
    $socialLinks  from SiteSettings::socialLinks() (https links only)
    $class        extra class on the wrapper, e.g. "footer"
--}}
@if ($socialLinks !== [])
    <div class="social-link {{ $class ?? '' }}">
        @foreach ($socialLinks as $link)
            <a href="{{ $link['url'] }}" target="_blank" rel="noopener" aria-label="{{ $link['label'] }}"><i class="fa-brands {{ $link['icon'] }}" aria-hidden="true"></i></a>
        @endforeach
    </div>
@endif
