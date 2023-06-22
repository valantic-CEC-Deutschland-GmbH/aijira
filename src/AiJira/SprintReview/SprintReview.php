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
        $this->mapper = new Mapper();
    }

    public function getSprintReviewFromMergeRequestsAndTickets(string $paramSprintName): string
    {
        $ticketData = $this->jiraClient->getTasksBySprintName($paramSprintName);
        $tasks = $this->mapper->mapTaskFields($ticketData);

        $jiraSprint = $this->jiraClient->getSprint($paramSprintName);
        $mergeRequests = $this->gitLabClient->getMergeRequestsForJiraSprint($jiraSprint);

        return $this->openaiClient->getGeneratedSprintReviewFromMergeRequestsAndTickets($mergeRequests, $tasks, $paramSprintName);
    }
}
