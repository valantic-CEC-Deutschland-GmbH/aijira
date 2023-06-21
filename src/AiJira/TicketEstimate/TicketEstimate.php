<?php

declare(strict_types=1);

namespace AiJira\TicketEstimate;

use AiJira\Jira\JiraClient;
use AiJira\Jira\JiraFormatter;
use AiJira\OpenAI\OpenAIClient;

class TicketEstimate
{
    public function __construct()
    {
        $this->jiraClient = new JiraClient();
        $this->jiraFormatter = new JiraFormatter();
        $this->openaiClient = new OpenAIClient();
    }

    public function getTicketEstimation(string $ticketNumber): string
    {
        $ticketData = $this->jiraClient->getTicketByKey($ticketNumber);
        $ticketData = $this->jiraFormatter->formatTicketData($ticketData);

        $referenceTickets = $this->jiraClient->getEstimatedTickets();
        $formattedReferenceTickets = [];
        foreach ($referenceTickets['issues'] as $referenceTicket)
        {
            $formattedReferenceTickets[] = $this->jiraFormatter->formatTicketData($referenceTicket);
        }

        return $this->openaiClient->getGeneratedTicketEstimation($ticketData, $formattedReferenceTickets);
    }
}
