<?php

declare(strict_types=1);

namespace AiJira\GitLab;

use GuzzleHttp\Client;

class GitLabClient
{
    public function getMergeRequestsForJiraSprint(array $jiraSprint): array
    {
        $startDate = new \DateTimeImmutable($jiraSprint['startDate']);
        $endDate = new \DateTimeImmutable($jiraSprint['endDate']);

        $mergeRequests = [];
        foreach (explode(',', getenv('AI_GITLAB_PROJECT_IDS')) as $gitlabProjectID) {
            $mergeRequests = array_merge($mergeRequests, $this->getMergeRequestsByDateRange($gitlabProjectID, $startDate, $endDate));
        }

        return $mergeRequests;
    }

    private function getMergeRequestsByDateRange(string $gitlabProjectId, \DateTimeImmutable $startDate, \DateTimeImmutable $endDate): array
    {
        $url = sprintf(
            '%sapi/v4/projects/%s/merge_requests',
            getenv('AI_GITLAB_URL'),
            $gitlabProjectId
        );
        $data = [
            'updated_before' => $endDate->format('Y-m-d\T00:00:00\Z'),
            'updated_after' => $startDate->format('Y-m-d\T00:00:00\Z'),
            'state' => 'merged',
            'per_page' => 100,
            'not[labels]' => 'renovate',
        ];

        $mergeRequests = $this->getApi($url, $data);

        $filteredMergeRequests = [];
        foreach ($mergeRequests as $entry) {
            $author = $this->anonString($entry['author']['username']);
            $filteredMergeRequests[$author][] = ['author' => $author, 'title' => $entry['title']];
        }

        return $filteredMergeRequests;
    }

    private function anonString($string)
    {
        $length = strlen($string);

        for ($i = 1; $i < $length; $i += 2) {
            $string[$i] = '*';
        }

        return $string;
    }

    private function getApi(string $endpoint, array $data = []): ?array
    {
        try {
            $response = (new Client())->request(
                'GET',
                $endpoint,
                [
                    'headers' => [
                        'PRIVATE-TOKEN' => getenv('AI_GITLAB_TOKEN'),
                    ],
                    'query' => $data
                ]
            );
        } catch (\Exception $e) {
            echo "Error while fetching jira data \n" . $e->getMessage();
            exit;
        }

        return json_decode((string)$response->getBody(), true, 512, JSON_THROW_ON_ERROR);
    }
}
