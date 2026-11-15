{{--
    One uploadable site image: current image (or the theme image), file input and a
    "use the theme image" switch.
    $image   key in SiteSettings::IMAGES (also the input name)
    $config  that entry plus "url" and "uploaded"
--}}
<div class="col-md-6 col-xxl-4">
    <div class="ss-image @error($image) has-error @enderror">
        <div class="ss-image-box {{ $image === 'favicon' ? 'is-small' : '' }}">
            <img src="{{ $config['url'] }}" alt="Current {{ strtolower($config['label']) }}" data-image-preview="{{ $image }}" data-original-src="{{ $config['url'] }}">
        </div>
        <label for="{{ $image }}">{{ $config['label'] }}</label>
        <input type="file" class="form-control form-control-sm @error($image) is-invalid @enderror" id="{{ $image }}" name="{{ $image }}" accept="image/png,image/webp,image/jpeg" data-image-input="{{ $image }}">
        <div class="form-text">
            PNG, WebP or JPG.
            {{ $config['mode'] === 'crop' ? "Cropped to {$config['width']}×{$config['height']}." : "Scaled to fit {$config['width']}×{$config['height']}." }}
        </div>
        @error($image) <div class="invalid-feedback">{{ $message }}</div> @enderror
        @if ($config['uploaded'])
            <div class="form-check mt-2">
                <input type="hidden" name="reset_{{ $image }}" value="0">
                <input class="form-check-input" type="checkbox" id="reset_{{ $image }}" name="reset_{{ $image }}" value="1">
                <label class="form-check-label" for="reset_{{ $image }}">Use the theme image again</label>
            </div>
        @endif
    </div>
</div>
