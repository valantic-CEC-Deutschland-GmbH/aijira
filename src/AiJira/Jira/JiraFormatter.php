<?php

declare(strict_types=1);

namespace AiJira\Jira;

class JiraFormatter
{
    public function formatTicketData(array $ticket): array
    {
        $description = '';
        $fields = $ticket['fields'];
        if (isset($fields['description']['content'])) {
            $description = $this->formatDescription($fields['description']['content']);
        }

        $labels = implode(',', $fields['labels']);

        $formattedTicketData = [
            'title' => $fields['summary'],
            'description' => $description,
            'type' => $ticket['fields']['issuetype']['name'],
            'fields' => $this->extractCustomFields($fields),
            'labels' => $labels,
        ];
        foreach ($formattedTicketData['fields'] as $key => $value) {
            if (trim($value) === '.') {
                $formattedTicketData['fields'][$key] = 'N/A';
            }
        }

        return $formattedTicketData;
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

    public function extractLabels(array $tickets): array
    {
        $formattedLabels = [];
        foreach ($tickets as $ticket) {
            array_map(function (string $label) use (&$formattedLabels) {
                $formattedLabels[] = $label;
            }, explode(',', $ticket['labels']));
        }

        return array_unique($formattedLabels);
    }
}
