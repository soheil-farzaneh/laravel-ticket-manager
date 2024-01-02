<?php

namespace soheilfarzaneh\Ticket\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class BaseRequest extends FormRequest {
    protected function failedValidation(Validator $validator) {
        $response = [
            'status'  => 'error',
            'message' => $validator->errors()->first(),
            'data'    => $validator->errors()
        ];
        throw new HttpResponseException(response()->json($response, 422));
    }
}