<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateApprovalChainRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'project_id' => 'required|exists:projects,id|unique:approval_chains,project_id', // تحقق من أن project_id فريد وموجود
        ];
    }
    public function messages()
    {
        return [
            'project_id.required' => 'The project ID is required.',
            'project_id.exists'   => 'The selected project does not exist.',
            'project_id.unique'   => 'The project already has an approval chain.',
        ];
    }
}
