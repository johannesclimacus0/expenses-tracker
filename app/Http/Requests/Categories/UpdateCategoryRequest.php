<?php

namespace App\Http\Requests\Categories;

use App\Actions\Accounts\ResolveCurrentAccount;
use App\Enums\TransactionType;
use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $category = $this->route('category');

        return $category instanceof Category
            && ($this->user()?->can('update', $category) ?? false);
    }

    public function rules(): array
    {
        $category = $this->route('category');
        $account = app(ResolveCurrentAccount::class)->handle($this->user());

        return [
            'name' => ['required', 'string', 'max:50',
                Rule::unique('categories', 'name')
                    ->where('account_id', $account->getKey())
                    ->where('type', $this->input('type'))
                    ->ignore($category),
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

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $category = $this->route('category');

                if (!$category instanceof Category
                    || $validator->errors()->has('type')
                    || $this->input('type') === $category->type->value) {
                    return;
                }

                if ($category->transactions()->exists() || $category->budgets()->exists()) {
                    $validator->errors()->add(
                        'type',
                        'Нельзя изменить тип категории, которая уже используется',
                    );
                }
            },
        ];
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
