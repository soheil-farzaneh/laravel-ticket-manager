<?php

namespace soheilfarzaneh\Ticket\Contracts;

use Illuminate\Http\Request;

interface InterfaceTicketService
{
    public function createTicket($request);

    public function replyToTicket($request, $status);

    public function updateTicket(array $data);

    public function updateReply($request, int $replyId);

    public function loadTicketById(int $id);

    public function loadReplyById(int $id);

    public function loadAllTickets();

    public function uploader(UploadedFile $file, int $localId);
}
