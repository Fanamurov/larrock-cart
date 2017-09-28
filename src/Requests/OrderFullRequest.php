<?php

namespace Larrock\ComponentCart\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderFullRequest extends FormRequest
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
            'email' => 'required_without:without_registry|email',
            'tel' => 'required',
            'address' => 'required',
            'fio' => 'required',
            'oferta' => 'accepted'
        ];
    }
}
