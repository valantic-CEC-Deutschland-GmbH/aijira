<?php

declare(strict_types=1);

namespace AiJira\TicketInterview;

use AiJira\Jira\JiraClient;
use AiJira\OpenAI\OpenAIClient;
use AiJira\TicketValidator\Formatter;

class TicketInterview
{
    public function __construct()
    {
        $this->jiraClient = new JiraClient();
        $this->openaiClient = new OpenAIClient();
        $this->formatter = new Formatter();
    }

    public function getTicketInterviewQuestions(string $ticketNumber): string
    {
        $ticketData = $this->jiraClient->getTicketByKey($ticketNumber);
        $formattedTicket = (new Formatter())->formatTicketData($ticketData);

        return $this->openaiClient->getGeneratedTicketInterviewQuestions($formattedTicket);
    }
}
