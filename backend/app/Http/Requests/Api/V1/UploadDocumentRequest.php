<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UploadDocumentRequest extends FormRequest
{
    /**
     * Authorization is delegated to ProjectDocumentPolicy::store() so the specific
     * denial reason reaches the client instead of a generic 403.
     */
    public function authorize(): \Illuminate\Auth\Access\Response
    {
        return app(\App\Policies\ProjectDocumentPolicy::class)
            ->store($this->user(), $this->route('project'));
    }

    public function rules(): array
    {
        return [
            'document' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'file' => ':attribute harus berupa file.',
            'mimes' => ':attribute harus berupa file dengan tipe: :values.',
            'max' => 'Ukuran :attribute maksimal :max kilobyte.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validasi gagal.',
            'errors' => $validator->errors(),
        ], 422));
    }
}
