<?php

namespace App\Http\Requests\Transactions;

use App\Models\Transaction;
use Illuminate\Foundation\Http\FormRequest;

class DeleteTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $transaction = $this->route('transaction');

        return $transaction instanceof Transaction
            && ($this->user()?->can('delete', $transaction) ?? false);
    }

    public function rules(): array
    {
        return [];
    }
}
