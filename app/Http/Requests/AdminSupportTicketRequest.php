<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminSupportTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdminUser() ?? false;
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', 'in:open,in_progress,resolved,closed'],
            'assigned_admin_id' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }
}
