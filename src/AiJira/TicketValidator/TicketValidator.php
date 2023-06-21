<?php

declare(strict_types=1);

namespace AiJira\TicketValidator;

use AiJira\Jira\JiraClient;
use AiJira\Jira\JiraFormatter;
use AiJira\OpenAI\OpenAIClient;

class TicketValidator
{
    public function __construct()
    {
        $this->jiraClient = new JiraClient();
        $this->jiraFormatter = new JiraFormatter();
        $this->openaiClient = new OpenAIClient();
        $this->mapper = new Mapper();
    }

    public function validateTicketDescription(string $ticketNumber): string
    {
        $ticketData = $this->jiraClient->getTicketByKey($ticketNumber);
        $formattedTicket = $this->jiraFormatter->formatTicketData($ticketData);
        $ticketFields = $this->mapper->mapTicketTypeFields($formattedTicket['type']);

        return $this->openaiClient->getGeneratedTicketDescription($formattedTicket, $ticketFields);
    }
}
