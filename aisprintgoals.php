<?php

function callApi(string $endpoint, array $headers, array $data): ?array
{
    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}

function getGeneratedSprintGoals(string $ticketData, bool $splitByLabels = true): string
{
    $openAiKey = getenv('OPENAI_KEY');
    $endpoint = 'https://api.openai.com/v1/chat/completions';

    $headers = [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $openAiKey,
    ];

    $goalsPerCategory = 3;

    $prompt = sprintf(
        'Generate one-sentence sprint goals based on the provided Jira ticket data%s. The sprint goal should shortly describe what will be done in this sprint. Do not just list the ticket titles but describe the most important tasks for this sprint. Generate a maximum of %s goals%s. Return only the sprint goals without comments or other text.',
        $splitByLabels ? ', splitting the goals into different categories, based on the ticket "labels" value' : '',
        $goalsPerCategory,
        $splitByLabels ? ' for each category' : ''
    );

    $data = [
        'temperature' => 0,
        'model' => 'gpt-3.5-turbo',
        'messages' => [
            [
                'role' => 'system',
                'content' => $prompt,
            ],
            [
                'role' => 'user',
                'content' => $ticketData,
            ],
        ],
        'max_tokens' => 250,
    ];

    $responseData = callApi($endpoint, $headers, $data);
    $message = $responseData['choices'][0]['message']['content'];

    return str_replace(['"', "\n"], '', $message);
}

function getTicketData(): array
{
    $email = getenv('AI_JIRA_EMAIL');
    $jiraApiToken = getenv('AI_JIRA_API_TOKEN');
    $endpoint = sprintf(
        '%s%s',
        getenv('AI_JIRA_URL'),
        '/rest/api/3/search'
    );

    $headers = [
        'Content-Type: application/json',
        'Authorization: Basic ' . base64_encode($email . ':' . $jiraApiToken),
    ];

    $data = [
        'jql' => getenv('AI_JIRA_JQL'),
        'fields' => [
            'description',
            'labels',
            'summary',
            'issuetype',
        ],
    ];

    return callApi($endpoint, $headers, $data);
}

function formatDescription(array $descriptionParts): string
{
    $description = '';

    foreach ($descriptionParts as $content) {
        if ($content['type'] === null || $content['type'] !== 'paragraph') {
            continue;
        }

        foreach ($content['content'] as $text) {
            if ($text['type'] === 'text') {
                $description .= $text['text'];
            }
        }
    }

    return $description;
}

function formatTicketData(array $ticket): array
{
    $description = '';
    if (isset($ticket['fields']['description']['content'])) {
        $description = formatDescription($ticket['fields']['description']['content']);
    }

    $labels = implode(',', $ticket['fields']['labels']);

    return [
        'title' => $ticket['fields']['summary'],
        'description' => $description,
        'labels' => $labels,
    ];
}

function splitTicketsByType(array $ticketData): array
{
    $tasks = [];
    $stories = [];
    foreach ($ticketData['issues'] as $ticket) {
        $issueType = $ticket['fields']['issuetype']['name'];
        $formattedTicket = formatTicketData($ticket);
        if ($issueType === 'Story') {
            $stories[] = $formattedTicket;
        } else {
            $tasks[] = $formattedTicket;
        }
    }

    return [$tasks, $stories];
}

function generateSprintGoals()
{
    $ticketData = getTicketData();
    [$tasks, $stories] = splitTicketsByType($ticketData);

    echo getGeneratedSprintGoals(json_encode($tasks)) . getGeneratedSprintGoals(json_encode($stories), false);
}

generateSprintGoals();
