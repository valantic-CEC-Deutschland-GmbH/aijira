<?php

declare(strict_types=1);

namespace AiJira\TicketTestCaseCreator;

use AiJira\Jira\JiraClient;
use AiJira\Jira\JiraFormatter;
use AiJira\OpenAI\OpenAIClient;

class TicketTestCaseCreator
{
    public function __construct()
    {
        $this->jiraClient = new JiraClient();
        $this->jiraFormatter = new JiraFormatter();
        $this->openaiClient = new OpenAIClient();
    }

    public function createTicketTestCases(string $ticketNumber): string
    {
        $ticketData = $this->jiraClient->getTicketByKey($ticketNumber);
        $fields = $this->jiraClient->getFields();
        $fieldIdToLabelMapping = array_combine(
            array_column($fields, 'id'),
            array_column($fields, 'name')
        );
        $formattedTicket = $this->jiraFormatter->formatTicketData($ticketData);
        foreach ($formattedTicket['fields'] as $key => $value) {
            if (!array_key_exists($key, $fieldIdToLabelMapping)) {
                continue;
            }
            $formattedTicket['fields'][$fieldIdToLabelMapping[$key]] = $value;
            unset($formattedTicket['fields'][$key]);
        }

        $testCases = $this->openaiClient->getGeneratedTestCases($formattedTicket);

        return json_encode(json_decode($testCases), JSON_PRETTY_PRINT) . PHP_EOL;
    }
}
