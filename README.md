# aijira

Generates sprint goals using OpenAI based on provided Jira tickets.

## Usage
```bash
> aisprintgoals <SPRINT-NAME>
> aiticketvalidator <TICKET-KEY>
```

## Setup
```
composer global config repositories.gitlab.nxs360.com/460 '{"type": "composer", "url": "https://gitlab.nxs360.com/api/v4/group/460/-/packages/composer/packages.json"}'
composer global require valantic/aijira
```

- Retrieve your [OpenAI API Key](https://platform.openai.com/account/api-keys)
- Retrieve your [Jira API Key](https://id.atlassian.com/manage-profile/security/api-tokens)

## Configuration
The following env parameters need to be configured:
- OPENAI_KEY
- AI_JIRA_EMAIL
- AI_JIRA_API_TOKEN
- AI_JIRA_URL
- AI_JIRA_PROJECT

## Others
### OpenAI
- Model: gpt-3.5-turbo
- Max tokens: 250

### Prompts
Ticket sprint goals:
```
Generate one-sentence sprint goals based on the provided Jira ticket data, splitting the goals into the categories "<LABELS>". The sprint goal should shortly describe what will be done in this sprint. Do not just list the ticket titles but describe the most important tasks for this sprint. Generate a maximum of 3 goals for each category. Return only the sprint goals without comments or other text.
```
Story sprint goals:
```
Generate one-sentence sprint goals based on the provided Jira ticket data. The sprint goal should shortly describe what will be done in this sprint. Do not just list the ticket titles but describe the most important tasks for this sprint. Generate a maximum of 3 goals. Return only the sprint goals without comments or other text.
```
Ticket validation:
```
Given the title and description of a Jira ticket, your task is to enhance the wording of the ticket and provide revised versions of the title and description. Please exclude any comments or extraneous text from your response.
```

## ToDo
- Testcase generator "Ticket NR"
- Sprint Review Ticket List generator "DateRange"
- Ticket Quality Improver "Ticket NR"
