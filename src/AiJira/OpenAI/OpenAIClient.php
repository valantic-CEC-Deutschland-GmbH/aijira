<?php

declare(strict_types=1);

namespace AiJira\OpenAI;

class OpenAIClient
{
    public function getGeneratedSprintGoals(string $ticketData, string $labels = ''): string
    {
        $endpoint = 'https://api.openai.com/v1/chat/completions';
        $goalsPerCategory = 3;
        $prompt = sprintf(
            'Generate one-sentence sprint goals based on the provided Jira ticket data%s. The sprint goal should shortly describe what will be done in this sprint. Do not just list the ticket titles but describe the most important tasks for this sprint. Generate a maximum of %s goals%s. Return only the sprint goals without comments or other text.',
            $labels ? ', splitting the goals into the categories "' . $labels . '"' : '',
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
                    'content' => $ticketData,
                ],
            ],
            'max_tokens' => 250,
        ];

        return $this->callApi($endpoint, $data);
    }

    public function getGeneratedTicketDescription(string $ticketData): string
    {
        $endpoint = 'https://api.openai.com/v1/chat/completions';
        $prompt = 'Given the title and description of a Jira ticket, your task is to enhance the wording of the ticket and provide revised versions of the title and description. Please exclude any comments or extraneous text from your response.';

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
                    'content' => $ticketData,
                ],
            ],
            'max_tokens' => 250,
        ];

        return $this->callApi($endpoint, $data);
    }

    private function callApi(string $endpoint, array $data): string
    {
        $client = new \GuzzleHttp\Client();
        $response = $client->request(
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
