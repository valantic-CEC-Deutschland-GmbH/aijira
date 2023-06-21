<?php

declare(strict_types=1);

namespace AiJira\SprintGoals;

use AiJira\Jira\JiraFormatter;

class Mapper
{
    public function __construct(private JiraFormatter $jiraFormatter)
    {
    }

    public function splitTicketsByType(array $ticketData): array
    {
        $tasks = [];
        $stories = [];
        foreach ($ticketData['issues'] as $ticket) {
            $formattedTicket = $this->jiraFormatter->formatTicketData($ticket);
            $issueType = $formattedTicket['type'];
            if ($issueType === 'Story') {
                $stories[] = $formattedTicket;
            } else {
                $tasks[] = $formattedTicket;
            }
        }

        return [$tasks, $stories];
    }
}
