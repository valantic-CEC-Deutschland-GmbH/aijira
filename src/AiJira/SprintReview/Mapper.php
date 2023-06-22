<?php

declare(strict_types=1);

namespace AiJira\SprintReview;

class Mapper
{
    public function mapTaskFields(array $ticketData): array
    {
        $tasks = [];
        foreach ($ticketData['issues'] as $ticket) {
            $tasks[$ticket['key']] = ['title' => $ticket['fields']['summary'], 'estimation' => $ticket['fields']['timetracking']['originalEstimate']];
        }

        return $tasks;
    }
}
