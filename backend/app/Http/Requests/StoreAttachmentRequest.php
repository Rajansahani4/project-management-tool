<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttachmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'max:10240',
                'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,txt',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'file.max'   => 'The file must not exceed 10MB.',
            'file.mimes' => 'Allowed types: pdf, doc, docx, xls, xlsx, jpg, jpeg, png, txt.',
        ];
    }
}
