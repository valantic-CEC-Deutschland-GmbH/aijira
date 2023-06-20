# aijira

Generates sprint goals using OpenAI based on provided Jira tickets.

## Usage
```bash
> aisprintgoals <SPRINT-NAME>
> aisprintreview <SPRINT-NAME>
```

## Setup
```
composer global config repositories.gitlab.nxs360.com/460 '{"type": "composer", "url": "https://gitlab.nxs360.com/api/v4/group/460/-/packages/composer/packages.json"}'
composer global require valantic/aijira
```

- Retrieve your [OpenAI API Key](https://platform.openai.com/account/api-keys)
- Retrieve your [Jira API Key](https://id.atlassian.com/manage-profile/security/api-tokens)
- Retrieve your [Gitlab Access Token](https://gitlab.nxs360.com/-/profile/personal_access_tokens)

## Configuration
The following env parameters need to be configured:

### General
- OPENAI_KEY
- AI_JIRA_EMAIL (i.e. schoenfeld@nexus-netsoft.com)
- AI_JIRA_API_TOKEN (i.e. ATATT3xF...)
- AI_JIRA_URL (i.e. https://lr4digital.atlassian.net/)
- AI_JIRA_PROJECT (i.e. SPRY)
- AI_JIRA_BOARD_ID (i.e. 10)
- AI_GITLAB_URL (i.e. https://gitlab.nxs360.com/)
- AI_GITLAB_TOKEN
- AI_GITLAB_PROJECT_IDS (i.e. 476,735)

## Others
### OpenAI
- Model: gpt-3.5-turbo
- Max tokens: 250
- Sprint goals per category: 3

Ticket prompt:
```
Generate one-sentence sprint goals based on the provided Jira ticket data, splitting the goals into different categories, based on the ticket "labels" value. The sprint goal should shortly describe what will be done in this sprint. Do not just list the ticket titles but describe the most important tasks for this sprint. Generate a maximum of 3 goals for each category. Return only the sprint goals without comments or other text.
```

Story prompt:
```
Generate one-sentence sprint goals based on the provided Jira ticket data. The sprint goal should shortly describe what will be done in this sprint. Do not just list the ticket titles but describe the most important tasks for this sprint. Generate a maximum of 3 goals. Return only the sprint goals without comments or other text.
```

## ToDo
- `aijira-ticket-validate "Ticket No"` Ticket Quality Checker "Ticket NR"
- `aijira-ticket-estimate "Ticket No"` Automatische Estimations (Only Tasks)
- `aijira-sprint-review-generate "Sprint Name"` Sprint Review Ticket List generator "DateRange"
- `aijira-ticket-interview "Ticket No"` Ticket/Story Interview Questions to productowner (`Liste die Fragen für das Benutzerinterview für das folgende Feature auf: [Feature beschreiben]`)
- `aijira-ticket-acceptance-criteria "Ticket No"` Auto ACs
- `aijira-ticket-test-cases "Ticket No"` Testcase generator "Ticket NR"
- `aijira-gitlab-release-notes UNKNOWN PARAMETER` Auto release notes gitlab > CHANGELOG-2023-06-15.md
- perfect prompt gpt4 plugin -> improve our prompts
