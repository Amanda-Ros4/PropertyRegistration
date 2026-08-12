<?php

namespace App\Http\Requests\Users;

use App\Enums\ActiveStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateUserActiveRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->route('user');

        return $user !== null && ($this->user()?->can('update', $user) ?? false);
    }

    public function rules(): array
    {
        return [
            'active' => ['required', new Enum(ActiveStatus::class)],
        ];
    }

    public function attributes(): array
    {
        return [
            'active' => __('users.fields.active'),
        ];
    }
}
