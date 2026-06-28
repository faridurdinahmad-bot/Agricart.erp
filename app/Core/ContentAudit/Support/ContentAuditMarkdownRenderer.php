<?php

namespace App\Core\ContentAudit\Support;

final class ContentAuditMarkdownRenderer
{
    /**
     * @param  array<string, mixed>  $document
     */
    public function render(array $document): string
    {
        $lines = [];

        $meta = $document['meta'] ?? [];
        $title = trim((string) ($meta['entity_label'] ?? $meta['entity_code'] ?? 'Content Review'));

        $lines[] = '# Content Review Export';
        $lines[] = '';
        $lines[] = '## '.$title;
        $lines[] = '';
        $lines[] = $this->metaBlock($meta);
        $lines[] = '';

        if ($analysis = $document['analysis'] ?? []) {
            $lines[] = '## Missing Fields Summary';
            $lines[] = '';

            if ($missing = $analysis['missing_fields'] ?? []) {
                foreach ($missing as $field) {
                    $lines[] = sprintf(
                        '- [%s] **%s** (%s)',
                        strtoupper((string) ($field['severity'] ?? 'INFO')),
                        (string) ($field['label'] ?? ''),
                        (string) ($field['section'] ?? ''),
                    );
                }
            } else {
                $lines[] = '- No critical or recommended missing fields detected.';
            }

            $lines[] = '';
            $lines[] = '## Content Quality Summary';
            $lines[] = '';
            $lines[] = $this->qualityTable($analysis['content_quality'] ?? []);
            $lines[] = '';
            $lines[] = '## AI Review Checklist';
            $lines[] = '';
            $lines[] = $this->checklistTable($analysis['ai_review_checklist'] ?? []);
            $lines[] = '';
        }

        if ($structure = $document['structure'] ?? []) {
            $lines[] = '## Category Level & Counts';
            $lines[] = '';
            $lines[] = $this->fieldTable([
                ContentAuditFieldFormatter::field('Category Level', (string) ($structure['level'] ?? '')),
                ContentAuditFieldFormatter::field('Children Count', (string) ($structure['children_count'] ?? 0)),
                ContentAuditFieldFormatter::field('Products Count', ($structure['products_module_connected'] ?? false)
                    ? (string) ($structure['products_count'] ?? 0)
                    : '0 (Products module not connected)'),
            ]);
            $lines[] = '';
        }

        if ($image = $document['image'] ?? []) {
            $lines[] = '## Image Details';
            $lines[] = '';
            $lines[] = $this->fieldTable([
                ContentAuditFieldFormatter::field('Filename', $image['filename'] ?? null),
                ContentAuditFieldFormatter::field('Format', $image['format'] ?? null),
                ContentAuditFieldFormatter::field('MIME Type', $image['mime_type'] ?? null),
                ContentAuditFieldFormatter::field('File Size', $image['size_human'] ?? null),
                ContentAuditFieldFormatter::field('Dimensions', filled($image['width'] ?? null)
                    ? ($image['width'].' × '.$image['height'].' px')
                    : null),
                ContentAuditFieldFormatter::field('Storage Path', $image['path'] ?? null),
                ContentAuditFieldFormatter::field('Public URL', $image['url'] ?? null),
            ]);
            $lines[] = '';
        }

        if ($summary = $document['summary'] ?? []) {
            $lines[] = '## Summary';
            $lines[] = '';
            $lines[] = $this->fieldTable($summary);
            $lines[] = '';
        }

        if ($hierarchy = $document['parent_hierarchy'] ?? []) {
            $lines[] = '## Parent Hierarchy';
            $lines[] = '';

            foreach ($hierarchy as $level) {
                $current = ($level['is_current'] ?? false) ? ' *(current)*' : '';
                $lines[] = sprintf(
                    '%d. **%s** (%s) — %s%s',
                    (int) ($level['level'] ?? 0),
                    (string) ($level['english_name'] ?? '—'),
                    (string) ($level['code'] ?? '—'),
                    (string) ($level['urdu_name'] ?? '—'),
                    $current,
                );
            }

            if ($breadcrumb = $meta['hierarchy_breadcrumb'] ?? null) {
                $lines[] = '';
                $lines[] = '**Breadcrumb:** '.$breadcrumb;
            }

            $lines[] = '';
        }

        foreach ($document['sections'] ?? [] as $section) {
            $lines[] = '## '.(string) ($section['title'] ?? 'Section');
            $lines[] = '';

            if ($fields = $section['fields'] ?? []) {
                $lines[] = $this->fieldTable($fields);
                $lines[] = '';
            }

            foreach ($section['groups'] ?? [] as $group) {
                if ($groupTitle = $group['title'] ?? null) {
                    $lines[] = '### '.$groupTitle;
                    $lines[] = '';
                }

                if ($groupFields = $group['fields'] ?? []) {
                    $lines[] = $this->fieldTable($groupFields);
                    $lines[] = '';
                }
            }
        }

        if ($review = $document['review'] ?? []) {
            $lines[] = '## Senior Review Notes';
            $lines[] = '';
            $lines[] = (string) ($review['senior_review_notes_instructions'] ?? 'Add reviewer notes below.');
            $lines[] = '';
            $lines[] = '```';
            $lines[] = (string) ($review['senior_review_notes'] ?? '');
            $lines[] = '';
            $lines[] = 'Reviewer:';
            $lines[] = 'Date:';
            $lines[] = 'Decision: [ ] Approved  [ ] Needs Revision  [ ] Rejected';
            $lines[] = '```';
            $lines[] = '';
        }

        $lines[] = '---';
        $lines[] = '';
        $lines[] = '_Generated for professional AI and SEO content review. No sensitive credentials are included._';

        return rtrim(implode("\n", $lines))."\n";
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    protected function metaBlock(array $meta): string
    {
        $rows = [
            ContentAuditFieldFormatter::field('Export Type', $meta['export_type'] ?? 'content_review'),
            ContentAuditFieldFormatter::field('Module', ucfirst((string) ($meta['module'] ?? ''))),
            ContentAuditFieldFormatter::field('Entity', ucfirst((string) ($meta['entity'] ?? ''))),
            ContentAuditFieldFormatter::field('Entity Code', $meta['entity_code'] ?? null),
            ContentAuditFieldFormatter::field('Exported At', $meta['exported_at'] ?? null),
        ];

        return $this->fieldTable($rows);
    }

    /**
     * @param  list<array{metric: string, value: string, status: string}>  $rows
     */
    protected function qualityTable(array $rows): string
    {
        $lines = [];

        foreach ($rows as $row) {
            $icon = match ($row['status'] ?? '') {
                'good' => '✅',
                'warn' => '⚠️',
                default => '❌',
            };
            $lines[] = sprintf('- %s **%s:** %s', $icon, $row['metric'] ?? '', $row['value'] ?? '');
        }

        return implode("\n", $lines);
    }

    /**
     * @param  list<array{item: string, status: string, detail: string|null}>  $rows
     */
    protected function checklistTable(array $rows): string
    {
        $lines = [];

        foreach ($rows as $row) {
            $icon = ($row['status'] ?? '') === 'pass' ? '[x]' : '[ ]';
            $line = '- '.$icon.' '.($row['item'] ?? '');

            if (filled($row['detail'] ?? null)) {
                $line .= ' — '.$row['detail'];
            }

            $lines[] = $line;
        }

        return implode("\n", $lines);
    }

    /**
     * @param  list<array{label: string, value: string|null}>  $fields
     */
    protected function fieldTable(array $fields): string
    {
        $lines = [];

        foreach ($fields as $field) {
            $label = (string) ($field['label'] ?? '');
            $value = $field['value'] ?? null;
            $lines[] = '- **'.$label.':** '.$this->displayValue($value);
        }

        return implode("\n", $lines);
    }

    protected function displayValue(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '*(not set)*';
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        $string = trim((string) $value);

        if (str_contains($string, "\n")) {
            return "\n\n  ```\n  ".str_replace("\n", "\n  ", $string)."\n  ```";
        }

        return $string;
    }
}
