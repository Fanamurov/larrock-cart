<?php

namespace Larrock\ComponentCart\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderShortRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
		return [
			'name' => 'required|max:255',
			'contact' => 'required',
			//'g-recaptcha-response' => 'required|recaptcha',
		];
    }
}
