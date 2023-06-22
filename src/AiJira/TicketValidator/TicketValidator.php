<?php

declare(strict_types=1);

namespace AiJira\TicketValidator;

use AiJira\Jira\JiraClient;
use AiJira\Jira\JiraFormatter;
use AiJira\OpenAI\OpenAIClient;
use Jfcherng\Diff\Differ;
use Jfcherng\Diff\Factory\RendererFactory;

class TicketValidator
{
    public function __construct()
    {
        $this->jiraClient = new JiraClient();
        $this->jiraFormatter = new JiraFormatter();
        $this->openaiClient = new OpenAIClient();
        $this->mapper = new Mapper();
    }

    public function validateTicketDescription(string $ticketNumber): string
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
        $ticketFields = $this->mapper->mapTicketTypeFields($formattedTicket['type']);

        $updatedFormattedTicket = $this->openaiClient->getGeneratedTicketDescription($formattedTicket, $ticketFields);

        return $this->compare(
            json_encode($formattedTicket, JSON_PRETTY_PRINT),
            json_encode(json_decode($updatedFormattedTicket), JSON_PRETTY_PRINT)
        );
    }

    private function compare(string $old, string $new): string
    {
        $rendererName = 'Unified';

        $differOptions = [
            'context' => 3,
            'ignoreCase' => false,
            'ignoreLineEnding' => false,
            'ignoreWhitespace' => false,
            'lengthLimit' => 2000,
        ];

        $rendererOptions = [
            'detailLevel' => 'word',
            'language' => 'eng',
            'lineNumbers' => false,
            'separateBlock' => true,
            'wrapper-class' => ['diff-side-by-side']
        ];

        $differ = new Differ(explode("\n", $old), explode("\n", $new), $differOptions);
        $renderer = RendererFactory::make($rendererName, $rendererOptions);

        return $renderer->render($differ);
    }
}
