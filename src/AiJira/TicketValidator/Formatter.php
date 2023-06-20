<?php

declare(strict_types=1);

namespace AiJira\TicketValidator;

class Formatter
{
    public function formatTicketData(array $ticket): array
    {
        $description = '';
        if (isset($ticket['fields']['description']['content'])) {
            $description = $this->formatDescription($ticket['fields']['description']['content']);
        }

        return [
            'title' => $ticket['fields']['summary'],
            'description' => $description,
        ];
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
}
