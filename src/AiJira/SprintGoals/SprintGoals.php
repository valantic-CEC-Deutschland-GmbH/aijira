<?php

declare(strict_types=1);

namespace AiJira\SprintGoals;

class SprintGoals
{
    private Client $client;
    private Formatter $formatter;

    public function __construct()
    {
        $this->client = new Client();
        $this->formatter = new Formatter();
    }

    public function generateSprintGoals(string $sprintName): string
    {
        $ticketData = $this->client->getTicketData($sprintName);
        [$tasks, $stories] = $this->formatter->splitTicketsByType($ticketData);
        $labels = $this->formatter->extractLabels($tasks);

        return $this->client->getGeneratedSprintGoals(json_encode($tasks), $labels) . "\n\nOverall:\n" . $this->client->getGeneratedSprintGoals(json_encode($stories));
    }
}
