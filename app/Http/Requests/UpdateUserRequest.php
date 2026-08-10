<?php

namespace App\Http\Requests;

use App\Http\Requests\BaseRequest;

class UpdateUserRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user')->id ?? $this->route('user');

        return [
            'name'      => 'sometimes|required|string|max:255',
            'email'     => 'sometimes|required|string|email|max:255|unique:users,email,' . $userId,
            'role_id'   => 'sometimes|required|integer|exists:roles,id',
            'password'  => 'sometimes|nullable|string|min:6',
        ];
    }
}