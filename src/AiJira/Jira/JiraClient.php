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

        return $this->postApi($endpoint, $data);
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
            $sprintsResponse = $this->getSprints($board['id']);
            $sprints = json_decode($sprintsResponse, true);

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
        $endpoint = getenv('AI_JIRA_URL') . '/rest/agile/1.0/board';
        $data = [
            'projectKeyOrId' => getenv('AI_JIRA_PROJECT')
        ];

        return $this->getApi($endpoint, $data);
    }

    private function getSprints($boardId): string
    {
        $endpoint = getenv('AI_JIRA_URL') . '/rest/agile/1.0/board/' . $boardId . '/sprint';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, getenv('AI_JIRA_EMAIL') . ':' . getenv('AI_JIRA_API_TOKEN'));
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
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
