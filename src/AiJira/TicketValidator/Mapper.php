<?php

declare(strict_types=1);

namespace AiJira\TicketValidator;

class Mapper
{
    public function mapTicketTypeFields(string $ticketType): array
    {
        return match ($ticketType) {
            'Bug' => [
                'Title',
                'Description',
                'Environment',
                'Open questions',
                'Acceptance criteria',
                'Details',
            ],
            default => [
                'Title',
                'Description',
                'Open questions',
                'Acceptance criteria',
                'Details'
            ]
        };
    }
}
