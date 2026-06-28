@php
    use App\Modules\Catalog\Support\BrandLogoSpec;

    $live = $live ?? false;
    $allowedExtensionsJson = json_encode(BrandLogoSpec::allowedExtensions());
    $allowedMimeTypesJson = json_encode(BrandLogoSpec::allowedMimeTypes());
    $invalidTypeMessage = BrandLogoSpec::invalidTypeMessage();
    $hasUploadPreview = $live
        && filled($brandLogo ?? null)
        && is_object($brandLogo)
        && method_exists($brandLogo, 'temporaryUrl');
    $hasExistingPreview = $live && ! $hasUploadPreview && filled($existingBrandLogoUrl ?? null);
    $hasPreview = $hasUploadPreview || $hasExistingPreview;
    $previewUrl = $hasUploadPreview
        ? $brandLogo->temporaryUrl()
        : ($existingBrandLogoUrl ?? null);
@endphp

<label @class([
    'agricart-staff-form-preview__file',
    'agricart-staff-form-preview__file--compact',
    'agricart-category-form__upload-box',
    'agricart-brand-form__upload-box',
    'agricart-category-form__upload-box--has-preview' => $hasPreview,
])>
    <input
        type="file"
        accept="{{ BrandLogoSpec::acceptAttribute() }}"
        @if ($live)
            x-on:change="
                const file = $event.target.files?.[0];
                const allowed = {{ $allowedExtensionsJson }};
                const mimeAllowed = {{ $allowedMimeTypesJson }};
                const extension = file?.name.split('.').pop()?.toLowerCase() ?? '';
                const extensionOk = allowed.includes(extension);
                const mimeOk = ! file?.type || mimeAllowed.includes(file.type);
                if (! file || ! extensionOk || ! mimeOk) {
                    $event.target.value = '';
                    alert(@js($invalidTypeMessage));
                    $wire.set('brandLogo', null);
                }
            "
            wire:model="brandLogo"
        @else
            x-on:change="onLogoSelect($event)"
        @endif
    >

    @if ($hasPreview)
        <img
            @if($live ?? false) wire:key="brand-logo-preview-{{ md5((string) $previewUrl) }}" @endif
            src="{{ $previewUrl }}"
            alt="Brand logo preview"
            class="agricart-staff-form-preview__file-image agricart-category-form__upload-image"
        >
    @elseif (! $live)
        <img
            x-show="logoPreview"
            x-bind:src="logoPreview"
            alt="Brand logo preview"
            class="agricart-staff-form-preview__file-image agricart-category-form__upload-image"
        >
        <span class="agricart-staff-form-preview__file-text" x-show="! logoPreview">
            Drag &amp; Drop your files or <strong>Browse</strong>
        </span>
    @else
        <span class="agricart-staff-form-preview__file-text">
            Drag &amp; Drop your files or <strong>Browse</strong>
        </span>
    @endif
</label>
