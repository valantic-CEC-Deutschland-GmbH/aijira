<?php

declare(strict_types=1);

namespace AiJira\OpenAI;

use GuzzleHttp\Client;

class OpenAIClient
{
    public function getGeneratedSprintGoals(array $ticketData, array $labels = []): string
    {
        $endpoint = 'https://api.openai.com/v1/chat/completions';
        $goalsPerCategory = 3;
        $prompt = sprintf(
            'Generate one-sentence sprint goals based on the provided Jira ticket data%s. The sprint goal should shortly describe what will be done in this sprint. Do not just list the ticket titles but describe the most important tasks for this sprint. Generate a maximum of %s goals%s. Return only the sprint goals without comments or other text.',
            $labels ? ', splitting the goals into the categories "' . implode(', ', $labels) . '"' : '',
            $goalsPerCategory,
            $labels ? ' for each category' : ''
        );

        $data = [
            'temperature' => 0,
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $prompt,
                ],
                [
                    'role' => 'user',
                    'content' => json_encode($ticketData),
                ],
            ],
            'max_tokens' => 250,
        ];

        return $this->callApi($endpoint, $data);
    }

    public function getGeneratedTicketDescription(array $ticketData, array $fields): string
    {
        $endpoint = 'https://api.openai.com/v1/chat/completions';
        $prompt = sprintf(
            'Given the field data of a Jira ticket, your task is to enhance the wording of the ticket and provide revised versions of the fields %s. Please exclude any comments or extraneous text from your response.',
            '"' . implode(', ', $fields) . '"'
        );

        $data = [
            'temperature' => 0,
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $prompt,
                ],
                [
                    'role' => 'user',
                    'content' => json_encode($ticketData),
                ],
            ],
            'max_tokens' => 250,
        ];

        return $this->callApi($endpoint, $data);
    }

    public function getGeneratedTicketInterviewQuestions(array $ticket): string
    {
        $endpoint = 'https://api.openai.com/v1/chat/completions';
        $prompt = 'I will send you a ticket with all its details in JSON Format. 
    Please define questions that will improve ticket content quality.
    Only questions allowed, that are not already answered in provided ticket details.
    Please return a numbered list. Dont ask questions regarding ticket structure, just try understand the content of this ticket and ask your questions: ' . json_encode($ticket) . '
      ';

        $data = [
            'n' => 1,
            'temperature' => 0,
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $prompt,
                ],
            ],
        ];

        return $this->callApi($endpoint, $data);
    }

    private function callApi(string $endpoint, array $data): string
    {
        $response = (new Client())->request(
            'POST',
            $endpoint,
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . getenv('OPENAI_KEY'),
                ],
                'body' => json_encode($data),
            ]
        );

        $responseData = json_decode((string)$response->getBody(), true);

        return $responseData['choices'][0]['message']['content'] ?? '';
    }
}
