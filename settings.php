<?php

return [

    'email' => [

        /**
         * The recipient email addresses for the notification emails
         */
        'recipients' => [
            'recipient one',
            'recipient two'
        ]
    ],

    /**
     * Add system log paths here
     *
     * Syntax:
     *
     * [logs][i][path] = path to log file on filesystem
     * [logs][i][filterLineBy] = an optional string search pattern
     *
     * This example will filter each line that begins with todays date
     * in format Month {integral day without leading 0}
     */

    'logs' => [
        [
            'path' => '/path/to/log/file',
            'filterLineBy' => '/' . date("M") . '\040+' . date("j") . '/'
        ]
    ]
];