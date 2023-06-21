<?php

declare(strict_types=1);

namespace AiJira\SprintGoals;

use AiJira\Jira\JiraClient;
use AiJira\Jira\JiraFormatter;
use AiJira\OpenAI\OpenAIClient;

class SprintGoals
{
    public function __construct()
    {
        $this->jiraClient = new JiraClient();
        $this->jiraFormatter = new JiraFormatter();
        $this->openaiClient = new OpenAIClient();
        $this->mapper = new Mapper($this->jiraFormatter);
    }

    public function generateSprintGoals(string $sprintName): string
    {
        $ticketData = $this->jiraClient->getTicketsBySprintName($sprintName);
        [$tasks, $stories] = $this->mapper->splitTicketsByType($ticketData);
        $labels = $this->jiraFormatter->extractLabels($tasks);

        return $this->openaiClient->getGeneratedSprintGoals($tasks, $labels) . "\n\nOverall:\n" . $this->openaiClient->getGeneratedSprintGoals($stories);
    }
}
