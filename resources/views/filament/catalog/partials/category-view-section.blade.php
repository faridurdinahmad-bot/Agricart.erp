<section class="agricart-category-view__section">
    <h3 class="agricart-category-view__section-title">{{ $title }}</h3>

    @if (! empty($fields))
        <dl class="agricart-category-view__grid">
            @foreach ($fields as $label => $value)
                <div class="agricart-category-view__item">
                    <dt>{{ $label }}</dt>
                    <dd @if(str_contains($label, 'UR') || str_contains($label, 'Urdu')) dir="rtl" lang="ur" @endif>{{ $value }}</dd>
                </div>
            @endforeach
        </dl>
    @endif

    @foreach ($groups ?? [] as $groupTitle => $groupFields)
        <h4 class="agricart-category-view__group-title">{{ $groupTitle }}</h4>
        <dl class="agricart-category-view__grid">
            @foreach ($groupFields as $label => $value)
                <div class="agricart-category-view__item">
                    <dt>{{ $label }}</dt>
                    <dd @if(str_contains($label, 'UR')) dir="rtl" lang="ur" @endif>{{ $value }}</dd>
                </div>
            @endforeach
        </dl>
    @endforeach
</section>
