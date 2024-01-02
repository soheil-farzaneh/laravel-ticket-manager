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
            'local_id'      => 'required|int',
            'user_id'       => 'required_if:created_by,admin',
            'text'          => 'required_without:file|string',
            'file'          => 'required_without:text|mimes:jpeg,jpg,png,gif,txt,pdf,doc|max:2048',
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
            'model'  => \App\Models\TicketReply::class,
            'rules' => [
                'text'          => 'nullable|string',
                'file'          => 'nullable|mimes:jpeg,jpg,png,gif,txt,pdf,doc|max:2048',
                'parent_id'     => 'nullable',
                'local_id'      => 'required|int',
                'created_by'    => 'nullable|in:user,admin'
            ],

            'fields' => [
                'text'       => 'text',
                'parent_id'  => 'parent_id',
                'file'       => 'file',
                'local_id'   => 'local_id',
                'created_by' => 'created_by'
            ],
        ],

        'attachment_path' => "public/tickets",

        'relations_foreign_key' => [
            'user'   => 'user_id',
            'ticket' => 'ticket_id',
        ]
    ],
    'token_name' => 'admin'
    'user_model' => \App\Models\User::class
];
