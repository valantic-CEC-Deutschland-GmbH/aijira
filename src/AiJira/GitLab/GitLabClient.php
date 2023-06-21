<?php

declare(strict_types=1);

namespace AiJira\GitLab;

use GuzzleHttp\Client;

class GitLabClient
{
    public function getMergeRequestsByDateRange(string $gitlabProjectId, \DateTimeImmutable $startDate, \DateTimeImmutable $endDate): array
    {
        $url = getenv('AI_GITLAB_URL') . 'api/v4/projects/' . $gitlabProjectId . '/merge_requests';

        $blacklist = [
//            'nxs_schoenfeld',
//            'dominik_baehr',
        ];

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
            $author = $entry['author']['username'];
            if (!in_array($author, $blacklist, true)) {
                $filteredMergeRequests[$author][] = ['author' => $author, 'title' => $entry['title']];
            }
        }

        return $filteredMergeRequests;
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
