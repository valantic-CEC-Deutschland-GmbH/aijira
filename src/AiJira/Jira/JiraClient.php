<?php

declare(strict_types=1);

namespace AiJira\Jira;

class JiraClient
{
    public function getTicketsBySprintName(string $sprintName): array
    {
        $endpoint = sprintf(
            '%s%s',
            getenv('AI_JIRA_URL'),
            '/rest/api/3/search'
        );

        $data = [
            'jql' => sprintf('project = "%s" and Sprint = "%s" and (Sprint in futureSprints() or Sprint in openSprints()) and type not in(Epic) order by created DESC', getenv('AI_JIRA_PROJECT'), $sprintName),
            'fields' => [
                'summary',
                'description',
                'labels',
                'issuetype',
            ],
        ];

        return $this->callApi($endpoint, $data, 'POST');
    }

    public function getTicketByKey(string $ticketNumber): array
    {
        $endpoint = sprintf(
            '%s%s%s',
            getenv('AI_JIRA_URL'),
            '/rest/api/3/issue/',
            $ticketNumber
        );

        $data = [
            'fields' => [
                'summary',
                'description',
            ],
        ];

        return $this->callApi($endpoint, $data);
    }

    private function callApi(string $endpoint, array $data, string $method = 'GET'): ?array
    {
        $client = new \GuzzleHttp\Client();
        $response = $client->request(
            $method,
            $endpoint,
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Basic ' . base64_encode(getenv('AI_JIRA_EMAIL') . ':' . getenv('AI_JIRA_API_TOKEN')),
                ],
                'body' => json_encode($data),
            ]
        );

        return json_decode((string)$response->getBody(), true);
    }
}
