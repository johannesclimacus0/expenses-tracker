<?php

declare(strict_types=1);

namespace App\Http\Requests\Goals;

use App\Models\Goal;
use App\Models\GoalContribution;
use Illuminate\Foundation\Http\FormRequest;

final class DeleteGoalContributionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $goal = $this->route('goal');
        $contribution = $this->route('contribution');

        return $goal instanceof Goal
            && $contribution instanceof GoalContribution
            && $contribution->goal_id === $goal->getKey()
            && ($this->user()?->can('contribute', $goal) ?? false);
    }

    public function rules(): array
    {
        return [];
    }
}
