<?php

 return [
            'id' => [
                "INT",
                "NOT NULL",
                "AUTO_INCREMENT",
                "PRIMARY KEY",
            ],
            'role_id' => [
                "INT(10)",
                "NOT NULL",
            ],
             'username' => [
                "VARCHAR(50)",
                "NOT NULL",
                'UNIQUE'
            ],
            'fullname' => [
                "VARCHAR(40)",
                "NOT NULL",
            ],
            'email' => [
                "VARCHAR(40)",
                "NOT NULL",
                "UNIQUE",
            ],
            'mobile' => [
                "VARCHAR(10)",
                "NOT NULL",
                "UNIQUE",
            ],
            'password' => [
                "TEXT",
                "NOT NULL",
            ],
            'blocked_at' => [
                "DATETIME",
                "NULL"
            ],
             'otp' => [
                "VARCHAR(6)",
                "NULL"
            ],
             'otp_created_at' => [
                "DATETIME",
                "NULL"
            ],
            'verified_at' => [
                "DATETIME",
                "NULL"
            ],
            'created_at' => [
                "TIMESTAMP",
                "DEFAULT CURRENT_TIMESTAMP"
            ],
            'updated_at' => [
                "TIMESTAMP",
                "DEFAULT CURRENT_TIMESTAMP",
                "ON UPDATE CURRENT_TIMESTAMP"
            ]
        ];
