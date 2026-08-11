<?php

namespace App\Http\Requests;

use App\Http\Requests\BaseRequest;

class UpdateUserStatusRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'is_active' => 'required|boolean',
        ];
    }
}