<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Brand Review — {{ $document['meta']['entity_code'] ?? '' }}</title>
    <style>
        body {
            font-family: arial, sans-serif;
            font-size: 9.5px;
            color: #222;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        .pdf-en {
            font-family: arial, sans-serif;
            direction: ltr;
        }
        .pdf-urdu {
            font-family: notonaskharabic, arial, sans-serif;
            direction: rtl;
            unicode-bidi: embed;
            text-align: right;
        }
        .pdf-mixed-sep {
            font-family: arial, sans-serif;
            direction: ltr;
            unicode-bidi: isolate;
        }
        .pdf-empty { color: #777; font-style: italic; }
        h1 {
            font-size: 16px;
            font-weight: bold;
            margin: 0 0 2px;
            color: #111;
        }
        .meta { font-size: 8.5px; color: #555; margin-bottom: 7px; }
        h2 {
            font-size: 10.5px;
            font-weight: bold;
            margin: 9px 0 4px;
            padding-bottom: 2px;
            border-bottom: 1.5px solid #83B735;
            color: #111;
        }
        h3 {
            font-size: 9.5px;
            font-weight: bold;
            margin: 6px 0 2px;
            color: #333;
        }
        p { margin: 0 0 4px; }
        .overview { width: 100%; margin-bottom: 6px; border-collapse: collapse; }
        .overview-photo { width: 60px; vertical-align: top; padding-right: 8px; }
        .overview-photo img { width: 56px; height: 56px; object-fit: cover; }
        .overview-body { vertical-align: top; }
        .title-en { font-size: 13px; font-weight: bold; margin: 0 0 2px; }
        .title-ur { font-size: 11px; margin: 0 0 4px; text-align: right; }
        .facts { font-size: 8.5px; color: #444; line-height: 1.45; }
        .facts strong { color: #222; }
        .ruled {
            width: 100%;
            border-collapse: collapse;
            margin: 3px 0 6px;
            table-layout: fixed;
        }
        .ruled td, .ruled th {
            padding: 2px 4px 2px 0;
            vertical-align: top;
            border-bottom: 0.5px solid #ddd;
            font-size: 8.5px;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        .ruled th {
            font-weight: bold;
            color: #444;
            text-align: left;
            padding-bottom: 3px;
        }
        .ruled th.cell-ur { text-align: right; }
        .ruled td.label { width: 28%; font-weight: bold; color: #444; padding-right: 6px; }
        .ruled td.cell-en { text-align: left; }
        .ruled td.cell-ur { text-align: right; }
        .checklist { margin: 0; padding: 0; list-style: none; }
        .checklist li { margin: 0 0 1px; font-size: 8.5px; }
        .pass { color: #166534; }
        .fail { color: #991b1b; }
        .field-block { margin-bottom: 5px; }
        .field-label {
            font-weight: bold;
            color: #333;
            display: block;
            margin-bottom: 1px;
        }
        .field-value { margin: 0; text-align: left; }
        .field-value--urdu { text-align: right; }
        .review-lines { margin-top: 4px; }
        .line { border-bottom: 0.5px solid #999; height: 16px; margin: 8px 0 3px; }
        .footer {
            margin-top: 10px;
            padding-top: 4px;
            border-top: 0.5px solid #ccc;
            font-size: 7.5px;
            color: #666;
            text-align: center;
        }
    </style>
</head>
<body>
@php
    use App\Core\ContentAudit\Support\ContentAuditPdfLayout;
    use App\Core\ContentAudit\Support\ContentAuditPdfTypography;

    $meta = $document['meta'] ?? [];
    $image = $document['image'] ?? [];
    $analysis = $document['analysis'] ?? [];
    $structure = $document['structure'] ?? [];
    $review = $document['review'] ?? [];

    $englishName = $meta['entity_label'] ?? ContentAuditPdfLayout::summaryValue($document, 'English Name');
    $urduName = ContentAuditPdfLayout::summaryValue($document, 'Urdu Name');
    $contentStatus = ContentAuditPdfLayout::qualityMetric($analysis, 'AI Content Status');
    $seoStatus = ContentAuditPdfLayout::qualityMetric($analysis, 'SEO Completeness');

    $fieldValue = static function (array $fields, string $label): ?string {
        $field = collect($fields)->firstWhere('label', $label);

        return filled($field['value'] ?? null) ? (string) $field['value'] : null;
    };

    $aiFields = ContentAuditPdfLayout::sectionFields($document, 'AI Content');
    $seoFields = ContentAuditPdfLayout::sectionFields($document, 'SEO');
    $searchFields = ContentAuditPdfLayout::sectionFields($document, 'Search');
    $marketFields = ContentAuditPdfLayout::sectionFields($document, 'Marketplace');

    $renderField = static function (string $label, ?string $value, bool $forceUrdu = false): void {
        $isUrdu = $forceUrdu || ContentAuditPdfTypography::shouldRenderAsUrdu($label, $value);
        echo '<div class="field-block">';
        echo '<span class="field-label">'.e($label).'</span>';

        if (! filled($value)) {
            echo '<p class="field-value">'.ContentAuditPdfTypography::emptyHtml($label).'</p>';
        } elseif ($isUrdu) {
            echo '<p class="field-value field-value--urdu">'.ContentAuditPdfTypography::urduSpan($value).'</p>';
        } else {
            echo '<p class="field-value">'.ContentAuditPdfTypography::englishSpan($value).'</p>';
        }

        echo '</div>';
    };

    $renderFieldList = static function (array $fields) use ($renderField): void {
        foreach ($fields as $field) {
            $label = (string) ($field['label'] ?? '');
            $value = $field['value'] ?? null;
            $text = is_string($value) ? $value : (filled($value) ? (string) $value : null);
            $renderField($label, $text);
        }
    };
@endphp

<h1>Brand Review Report</h1>
<p class="meta">
    {{ $meta['entity_code'] ?? '' }} · {{ $englishName ?? '—' }} · Exported {{ $meta['exported_at'] ?? '' }}
</p>

<table class="overview">
    <tr>
        <td class="overview-photo">
            @if (! empty($image['base64_data_uri']))
                <img src="{{ $image['base64_data_uri'] }}" alt="">
            @endif
        </td>
        <td class="overview-body">
            <p class="title-en">{{ $englishName ?? '—' }}</p>
            <div class="title-ur">
                @if (filled($urduName))
                    {!! ContentAuditPdfTypography::urduSpan($urduName) !!}
                @else
                    {!! ContentAuditPdfTypography::emptyHtml('Urdu Name') !!}
                @endif
            </div>
            <p class="facts">
                <strong>Code:</strong> {{ ContentAuditPdfLayout::summaryValue($document, 'brand Code') ?? '—' }}
                · <strong>Level:</strong> {{ $structure['level'] ?? '—' }}
                · <strong>Status:</strong> {{ ContentAuditPdfLayout::summaryValue($document, 'Status') ?? '—' }}
                · <strong>HS Code:</strong> {{ ContentAuditPdfLayout::summaryValue($document, 'HS Code') ?? '—' }}<br>
                <strong>Children:</strong> {{ $structure['children_count'] ?? 0 }}
                · <strong>Products:</strong> {{ ($structure['products_module_connected'] ?? false) ? ($structure['products_count'] ?? 0) : '0 (pending)' }}
                · <strong>Content status:</strong> {{ $contentStatus['value'] ?? '—' }}
                · <strong>SEO completeness:</strong> {{ $seoStatus['value'] ?? '—' }}%<br>
                <strong>Hierarchy:</strong> {{ $meta['hierarchy_breadcrumb'] ?? 'Root level' }}
            </p>
        </td>
    </tr>
</table>

@if (($document['parent_hierarchy'] ?? []) !== [])
    <h2>Parent Hierarchy</h2>
    <table class="ruled">
        <thead>
            <tr>
                <th style="width:8%;">Level</th>
                <th style="width:16%;">Code</th>
                <th style="width:38%;" class="cell-en">English</th>
                <th style="width:38%;" class="cell-ur">Urdu</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($document['parent_hierarchy'] as $level)
                <tr>
                    <td>{{ $level['level'] ?? '' }}</td>
                    <td>{{ $level['code'] ?? '' }}</td>
                    <td class="cell-en">{{ $level['english_name'] ?? '—' }}</td>
                    <td class="cell-ur">
                        @if (filled($level['urdu_name'] ?? null))
                            {!! ContentAuditPdfTypography::urduSpan($level['urdu_name']) !!}
                        @else
                            {!! ContentAuditPdfTypography::emptyHtml(null) !!}
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

<h2>Review Summary</h2>

<h3>Missing Fields</h3>
@php($missingFields = $analysis['missing_fields'] ?? [])
@if ($missingFields === [])
    <p>All required fields are present.</p>
@else
    <table class="ruled">
        @foreach ($missingFields as $field)
            <tr>
                <td class="label">{{ ucfirst($field['severity'] ?? '') }}</td>
                <td class="cell-en">{{ $field['label'] ?? '' }} <span style="color:#666;">({{ $field['section'] ?? '' }})</span></td>
            </tr>
        @endforeach
    </table>
@endif

<h3>Quality Checklist</h3>
<ul class="checklist">
    @foreach ($analysis['ai_review_checklist'] ?? [] as $item)
        <li @class([($item['status'] ?? '') === 'pass' ? 'pass' : 'fail'])>
            {{ ($item['status'] ?? '') === 'pass' ? '✓' : '✗' }}
            {{ $item['item'] ?? '' }}
            @if (filled($item['detail'] ?? null))
                <span style="color:#666;"> — {{ $item['detail'] }}</span>
            @endif
        </li>
    @endforeach
</ul>

<h2>brand Content</h2>

<h3>Descriptions</h3>
@php($renderField('Short Description (English)', $fieldValue($aiFields, 'Short Description (English)')))
@php($renderField('Short Description (Urdu)', $fieldValue($aiFields, 'Short Description (Urdu)'), true))
@php($renderField('Long Description (English)', $fieldValue($aiFields, 'Long Description (English)')))
@php($renderField('Long Description (Urdu)', $fieldValue($aiFields, 'Long Description (Urdu)'), true))

<h3>Usage, Benefits &amp; Warnings</h3>
@php($renderField('Usage (English)', $fieldValue($aiFields, 'Usage (English)')))
@php($renderField('Usage (Urdu)', $fieldValue($aiFields, 'Usage (Urdu)'), true))
@php($renderField('Benefits (English)', $fieldValue($aiFields, 'Benefits (English)')))
@php($renderField('Benefits (Urdu)', $fieldValue($aiFields, 'Benefits (Urdu)'), true))
@php($renderField('Warnings (English)', $fieldValue($aiFields, 'Warnings (English)')))
@php($renderField('Warnings (Urdu)', $fieldValue($aiFields, 'Warnings (Urdu)'), true))

<h2>SEO</h2>
@php($renderFieldList($seoFields))

<h2>Search</h2>
@php($renderFieldList($searchFields))

<h2>Marketplace</h2>
@php($renderFieldList($marketFields))

<h2>Senior Review &amp; Approval</h2>
<p style="font-size:8.5px;color:#555;">{{ $review['senior_review_notes_instructions'] ?? 'Record reviewer observations and approval decision below.' }}</p>
<div class="review-lines">
    <p><strong>Reviewer name</strong></p>
    <div class="line"></div>
    <p><strong>Review date</strong></p>
    <div class="line"></div>
    <p><strong>Decision:</strong> Approved &nbsp;&nbsp;/&nbsp;&nbsp; Needs revision &nbsp;&nbsp;/&nbsp;&nbsp; Rejected</p>
    <div class="line"></div>
    <p><strong>Notes (English)</strong></p>
    <div class="line" style="height:30px;"></div>
    <p><strong>Notes (Urdu)</strong></p>
    <div class="line" style="height:30px;"></div>
    <p><strong>Authorised signature</strong></p>
    <div class="line"></div>
</div>

<p class="footer">Agricart ERP — Brand Review report</p>
</body>
</html>

