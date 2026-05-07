<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InformationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $creating = $this->isMethod('POST');

        return [
            'title' => $creating ? 'required|string' : 'sometimes|string',
            'content' => $creating ? 'required|string' : 'sometimes|string',
            'is_active' => 'sometimes|boolean',
        ];
    }
}
