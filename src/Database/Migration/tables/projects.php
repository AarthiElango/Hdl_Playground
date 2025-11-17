<?php

 return [
            'id' => [
                "INT",
                "NOT NULL",
                "AUTO_INCREMENT",
                "PRIMARY KEY",
            ],
            'slug'=>[
                'VARCHAR(10)',
                'NOT NULL',
            ],
            'user_id'=>[
                'INT',
                'NOT NULL',
            ],
            'template_id'=>[
                'INT',
                'NOT NULL'
            ],
            'tool_id'=>[
                'INT',
                'NOT NULL'
            ],
            'title' => [
                "VARCHAR(200)",
                "NOT NULL",
            ],
            'description' => [
                "Text",
                "NOT NULL",
            ],
             'files' => [
                "Text",
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