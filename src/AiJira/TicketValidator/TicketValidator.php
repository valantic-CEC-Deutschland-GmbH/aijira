<?php

declare(strict_types=1);

namespace AiJira\TicketValidator;

use AiJira\Jira\JiraClient;
use AiJira\OpenAI\OpenAIClient;

class TicketValidator
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

    public function validateTicketDescription(string $ticketNumber): string
    {
        $ticketData = $this->jiraClient->getTicketByKey($ticketNumber);
        $formattedTicket = $this->formatter->formatTicketData($ticketData);

        return $this->openaiClient->getGeneratedTicketDescription(json_encode($formattedTicket));
    }
}
