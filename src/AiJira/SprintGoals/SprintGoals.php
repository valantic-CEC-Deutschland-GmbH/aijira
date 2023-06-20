<?php

declare(strict_types=1);

namespace AiJira\SprintGoals;

class SprintGoals
{
    public function generateSprintGoals(string $sprintName): string
    {
        $client = new Client();
        $formatter = new Formatter();

        $ticketData = $client->getTicketData($sprintName);
        [$tasks, $stories] = $formatter->splitTicketsByType($ticketData);
        $labels = $formatter->extractLabels($tasks);

        return $client->getGeneratedSprintGoals(json_encode($tasks), $labels) . "\n\nOverall:\n" . $client->getGeneratedSprintGoals(json_encode($stories));
    }
}
