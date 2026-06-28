@php
    use App\Modules\Catalog\Support\BrandWebsiteFormatter;

    $live = $live ?? false;
    $websiteProtocol = $websiteProtocol ?? BrandWebsiteFormatter::DEFAULT_PROTOCOL;
    $websiteDomain = $websiteDomain ?? '';
@endphp

<div class="agricart-category-form__field">
    <label class="agricart-category-form__label" for="brand_website_domain">Website</label>
    <div class="agricart-category-form__website-input">
        @if ($live)
            <select
                id="brand_website_protocol"
                class="agricart-category-form__control agricart-category-form__website-protocol"
                wire:model="brandForm.website_protocol"
                aria-label="Website protocol"
            >
                @foreach (BrandWebsiteFormatter::protocolOptions() as $option)
                    <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                @endforeach
            </select>
            <input
                id="brand_website_domain"
                type="text"
                class="agricart-category-form__control agricart-category-form__website-domain"
                placeholder="example.com"
                wire:model.blur="brandForm.website_domain"
                autocomplete="url"
            >
        @else
            <select
                id="brand_website_protocol"
                class="agricart-category-form__control agricart-category-form__website-protocol"
                x-model="websiteProtocol"
                aria-label="Website protocol"
            >
                @foreach (BrandWebsiteFormatter::protocolOptions() as $option)
                    <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                @endforeach
            </select>
            <input
                id="brand_website_domain"
                type="text"
                class="agricart-category-form__control agricart-category-form__website-domain"
                placeholder="example.com"
                x-model="websiteDomain"
                autocomplete="url"
            >
        @endif
    </div>
    <p class="agricart-category-form__hint">Select the protocol prefix, then enter the site address only.</p>
    @if ($live)
        @error('brandForm.website_domain') <span class="agricart-category-form__error">{{ $message }}</span> @enderror
        @error('brandForm.website_protocol') <span class="agricart-category-form__error">{{ $message }}</span> @enderror
    @endif
</div>
