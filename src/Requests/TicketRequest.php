<?php

namespace soheilfarzaneh\Ticket\Requests;

use soheilfarzaneh\Ticket\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use soheilfarzaneh\Ticket\Exceptions\TicketFaildValidationException;

class TicketRequest extends BaseRequest
{
    public function authorize(){
        return true;
    }

    public function rules(){
        return config('support.ticket.rules');
    }

}