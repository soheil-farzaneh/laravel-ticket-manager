<?php

namespace soheilfarzaneh\Ticket\Exceptions;

use Exception;
use Illuminate\Validation\ValidationException;

class TicketFaildValidationException extends ValidationException
{
    public function __construct($validator)
    {
        $message = $validator->getMessageBag()->toArray();
        parent::__construct($validator);
    }
}