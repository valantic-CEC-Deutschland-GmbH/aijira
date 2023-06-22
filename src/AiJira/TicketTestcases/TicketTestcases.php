<?php

declare(strict_types=1);

namespace AiJira\TicketTestcases;

use AiJira\Jira\JiraClient;
use AiJira\Jira\JiraFormatter;
use AiJira\OpenAI\OpenAIClient;

class TicketTestcases
{
    public function __construct()
    {
        $this->jiraClient = new JiraClient();
        $this->jiraFormatter = new JiraFormatter();
        $this->openaiClient = new OpenAIClient();
    }

    public function getTicketTestcases(string $ticketNumber): string
    {
        $ticketData = $this->jiraClient->getTicketByKey($ticketNumber);
        $formattedTicket = $this->jiraFormatter->formatTicketData($ticketData);

        return $this->openaiClient->getGeneratedTicketTestcases($formattedTicket);
    }
}
