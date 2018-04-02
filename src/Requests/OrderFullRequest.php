<?php

namespace Larrock\ComponentCart\Requests;

use LarrockCart;
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
        return LarrockCart::getValid();
    }
}
