@php
    $live = $live ?? false;
    $hasUploadPreview = $live
        && filled($categoryImage ?? null)
        && is_object($categoryImage)
        && method_exists($categoryImage, 'temporaryUrl');
    $hasExistingPreview = $live && ! $hasUploadPreview && filled($existingCategoryImageUrl ?? null);
    $hasPreview = $hasUploadPreview || $hasExistingPreview;
    $previewUrl = $hasUploadPreview
        ? $categoryImage->temporaryUrl()
        : ($existingCategoryImageUrl ?? null);
@endphp

<label @class([
    'agricart-staff-form-preview__file',
    'agricart-staff-form-preview__file--compact',
    'agricart-category-form__upload-box',
    'agricart-category-form__upload-box--has-preview' => $hasPreview,
])>
    <input
        type="file"
        accept="image/*"
        @unless($live) x-on:change="onImageSelect($event)" @endunless
        @if($live) wire:model="categoryImage" @endif
    >

    @if ($hasPreview)
        <img
            @if($live ?? false) wire:key="category-image-preview-{{ md5((string) $previewUrl) }}" @endif
            src="{{ $previewUrl }}"
            alt="Category image preview"
            class="agricart-staff-form-preview__file-image agricart-category-form__upload-image"
        >
    @elseif (! $live)
        <img
            x-show="imagePreview"
            x-bind:src="imagePreview"
            alt="Category image preview"
            class="agricart-staff-form-preview__file-image agricart-category-form__upload-image"
        >
        <span class="agricart-staff-form-preview__file-text" x-show="! imagePreview">
            Drag &amp; Drop your files or <strong>Browse</strong>
        </span>
    @else
        <span class="agricart-staff-form-preview__file-text">
            Drag &amp; Drop your files or <strong>Browse</strong>
        </span>
    @endif
</label>
