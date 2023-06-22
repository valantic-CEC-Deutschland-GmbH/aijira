<?php

declare(strict_types=1);

namespace AiJira\ReleaseNotes;

use AiJira\GitLab\GitLabClient;
use AiJira\Jira\JiraClient;
use AiJira\OpenAI\OpenAIClient;

class ReleaseNotes
{
    public function __construct()
    {
        $this->jiraClient = new JiraClient();
        $this->openaiClient = new OpenAIClient();
        $this->gitLabClient = new GitLabClient();
    }

    public function getReleaseNotesFromMergeRequests(string $paramSprintName): string
    {
        $jiraSprint = $this->jiraClient->getSprint($paramSprintName);
        $mergeRequests = $this->getMergeRequests($jiraSprint);

        return $this->openaiClient->getGeneratedSprintReviewFromMergeRequestsAndTickets($mergeRequests, $jiraSprint);
    }

    private function getMergeRequests(array $jiraSprint): array
    {
        $startDate = new \DateTimeImmutable($jiraSprint['startDate']);
        $endDate = new \DateTimeImmutable($jiraSprint['endDate']);

        $mergeRequests = [];
        foreach (explode(',', getenv('AI_GITLAB_PROJECT_IDS')) as $gitlabProjectID) {
            $mergeRequests = array_merge($mergeRequests, $this->gitLabClient->getMergeRequestsByDateRange($gitlabProjectID, $startDate, $endDate));
        }

        return $mergeRequests;
    }
}
