<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Правила валидации комментария.
 *
 * Одни и те же правила применяются при создании и обновлении,
 * в публичной части и в админке.
 */
class CommentRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'author' => 'required|string|max:255',
            'content' => 'required|string|max:1000',
            'post_id' => 'required|exists:posts,id',
        ];
    }
}
