{{--
    Gallery manager on the project edit page (PRJ-5). Each action is its own small form,
    so it lives outside the main project form.
    $project  the Project with its images loaded
--}}
@php
    $maxKilobytes = \App\Http\Requests\Admin\StoreProjectRequest::MAX_IMAGE_KILOBYTES;
    $room = \App\Http\Requests\Admin\StoreProjectImagesRequest::MAX_PER_PROJECT - $project->images->count();
@endphp

<section class="pj-gallery" id="gallery" aria-labelledby="gallery-heading">
    <div class="pj-gallery-head">
        <div>
            <h5 id="gallery-heading">Gallery</h5>
            <p>The image slider on the project page, in this order · {{ $project->images->count() }} / {{ \App\Http\Requests\Admin\StoreProjectImagesRequest::MAX_PER_PROJECT }} images.</p>
        </div>

        @if ($room > 0)
            <form method="POST" action="{{ route('admin.projects.images.store', $project) }}" enctype="multipart/form-data" class="pj-gallery-upload">
                @csrf
                <input type="file" class="visually-hidden" id="gallery-images" name="images[]" accept="image/jpeg,image/png,image/webp" multiple
                       data-max-bytes="{{ \App\Support\UploadLimit::bytes($maxKilobytes) }}"
                       data-max-label="{{ \App\Support\UploadLimit::label($maxKilobytes) }}"
                       data-max-files="{{ min(\App\Http\Requests\Admin\StoreProjectImagesRequest::MAX_PER_UPLOAD, $room) }}">
                <label for="gallery-images" class="btn btn-light-primary btn-sm mb-0">Choose images…</label>
                <span class="pj-gallery-picked" id="gallery-picked">Up to {{ min(\App\Http\Requests\Admin\StoreProjectImagesRequest::MAX_PER_UPLOAD, $room) }} at a time · cropped to {{ \App\Models\ProjectImage::WIDTH }}&times;{{ \App\Models\ProjectImage::HEIGHT }} · max {{ \App\Support\UploadLimit::label($maxKilobytes) }} each</span>
                <button type="submit" class="btn btn-primary btn-sm" id="gallery-upload-btn" disabled>Upload</button>
            </form>
        @endif
    </div>

    @if ($errors->has('images') || $errors->has('images.*'))
        <div class="alert alert-light-danger">
            {{ $errors->first('images') ?: $errors->first('images.*') }}
        </div>
    @endif

    @if ($project->images->isEmpty())
        <div class="pj-gallery-empty">No gallery images yet. The slider is hidden on the project page until you add some.</div>
    @else
        <div class="pj-gallery-grid">
            @foreach ($project->images as $image)
                <figure class="pj-gallery-item">
                    <img src="{{ $image->url }}" alt="{{ $image->alt }}" loading="lazy">
                    <figcaption>
                        <form method="POST" action="{{ route('admin.projects.images.update', [$project, $image]) }}" class="pj-gallery-alt">
                            @csrf
                            @method('PUT')
                            <label for="alt-{{ $image->id }}" class="visually-hidden">Description of image {{ $loop->iteration }}</label>
                            <input type="text" class="form-control form-control-sm" id="alt-{{ $image->id }}" name="alt" value="{{ $image->alt }}" maxlength="150" placeholder="Image description (alt text)">
                            <button type="submit" class="btn btn-light-dark btn-sm">Save</button>
                        </form>
                        <div class="pj-gallery-actions">
                            <form method="POST" action="{{ route('admin.projects.images.move', [$project, $image]) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="direction" value="left">
                                <button type="submit" class="pj-icon-btn" aria-label="Move image {{ $loop->iteration }} earlier" title="Move earlier" @disabled($loop->first)>←</button>
                            </form>
                            <span class="pj-gallery-index">{{ $loop->iteration }}</span>
                            <form method="POST" action="{{ route('admin.projects.images.move', [$project, $image]) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="direction" value="right">
                                <button type="submit" class="pj-icon-btn" aria-label="Move image {{ $loop->iteration }} later" title="Move later" @disabled($loop->last)>→</button>
                            </form>
                            <form method="POST" action="{{ route('admin.projects.images.destroy', [$project, $image]) }}" data-confirm="Remove this image from the gallery?" class="ms-auto">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="pj-icon-btn pj-delete" aria-label="Remove image {{ $loop->iteration }}" title="Remove">✕</button>
                            </form>
                        </div>
                    </figcaption>
                </figure>
            @endforeach
        </div>
    @endif
</section>
