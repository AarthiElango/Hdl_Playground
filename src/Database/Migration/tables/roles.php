<?php

 return [
            'id' => [
                "INT",
                "NOT NULL",
                "AUTO_INCREMENT",
                "PRIMARY KEY",
            ],
            'name' => [
                "VARCHAR(40)",
                "NOT NULL",
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