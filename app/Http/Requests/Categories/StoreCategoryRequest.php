<?php

namespace App\Http\Requests\Categories;

use App\Actions\Accounts\ResolveCurrentAccount;
use App\Enums\TransactionType;
use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Category::class) ?? false;
    }

    public function rules(): array
    {
        $account = app(ResolveCurrentAccount::class)->handle($this->user());

        return [
            'name' => ['required', 'string', 'max:50',
                Rule::unique('categories', 'name')
                    ->where('account_id', $account->getKey())
                    ->where('type', $this->input('type')),
            ],
            'type' => ['required', Rule::enum(TransactionType::class)],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
        ]);
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Введите название категории',
            'name.max' => 'Название не должно превышать 50 символов',
            'name.unique' => 'Такая категория уже существует',
            'type.required' => 'Выберите тип категории',
            'type.enum' => 'Выбран неверный тип категории',
        ];
    }
}
