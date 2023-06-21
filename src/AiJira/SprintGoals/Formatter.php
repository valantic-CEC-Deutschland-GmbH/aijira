<?php

declare(strict_types=1);

namespace AiJira\SprintGoals;

class Formatter
{
    public function splitTicketsByType(array $ticketData): array
    {
        $tasks = [];
        $stories = [];
        foreach ($ticketData['issues'] as $ticket) {
            $issueType = $ticket['fields']['issuetype']['name'];
            $formattedTicket = $this->formatTicketData($ticket);
            if ($issueType === 'Story') {
                $stories[] = $formattedTicket;
            } else {
                $tasks[] = $formattedTicket;
            }
        }

        return [$tasks, $stories];
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

    private function formatTicketData(array $ticket): array
    {
        $description = '';
        if (isset($ticket['fields']['description']['content'])) {
            $description = $this->formatDescription($ticket['fields']['description']['content']);
        }

        $labels = implode(',', $ticket['fields']['labels']);

        return [
            'title' => $ticket['fields']['summary'],
            'description' => $description,
            'labels' => $labels,
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
