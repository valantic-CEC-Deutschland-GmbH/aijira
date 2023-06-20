<?php

declare(strict_types=1);

namespace AiJira\SprintGoals;

use AiJira\Jira\JiraClient;
use AiJira\OpenAI\OpenAIClient;

class SprintGoals
{
    private JiraClient $jiraClient;
    private OpenAIClient $openaiClient;
    private Formatter $formatter;

    public function __construct()
    {
        $this->jiraClient = new JiraClient();
        $this->openaiClient = new OpenAIClient();
        $this->formatter = new Formatter();
    }

    public function generateSprintGoals(string $sprintName): string
    {
        $ticketData = $this->jiraClient->getTicketsBySprintName($sprintName);
        [$tasks, $stories] = $this->formatter->splitTicketsByType($ticketData);
        $labels = $this->formatter->extractLabels($tasks);

        return $this->openaiClient->getGeneratedSprintGoals(json_encode($tasks), $labels) . "\n\nOverall:\n" . $this->openaiClient->getGeneratedSprintGoals(json_encode($stories));
    }
}
