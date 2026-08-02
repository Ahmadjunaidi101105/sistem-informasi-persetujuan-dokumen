<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ReviseRejectRequest extends FormRequest
{
    /**
     * Delegated to ProjectPolicy::review() so the caller is told *why* the
     * action was refused instead of receiving a generic 403.
     */
    public function authorize(): \Illuminate\Auth\Access\Response
    {
        return app(\App\Policies\ProjectPolicy::class)
            ->review($this->user(), $this->route('project'));
    }

    public function rules(): array
    {
        return [
            'notes' => 'required|string|min:10|max:2000',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi untuk tindakan ini.',
            'string' => ':attribute harus berupa teks.',
            'min' => ':attribute minimal :min karakter.',
            'max' => ':attribute maksimal :max karakter.',
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
