@php
    $hasUploadPreview = $live
        && filled($file)
        && is_object($file)
        && method_exists($file, 'temporaryUrl');
    $hasExistingPreview = $live && ! $hasUploadPreview && filled($existingUrl ?? null);
    $hasPreview = $hasUploadPreview || $hasExistingPreview;
    $previewUrl = $hasUploadPreview
        ? $file->temporaryUrl()
        : ($existingUrl ?? null);
@endphp

<label @class([
    'agricart-staff-form-preview__file',
    'agricart-staff-form-preview__file--compact',
    'agricart-staff-form-preview__file--has-preview' => $hasPreview,
    'agricart-staff-form-preview__file--invalid' => $live && $errors->has($errorKey),
])>
    <input type="file" accept="image/webp,.webp" @if($live) wire:model="{{ $wireModel }}" @endif>

    @if ($hasPreview)
        <img
            src="{{ $previewUrl }}"
            alt="{{ $alt }}"
            class="agricart-staff-form-preview__file-image"
        >
    @else
        <span class="agricart-staff-form-preview__file-text">Browse</span>
    @endif

    @if ($live)
        <span
            class="agricart-staff-form-preview__file-loading"
            wire:loading
            wire:target="{{ $wireModel }}"
        >
            Uploading...
        </span>
    @endif
</label>

@if ($live)
    @error($errorKey)
        <span class="agricart-staff-form-preview__error">{{ $message }}</span>
    @enderror
@endif
