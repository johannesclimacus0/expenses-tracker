<?php

namespace App\Http\Requests\Transactions;

use App\Models\Transaction;
use Illuminate\Foundation\Http\FormRequest;

class ImportTransactionsCsvRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Transaction::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'csv' => 'required|file|mimes:csv,txt|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            'csv.required' => 'Выберите CSV-файл',
            'csv.file' => 'Не удалось прочитать загруженный файл',
            'csv.mimes' => 'Можно загрузить только CSV-файл',
            'csv.max' => 'CSV-файл не должен быть больше 5 МБ',
        ];
    }
}
