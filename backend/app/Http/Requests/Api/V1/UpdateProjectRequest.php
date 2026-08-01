<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateProjectRequest extends FormRequest
{
    /**
     * Authorization is delegated to ProjectPolicy::update() so the specific
     * denial reason reaches the client instead of a generic 403.
     */
    public function authorize(): \Illuminate\Auth\Access\Response
    {
        return app(\App\Policies\ProjectPolicy::class)
            ->update($this->user(), $this->route('project'));
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|required|string|max:255',
            'document_category_id' => 'sometimes|required|integer|exists:document_categories,id',
            'description' => 'sometimes|nullable|string|max:5000',
            'priority' => 'sometimes|nullable|in:low,normal,high',
            'notes' => 'sometimes|nullable|string|max:2000',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'string' => ':attribute harus berupa teks.',
            'max' => ':attribute maksimal :max karakter.',
            'integer' => ':attribute harus berupa angka.',
            'exists' => ':attribute tidak valid atau tidak ditemukan.',
            'in' => ':attribute hanya boleh berisi salah satu dari nilai yang diizinkan.',
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
