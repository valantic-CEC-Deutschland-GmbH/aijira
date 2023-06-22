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
        $mergeRequests = $this->gitLabClient->getMergeRequestsForJiraSprint($jiraSprint);

        return $this->openaiClient->getGeneratedReleaseNotes($mergeRequests, $jiraSprint);
    }
}
