<?php

declare(strict_types=1);

namespace AiJira\TicketInterview;

use AiJira\Jira\JiraClient;
use AiJira\Jira\JiraFormatter;
use AiJira\OpenAI\OpenAIClient;

class TicketInterview
{
    public function __construct()
    {
        $this->jiraClient = new JiraClient();
        $this->jiraFormatter = new JiraFormatter();
        $this->openaiClient = new OpenAIClient();
    }

    public function getTicketInterviewQuestions(string $ticketNumber): string
    {
        $ticketData = $this->jiraClient->getTicketByKey($ticketNumber);
        $formattedTicket = $this->jiraFormatter->formatTicketData($ticketData);

        return $this->openaiClient->getGeneratedTicketInterviewQuestions($formattedTicket);
    }
}
