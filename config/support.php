<?php

return [
    
    'ticket' => [
        'table'  => 'tickets',
        'model'  => \App\Models\Ticket::class,
        'rules' => [
            'title'         => 'required|string',
            'department'    => 'required|in:financial,general,technical',
            'priority'      => 'required|in:low,medium,high',
            'status'        => 'nullable|in:waiting,pending,answered,closed,customerResponse,adminCreated',
            'satisfaction'  => 'nullable|in:happy,unhappy',
            'user_id'       => 'required_if:created_by,admin',
            'text'          => 'required|string',
            'files'          => 'nullable|array',
            'opts'          => 'nullable'
        ],

        'fields' => [
            'title'      => 'title',
            'department' => 'department',
            'priority'   => 'priority',
            'ip'         => 'ip',
            'user_id'    => 'user_id',
            'opts'       => 'opts',
            'status'     => 'status'
        ],
       
        'reply' => [
            'table'  => 'ticket_replies',
            'model' => \App\Models\TicketMessage::class,
            'rules' => [
                'text'   => 'required|string',
                'files'  => 'nullable|array',
            ],

            'fields' => [
                'text'       => 'text',
                'parent_id'  => 'parent_id',
                'files'      => 'files',
                'created_by' => 'created_by'
            ],
        ],

        'attachment_path' => "public/tickets",

        'relations_foreign_key' => [
            'user'   => 'user_id',
            'ticket' => 'ticket_id',
        ],
        'token_name' => 'admin'
    ],
    'user_model' => \App\Models\User::class,
];
