<?php

namespace soheilfarzaneh\Ticket\Traits;

use soheilfarzaneh\Ticket\Exceptions\TicketNotFoundException;
use soheilfarzaneh\Ticket\Exceptions\MessageNotFoundException;

trait TicketInteractionTrait
{

    public function replies() {

        return $this->hasMany(
                config('Handy.ticket.reply.model' , \App\Models\TicketMessage::class), 
                config('Handy.ticket.relations_foreign_key.ticket' , 'ticket_id'),
                'id'
            );
    }

    public function users() {

        return $this->belongsTo(
                config('Handy.user_model' , \App\Models\User::class) ,     
                config('Handy.ticket.relations_foreign_key.user' , 'user_id'),
                'id'
            );
    }

    public function registerTicket(array $ticketData) {

        return $this->create($ticketData);
    }

    public function registerReply(array $replyData) {
        
        return $this->replies()->create($replyData);
    }

    public function getTicketById(int $id) {

        $ticketModel = $this->withTrashed()->find($id);
        if (!$ticketModel) throw new TicketNotFoundException();
        return $ticketModel;
    }

    public function getReplyById(int $id) {
        
        $replyModel = $this->replies()->withTrashed()->find($id);
        if (!$replyModel) throw new MessageNotFoundException();
        return $replyModel;
    }

    public function updateTicketFeilds(array $ticketData) {

        return $this->update($ticketData);
    }

    public function updateReplyFeilds($replyModel , array $replyData) {

        $replyModel->update($replyData);
        return $replyModel;
    }

    public function scopeAllTickets($query) {

        return $query->withTrashed()->latest();
    }

    public function scopeUserTickets($query , $userId) {

        return  $query->where(
                config('Handy.ticket.relations_foreign_key.user'),
                $userId
            );
    }

    public function getPaginate($query , $perPage = null) {
        
        return $query->paginate($perPage);
    }
}