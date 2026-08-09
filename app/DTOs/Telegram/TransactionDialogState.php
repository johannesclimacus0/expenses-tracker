<?php

declare(strict_types=1);

namespace App\DTOs\Telegram;

use App\Enums\TransactionDialogStep;
use App\Enums\TransactionType;

final readonly class TransactionDialogState
{
    public function __construct(
        public TransactionDialogStep $step,
        public ?TransactionType $type,
        public ?string $amount = null,
        public ?int $categoryId = null,
        public ?string $categoryName = null,
    ) {}

    public static function awaitingAmount(TransactionType $type): self
    {
        return new self(TransactionDialogStep::Amount, $type);
    }

    public static function fromArray(array $state): self
    {
        return new self(
            step: TransactionDialogStep::tryFrom((string) ($state['step'] ?? '')) ?? TransactionDialogStep::Invalid,
            type: TransactionType::tryFrom((string) ($state['type'] ?? '')),
            amount: is_string($state['amount'] ?? null) ? $state['amount'] : null,
            categoryId: is_int($state['category_id'] ?? null) ? $state['category_id'] : null,
            categoryName: is_string($state['category_name'] ?? null) ? $state['category_name'] : null,
        );
    }

    public function withAmount(string $amount): self
    {
        return new self(
            TransactionDialogStep::Category,
            $this->type,
            $amount,
        );
    }

    public function withCategory(int $categoryId, string $categoryName): self
    {
        return new self(
            TransactionDialogStep::Comment,
            $this->type,
            $this->amount,
            $categoryId,
            $categoryName,
        );
    }

    public function toArray(): array
    {
        return [
            'step' => $this->step->value,
            'type' => $this->type?->value,
            'amount' => $this->amount,
            'category_id' => $this->categoryId,
            'category_name' => $this->categoryName,
        ];
    }
}
