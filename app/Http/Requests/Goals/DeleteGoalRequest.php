<?php

declare(strict_types=1);

namespace App\Http\Requests\Goals;

use App\Models\Goal;
use Illuminate\Foundation\Http\FormRequest;

final class DeleteGoalRequest extends FormRequest
{
    public function authorize(): bool
    {
        $goal = $this->route('goal');

        return $goal instanceof Goal
            && ($this->user()?->can('delete', $goal) ?? false);
    }

    public function rules(): array
    {
        return [];
    }
}
