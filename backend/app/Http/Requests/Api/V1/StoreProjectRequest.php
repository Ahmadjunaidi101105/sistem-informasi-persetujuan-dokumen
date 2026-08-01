<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole('pemohon');
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'document_category_id' => 'required|integer|exists:document_categories,id',
            'description' => 'nullable|string|max:5000',
            'priority' => 'nullable|in:low,normal,high',
            'notes' => 'nullable|string|max:2000',
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
