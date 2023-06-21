<?php

declare(strict_types=1);

namespace AiJira\SprintReview;

use AiJira\GitLab\GitLabClient;
use AiJira\Jira\JiraClient;
use AiJira\OpenAI\OpenAIClient;

class SprintReview
{
    public function __construct()
    {
        $this->jiraClient = new JiraClient();
        $this->openaiClient = new OpenAIClient();
        $this->gitLabClient = new GitLabClient();
    }

    public function getSprintReviewFromMergeRequestsAndTickets(string $paramSprintName): string
    {
        $ticketData = $this->jiraClient->getTicketsBySprintName($paramSprintName);
        $jiraSprint = $this->jiraClient->getSprint($paramSprintName);
        $startDate = new \DateTimeImmutable($jiraSprint['startDate']);
        $endDate = new \DateTimeImmutable($jiraSprint['endDate']);

        $mergeRequests = [];
        foreach (explode(',', getenv('AI_GITLAB_PROJECT_IDS')) as $gitlabProjectID) {
            $mergeRequests = array_merge($mergeRequests, $this->gitLabClient->getMergeRequestsByDateRange($gitlabProjectID, $startDate, $endDate));
        }

        $tasks = [];
        foreach ($ticketData['issues'] as $ticket) {
            if ($ticket['fields']['issuetype']['name'] !== 'Story') {
                $tasks[$ticket['key']] = ['title' => $ticket['fields']['summary'], 'estimation' => $ticket['fields']['timetracking']['originalEstimate']];
            }
        }

        return $this->openaiClient->getGeneratedSprintReviewFromMergeRequestsAndTickets($mergeRequests, $tasks, $paramSprintName);
    }
}
