<?php

declare(strict_types=1);

namespace AiJira\Jira;

use GuzzleHttp\Client;

class JiraClient
{
    private const JIRA_SEARCH_ENDPOINT = '/rest/api/3/search';

    public function getTicketsBySprintName(string $sprintName): array
    {
        $endpoint = sprintf(
            '%s%s',
            getenv('AI_JIRA_URL'),
            self::JIRA_SEARCH_ENDPOINT
        );

        $data = [
            'jql' => sprintf('project = "%s" and Sprint = "%s" and (Sprint in futureSprints() or Sprint in openSprints()) and type not in (Epic) order by created DESC', getenv('AI_JIRA_PROJECT'), $sprintName),
            'fields' => [
                'summary',
                'description',
                'labels',
                'issuetype',
                'timetracking'
            ],
        ];

        return $this->postApi($endpoint, $data);
    }

    public function getTasksBySprintName(string $sprintName): array
    {
        $endpoint = sprintf(
            '%s%s',
            getenv('AI_JIRA_URL'),
            self::JIRA_SEARCH_ENDPOINT
        );

        $data = [
            'jql' => sprintf('project = "%s" and Sprint = "%s" and (Sprint in futureSprints() or Sprint in openSprints()) and type not in (Epic, Story) order by created DESC', getenv('AI_JIRA_PROJECT'), $sprintName),
            'fields' => [
                'summary',
                'timetracking'
            ],
        ];

        return $this->postApi($endpoint, $data);
    }

    public function getEstimatedTickets(): array
    {
        $endpoint = sprintf(
            '%s%s',
            getenv('AI_JIRA_URL'),
            self::JIRA_SEARCH_ENDPOINT
        );

        $data = [
            'jql' => sprintf('project = "%s" AND status = Done and originalEstimate > 0 ORDER BY updated', getenv('AI_JIRA_PROJECT')),
            'fields' => [
                'summary',
                'description',
                'labels',
                'issuetype',
                'timetracking'
            ],
            'maxResults' => 30
        ];

        return $this->postApi($endpoint, $data);
    }

    public function getFields(): array
    {
        $endpoint = sprintf(
            '%s%s',
            getenv('AI_JIRA_URL'),
            '/rest/api/3/field/',
        );

        return $this->getApi($endpoint);
    }

    public function getTicketByKey(string $ticketNumber): array
    {
        $endpoint = sprintf(
            '%s%s%s',
            getenv('AI_JIRA_URL'),
            '/rest/api/3/issue/',
            $ticketNumber
        );

        return $this->getApi($endpoint);
    }

    public function getSprint(string $paramSprintName): array
    {
        $checkedSprints = [];

        $boards = $this->getBoards();
        foreach ($boards['values'] as $board) {
            if ($board['type'] !== 'scrum') {
                continue;
            }

            $sprints = $this->getSprints($board['id']);
            if (!isset($sprints['values'])) {
                continue;
            }

            $checkedSprints = array_merge($checkedSprints, $sprints['values']);

            $requestedJiraSprint = [];
            foreach ($sprints['values'] as $sprint) {
                if ($sprint['name'] === $paramSprintName) {
                    $requestedJiraSprint = $sprint;
                }
            }
        }

        if (empty($requestedJiraSprint)) {
            echo sprintf(
                'Provided Sprint not found when checking %s. List of valid options: %s',
                getenv('AI_JIRA_URL'), json_encode(array_map(fn($sprint) => [$sprint['name']], $checkedSprints))
            );
            exit(0);
        }

        return $requestedJiraSprint;
    }

    private function getBoards(): array
    {
        $endpoint = sprintf(
            '%s/rest/agile/1.0/board',
            getenv('AI_JIRA_URL'),
        );
        $data = [
            'projectKeyOrId' => getenv('AI_JIRA_PROJECT')
        ];

        return $this->getApi($endpoint, $data);
    }

    private function getSprints($boardId): array
    {
        $endpoint = sprintf(
            '%s/rest/agile/1.0/board/%s/sprint',
            getenv('AI_JIRA_URL'),
            $boardId
        );

        return $this->getApi($endpoint);
    }

    private function postApi(string $endpoint, array $data = []): ?array
    {
        try {
            $response = (new Client())->request(
                'POST',
                $endpoint,
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                    ],
                    'auth' => [
                        getenv('AI_JIRA_EMAIL'),
                        getenv('AI_JIRA_API_TOKEN')
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

    private function getApi(string $endpoint, array $data = []): ?array
    {
        try {
            $response = (new Client())->request(
                'GET',
                $endpoint,
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                    ],
                    'auth' => [
                        getenv('AI_JIRA_EMAIL'),
                        getenv('AI_JIRA_API_TOKEN')
                    ],
                    'query' => $data
                ]
            );
        } catch (\Exception $e) {
            echo "Error while fetching jira data \n" . $e->getMessage();
            exit;
        }

        return json_decode((string)$response->getBody(), true);
    }
}
