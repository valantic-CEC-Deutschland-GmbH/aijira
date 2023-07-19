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

    public function generateSprintGoals(string $sprintName, ?string $overwritePrompt): string
    {
        $ticketData = $this->jiraClient->getTicketsBySprintName($sprintName);
        [$tasks, $stories] = $this->mapper->splitTicketsByType($ticketData);
        $labels = $this->jiraFormatter->extractLabels($tasks);

        if ($overwritePrompt) {
            return $this->openaiClient->getGeneratedSprintGoals([$tasks, $stories], [], $overwritePrompt);
        } else {
            return $this->openaiClient->getGeneratedSprintGoals($tasks, $labels, null) . "\n\nOverall:\n" . $this->openaiClient->getGeneratedSprintGoals($stories, [], null);
        }
    }
}
