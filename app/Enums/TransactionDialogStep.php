<?php

declare(strict_types=1);

namespace App\Enums;

enum TransactionDialogStep: string
{
    case Amount = 'amount';
    case Category = 'category';
    case Comment = 'comment';
    case Invalid = 'invalid';
}
