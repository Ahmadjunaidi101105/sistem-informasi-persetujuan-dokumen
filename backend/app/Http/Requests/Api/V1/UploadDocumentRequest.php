<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Models\Project;

class UploadDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $project = $this->route('project');
        
        if (!$this->user() || !$project) {
            return false;
        }

        $isOwner = $project->user_id === $this->user()->id;
        $isEditableStatus = in_array($project->status, ['draft', 'revised']);

        return $isOwner && $isEditableStatus;
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
            'errors' => $validator->errors()
        ], 422));
    }
}
