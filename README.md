# aijira
Automization for regular project related tasks - no developer likes.

![2023-06-22_0.png](assets/2023-06-22_0.png)

## Usage
```bash
> aijira <COMMAND>
```

## Setup
 - install PHP
 - `composer global require valantic-cec/aijira`
 - add environment variables mentioned below

## Configuration
The following env parameters need to be configured, depending on the CLI commands you like to use (just configure them all):

- Retrieve your [OpenAI API Key](https://platform.openai.com/account/api-keys)
- Retrieve your [Jira API Key](https://id.atlassian.com/manage-profile/security/api-tokens)
- Retrieve your [Gitlab Access Token](https://docs.gitlab.com/ee/user/profile/personal_access_tokens.html)

| Scope        |                                                                                                     Variable                                                                                                      |
| ------------- |:-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------:|
| global      |                                                                                                    OPENAI_KEY                                                                                                     |
| aisprintgoals<br/>aiticketvalidator<br/>aiticketestimate<br/>aiticketinterview      |   AI_JIRA_EMAIL (i.e. schoenfeld@nexus-netsoft.com)<br/>AI_JIRA_API_TOKEN (i.e. ATATT3xF...)<br/>AI_JIRA_URL (i.e. https://nexus-netsoft.atlassian.net/)<br/>AI_JIRA_PROJECT (i.e. SPRY)<br/>AI_JIRA_BOARD_ID (i.e. 10)|
| aisprintreview<br/>aireleasenotes |AI_GITLAB_URL (selfhosted i.e. https://gitlab.nxs360.com/, SaaS https://gitlab.example.com/)<br/>AI_GITLAB_TOKEN (i.e. 9s6h...)<br/>AI_GITLAB_PROJECT_IDS (i.e. 476,735)|

## Examples

### Use OpenAI to generate your sprint goals for a planned or existing sprint.
```bash
> aijira sprint:goals "<SPRINT-NAME>"
> aijira ticket:sprintgoals "<Ticket1>,<Ticket2>,<Ticket3>"
```

![2023-06-21_1.png](assets/2023-06-21_1.png)
![2023-06-21_5.png](assets%2F2023-06-21_5.png)

### Use OpenAI to generate a sprint review agenda.
```bash
> aijira sprint:review "<SPRINT-NAME>"
```

![2023-06-21_2.png](assets/2023-06-21_2.png)

### Use OpenAI to ask the right questions. (Works best with a Story)
```bash
> aijira ticket:ask "<TICKET-NR>"
```

![2023-06-21_3.png](assets/2023-06-21_3.png)

### Use OpenAI to get an estimation in hours for a specific task.
```bash
> aijira ticket:estimate "<TICKET-NR>"
```

![2023-06-21_4.png](assets/2023-06-21_4.png)

### Use OpenAI to score a given ticket for its completeness & quality on a scale between 1 and 10.
```bash
> aijira ticket:score "<TICKET-NR>"
```

![screenshot-ticket-score.png](assets/screenshot-ticket-score.png)

### Use OpenAI to improve ticket quality.
```bash
> aijira ticket:validate "<TICKET-NR>"
```

![screenshot-ticket-validate.png](assets/screenshot-ticket-validate.png)

### Use OpenAI to generate test-cases
```bash
> aijira ticket:test-cases "<TICKET-NR>"
```

![screenshot-ticket-test-cases.png](assets/screenshot-ticket-test-cases.png)

### Use OpenAI to generate your release notes to a given sprint. Its automatically collecting all merge requests.
```bash
> aijira sprint:release-notes "<SPRINT-NAME>"
```

![2023-06-21_20-59.png](assets/2023-06-21_20-59.png)

### Use OpenAI to generate testcases that allow manual check if ticket requirements are implemented correctly.
```bash
> aijira ticket:test-cases "<SPRINT-NAME>"
```

![2023-06-22_11.png](assets/2023-06-22_11.png)

## ToDo
None, code is perfect ;)
