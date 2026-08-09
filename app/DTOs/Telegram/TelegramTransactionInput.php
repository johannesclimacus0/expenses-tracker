<?php

declare(strict_types=1);

namespace App\DTOs\Telegram;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final readonly class TelegramTransactionInput
{
    public function __construct(
        public string $amount,
        public string $category,
        public ?string $description,
    ) {}

    public static function fromString(string $input): self
    {
        $input = trim($input);

        if (!preg_match('/^(\d+(?:[.,]\d{1,2})?)\s+([^|]+)(?:\|(.+))?$/u', $input, $matches)) {
            throw ValidationException::withMessages([
                'command' => 'Формат: сумма Категория | описание',
            ]);
        }

        $data = [
            'amount' => str_replace(',', '.', $matches[1]),
            'category' => trim($matches[2]),
            'description' => isset($matches[3]) ? trim($matches[3]) : null,
        ];

        if ($data['description'] === '') {
            $data['description'] = null;
        }

        $validated = Validator::make(
            $data,
            [
                'amount' => 'required|numeric|decimal:0,2|min:0.01|max:9999999999.99',
                'category' => 'required|string|max:50',
                'description' => 'nullable|string|max:255',
            ],
            [
                'amount.*' => 'Укажите корректную сумму от 0,01 до 9 999 999 999,99',
                'category.*' => 'Укажите название категории длиной до 50 символов',
                'description.max' => 'Описание не должно превышать 255 символов',
            ]
        )->validate();

        return new self(
            amount: $validated['amount'],
            category: $validated['category'],
            description: $validated['description'] ?? null,
        );
    }
}
