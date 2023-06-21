<?php

declare(strict_types=1);

namespace AiJira\TicketValidator;

class Formatter
{
    public function formatTicketData(array $ticket): array
    {
        $description = '';
        $fields = $ticket['fields'];
        if (isset($fields['description']['content'])) {
            $description = $this->formatDescription($fields['description']['content']);
        }

        return [
            'title' => $fields['summary'],
            'description' => $description,
            'type' => $ticket['fields']['issuetype']['name'],
            'fields' => $this->extractCustomFields($fields),
        ];
    }

    public function mapTicketTypeFields(string $ticketType): array
    {
        return match ($ticketType) {
            'Bug' => [
                'Title',
                'Description',
                'Environment',
                'Open questions',
                'Acceptance criteria',
                'Details',
            ],
            default => [
                'Title',
                'Description',
                'Open questions',
                'Acceptance criteria',
                'Details'
            ]
        };
    }

    private function formatDescription(array $descriptionParts): string
    {
        $description = '';

        foreach ($descriptionParts as $content) {
            if ($content['type'] === null || $content['type'] !== 'paragraph') {
                continue;
            }

            foreach ($content['content'] as $text) {
                if ($text['type'] === 'text') {
                    $description .= $text['text'];
                }
            }
        }

        return $description;
    }

    private function extractCustomFields(array $fields): array
    {
        $customFields = [];
        foreach ($fields as $key => $field) {
            if (!empty($field['content']) && str_starts_with($key, 'customfield')) {
                $content = $this->formatContent($field['content'], '');
                $customFields[$key] = $content;
            }
        }

        return $customFields;
    }

    private function formatContent($content, $indent = ''): string
    {
        $result = '';

        foreach ($content as $element) {
            if ($element['type'] === 'codeBlock') {
                continue;
            }

            if (isset($element['content'])) {
                $result .= $this->formatContent($element['content'], $indent) . "\n";
            } elseif ($element['type'] === 'text') {
                $result .= $indent . $element['text'];
            }
        }

        return $result;
    }
}
