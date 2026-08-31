<?php

return [
    /**
     * Jira config for infrastructure's team.
     */
    'infrastructure' => [
        'url' => 'https://lidahbuayagroup-infrastructure.atlassian.net',
        'project_id' => "10087",
        'request_issue_type' => "10090",
        'request_corrective_field' => 'customfield_10074',
        'complaint_issue_type' => "10091",
        'complaint_corrective_field' => 'customfield_10073',
        'reporter_id' => '557058:f58131cb-b67d-43c7-b30d-6b58d40bd077',
    ],

    /**
     * Jira config for application's team.
     */
    'application' => [
        'url' => 'https://lidahbuayagroup.atlassian.net',
        'project_id' => "10044",
        'request_issue_type' => "10075",
        'request_corrective_field' => 'customfield_10044',
        'complaint_issue_type' => "10076",
        'complaint_corrective_field' => 'customfield_10043',
        'reporter_id' => '557058:f58131cb-b67d-43c7-b30d-6b58d40bd077'
    ],

    /**
     * Jira config for ax / system analyst team.
     * Similar with application's team config.
     */
    'ax' => [
        'url' => 'https://lidahbuayagroup.atlassian.net',
        'project_id' => "10044",
        'request_issue_type' => "10075",
        'request_corrective_field' => 'customfield_10044',
        'complaint_issue_type' => "10076",
        'complaint_corrective_field' => 'customfield_10043',
        'reporter_id' => '557058:f58131cb-b67d-43c7-b30d-6b58d40bd077'
    ],

    /**
     * Authentication token with 'Basic' header.
     */
    'auth_token' => 'b3NjYXIud29uZ3NvQGxpZGFoYnVheWFncm91cC5jb206TUxYbFdETTczNmlVY1NhWlhZcGsyQTYz'
];
