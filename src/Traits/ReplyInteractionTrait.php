<?php

namespace soheilfarzaneh\Ticket\Traits;

trait ReplyInteractionTrait
{

    public function tickets() {
        return $this->belongsTo(config('support.ticket.model' , \App\Models\Ticket::class));
    }

    public function users() {
        return $this->belongsTo(config('support.model.user' , \App\Models\User::class) ,
                                 config('support.ticket.relations_foreign_key.user' , 'user_id'), 'id');
    }
}