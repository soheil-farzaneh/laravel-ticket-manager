<?php

namespace soheilfarzaneh\Ticket\Requests;

use soheilfarzaneh\Ticket\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;

class MessageRequest extends BaseRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return config('support.ticket.reply.rules');
    }

}