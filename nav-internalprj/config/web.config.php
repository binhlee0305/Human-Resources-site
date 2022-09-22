<?php

return [
    // period time of resource usage chart
    "num_month" => 3,
    "day_week" => "Monday",

    // account is lock
    "lock_account" => 1,

    //Type of User
    "user" => [
        "pm" => 2,
        "dev" => 3,
        "admin" => 1,
        "lock" => 0
    ],

    "default_user" => [
        "status" => "Active",
        "id_level" => 1,
        "privillege" => 3,
        "id_type" => 1
    ],

    //Default value of Project
    "project" => [
        "default_real_cost" => 0,
        "default_status" => 'Pre-sale',
    ],
    
    //Type of Work On
    "works_on" => [
        "type" => [
            "billable" => "B",
            "support" => "S",
            "none" => "N"
        ]
        ],

    "manhour_to_manmonth" => 1/160,
    "manmonth_to_manhour" => 160
];