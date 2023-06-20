<?php

declare(strict_types=1);

namespace AiJira\Jira;

use GuzzleHttp\Client;

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
                'timetracking'
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
        try {
            $response = (new Client())->request(
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
        } catch (\Exception $e) {
            echo "Error while fetching jira data \n" . $e->getMessage();
            exit;
        }

        return json_decode((string)$response->getBody(), true);
    }
}
