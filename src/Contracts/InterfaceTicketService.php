<?php

namespace soheilfarzaneh\Ticket\Contracts;

use Illuminate\Http\Request;

interface InterfaceTicketService {

    public function createTicket($request);
    public function replyToTicket($request ,$status);
    public function updateTicket(array $data);
    public function updateReply($request , $replyId);

}