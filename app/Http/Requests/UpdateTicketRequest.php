<?php

namespace App\Http\Requests;

use App\Enums\TicketPriority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'sometimes',
                'required',
                'string',
                'max:5000',
            ],

            'priority' => [
                'sometimes',
                'required',
                Rule::enum(TicketPriority::class),
            ],

            'due_date' => [
                'sometimes',
                'nullable',
                'date',
                'after_or_equal:today',
            ],
        ];
    }
}