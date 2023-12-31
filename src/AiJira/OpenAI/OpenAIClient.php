<?php

declare(strict_types=1);

namespace AiJira\OpenAI;

use GuzzleHttp\Client;

class OpenAIClient
{
    public function getGeneratedSprintGoals(array $ticketData, array $labels = [], ?string $overwritePrompt): string
    {
        $endpoint = 'https://api.openai.com/v1/chat/completions';
        $goalsPerCategory = 3;
        $prompt = sprintf(
            'Generate one-sentence sprint goals based on the provided Jira ticket data%s. The sprint goal should shortly describe what will be done in this sprint. Do not just list the ticket titles but describe the most important tasks for this sprint. Generate a maximum of %s goals%s. Return only the sprint goals without comments or other text.',
            $labels ? ', splitting the goals into the categories "' . implode(', ', $labels) . '"' : '',
            $goalsPerCategory,
            $labels ? ' for each category' : ''
        );

        if ($overwritePrompt) {
            $prompt = $overwritePrompt;
        }

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
                    'content' => json_encode($ticketData),
                ],
            ],
            'max_tokens' => 250,
        ];

        return $this->callApi($endpoint, $data);
    }

    public function getGeneratedTicketDescription(array $ticketData, array $fields): string
    {
        $endpoint = 'https://api.openai.com/v1/chat/completions';
        $prompt = sprintf(
            'Given the field data of a Jira ticket, your task is to enhance the wording of the ticket and provide revised versions of the fields %s. Please exclude any comments or extraneous text from your response. When no information is available for a field its content should be equal to "N/A". Return your response by updating the given field data of the jira ticket.',
            '"' . implode(', ', $fields) . '"'
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
                    'content' => json_encode($ticketData),
                ],
            ],
        ];

        return $this->callApi($endpoint, $data);
    }

    public function getGeneratedTicketScoring(array $ticketData): string
    {
        $endpoint = 'https://api.openai.com/v1/chat/completions';
        $prompt = 'Given the field data of a Jira ticket, your task is to rate the completeness & quality of the ticket on a scale between 1 and 10. Also provide suggestions improvements to increase the rating. Return your response only as a JSON array where each entry contains the properties "type" ("completeness", "quality", "comprehensibility"), "rating" and "suggestionsForImprovements" where each array-entry contains a string. Don\'t provide any explanation.';

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
                    'content' => json_encode($ticketData),
                ],
            ],
        ];

        return $this->callApi($endpoint, $data);
    }

    public function getGeneratedTestCases(array $ticketData): string
    {
        $endpoint = 'https://api.openai.com/v1/chat/completions';
        $prompt = 'Given the field data of a Jira ticket, your task is to write the following test-cases. Test-cases of type "concrete" that are extract from the Jira field data or are based on the Jira field data. Test cases of type "suggestion" which are recommended for this kind of issue based on the content Jira fields but don\'t repeat concrete test-cases. Return your response as an JSON array where each entry should contain the properties description, acceptanceCriteria & type ("concrete" or "suggestion").';

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
                    'content' => json_encode($ticketData),
                ],
            ],
        ];

        return $this->callApi($endpoint, $data);
    }

    public function getGeneratedTicketInterviewQuestions(array $ticket): string
    {
        $endpoint = 'https://api.openai.com/v1/chat/completions';
        $prompt = 'I will send you a ticket with all its details in JSON Format. 
    Please define questions that will improve ticket content quality.
    Only questions allowed, that are not already answered in provided ticket details.
    Please return a numbered list. Dont ask questions regarding ticket structure, just try understand the content of this ticket and ask your questions: ' . json_encode($ticket) . '
      ';

        $data = [
            'n' => 1,
            'temperature' => 0,
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $prompt,
                ],
            ],
        ];

        return $this->callApi($endpoint, $data);
    }

    public function getGeneratedTicketTestcases(array $ticket): string
    {
        $endpoint = 'https://api.openai.com/v1/chat/completions';
        $prompt = 'Given the field data of a Jira ticket, your task is to write the following test-cases. Test-cases of type "concrete" that are extract from the Jira field data or are based on the Jira field data. Test cases of type "suggestion" which are recommended for this kind of issue based on the content Jira fields but don\'t repeat concrete test-cases. Return your response as an JSON array where each entry should contain the properties description, acceptanceCriteria & type. Jira Ticket: ' . json_encode($ticket) . '
      ';

        $data = [
            'n' => 1,
            'temperature' => 0,
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $prompt,
                ],
            ],
        ];

        return $this->callApi($endpoint, $data);
    }

    public function getGeneratedTicketEstimation(array $ticket, array $tickets): string
    {
        $endpoint = 'https://api.openai.com/v1/chat/completions';
        $prompt = 'As a developer, I would like to have an estimated time for the provided Jira ticket. Please estimate in hours. Dont come up with excuses or substantiation -> just estimate it. I will use this as a prediction and orientiation. Answer me without any suggestion or comments or other texts, only give me the estimation in format: ###-### hours. This is the JIRA Ticket that you estimate: ' . json_encode($ticket) . '. Here are some estimated tickets you can use as a reference: ' . json_encode($tickets);

        $data = [
            'n' => 1,
            'temperature' => 0,
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $prompt,
                ],
            ],
        ];

        $response = $this->callApi($endpoint, $data);

        return str_replace('.', '', $response);
    }

    public function getGeneratedSprintReviewFromMergeRequestsAndTickets(array $mergeRequests, array $tickets, string $sprintName): string
    {
        $endpoint = 'https://api.openai.com/v1/chat/completions';
        $prompt = 'Given a JSON-encoded list of git `merge_requests` and `tickets` create a smart agenda where tickets and topics are grouped by its merge request author. Each merge request author will the topics he worked on. You can filter these topics based on their estimated time and potential impact on the customer experience. If possible, please add the corresponding ticket numbers to each topic you create - add `no-task` if you find no ticket number. Please combine topics and keep a clean uniformed structure. The headline should contain our sprint name: "' . $sprintName . '". ';

        $data = [
            'temperature' => 0,
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $prompt,
                ],
                [
                    'role' => 'assistant',
                    'content' => json_encode([
                        'merge_requests' => $mergeRequests,
                        'tickets' => $tickets,
                    ]),
                ],
            ],
        ];

        $response = $this->callApi($endpoint, $data);

        return str_replace('"', '', $response);
    }

    public function getGeneratedReleaseNotes(array $mergeRequests, array $jiraSprint): string
    {
        $endpoint = 'https://api.openai.com/v1/chat/completions';
        $prompt = 'Given a JSON-encoded list of git `merge_requests` create some fancy release notes. Sprint duration: "' . (new \DateTimeImmutable($jiraSprint['startDate']))->format('Y-m-d') . ' - ' . (new \DateTimeImmutable($jiraSprint['endDate']))->format('Y-m-d') . '". ';

        $data = [
            'temperature' => 0,
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $prompt,
                ],
                [
                    'role' => 'assistant',
                    'content' => json_encode([
                        'merge_requests' => $mergeRequests,
                    ]),
                ],
            ],
        ];

        $response = $this->callApi($endpoint, $data);

        return str_replace('"', '', $response);
    }

    private function callApi(string $endpoint, array $data): string
    {
        $response = (new Client())->request(
            'POST',
            $endpoint,
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . getenv('OPENAI_KEY'),
                ],
                'body' => json_encode($data),
            ]
        );

        $responseData = json_decode((string)$response->getBody(), true);

        return $responseData['choices'][0]['message']['content'] ?? '';
    }
}
