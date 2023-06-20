<?php

declare(strict_types=1);

namespace AiJira\SprintGoals;

class Client
{
    public function getGeneratedSprintGoals(string $ticketData, string $labels = ''): string
    {
        $openAiKey = getenv('OPENAI_KEY');
        $endpoint = 'https://api.openai.com/v1/chat/completions';

        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $openAiKey,
        ];

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

        $responseData = $this->callApi($endpoint, $headers, $data);
        $message = $responseData['choices'][0]['message']['content'];

        return str_replace('"', '', $message);
    }

    public function getTicketData(string $sprintName): array
    {
        $endpoint = sprintf(
            '%s%s',
            getenv('AI_JIRA_URL'),
            '/rest/api/3/search'
        );

        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Basic ' . base64_encode(getenv('AI_JIRA_EMAIL') . ':' . getenv('AI_JIRA_API_TOKEN')),
        ];

        $data = [
            'jql' => sprintf('project = "%s" and Sprint = "%s" and (Sprint in futureSprints() or Sprint in openSprints()) and type not in(Epic) order by created DESC', getenv('AI_JIRA_PROJECT'), $sprintName),
            'fields' => [
                'description',
                'labels',
                'summary',
                'issuetype',
                'timetracking'
            ],
        ];

        return $this->callApi($endpoint, $headers, $data);
    }

    private function callApi(string $endpoint, array $headers, array $data): ?array
    {
        $client = new \GuzzleHttp\Client();
        $response = $client->request(
            'POST',
            $endpoint,
            [
                'headers' => $headers,
                'body' => json_encode($data),
            ]
        );

        return json_decode((string)$response->getBody(), true);
    }
}
