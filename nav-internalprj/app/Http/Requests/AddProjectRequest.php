<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\BaseFormRequest;

class AddProjectRequest extends BaseFormRequest
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
     * @return array
     */
    public function rules()
    {
        return [
            'id' => 'required|unique:project',
            'name' => 'required',
            'start_date' => 'required',
            'end_date' => 'required|after_or_equal:start_date',
            'total_effort' => 'required',
            'id_client' => 'required',
            'id_pm' => 'required',
            'proj_member_type_b' => 'required',
            'proj_member_type_s' => '',
            'proj_member_type_n' => '',
            'status' => 'required'
        ];
    }
}
