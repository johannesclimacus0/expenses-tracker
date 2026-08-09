<?php

declare(strict_types=1);

namespace App\Http\Controllers\Goals;

use App\Actions\Goals\CreateGoal;
use App\Actions\Goals\DeleteGoal;
use App\Actions\Goals\UpdateGoal;
use App\DTOs\Goals\GoalData;
use App\Enums\GoalStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Goals\DeleteGoalRequest;
use App\Http\Requests\Goals\StoreGoalRequest;
use App\Http\Requests\Goals\UpdateGoalRequest;
use App\Models\Goal;
use App\Services\Goals\GoalProgressService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

final class GoalController extends Controller
{
    public function index(Request $request, GoalProgressService $progressService): View
    {
        Gate::authorize('viewAny', Goal::class);

        $goals = $request->user()->goals()
            ->orderByRaw("CASE status WHEN 'active' THEN 1 WHEN 'paused' THEN 2 WHEN 'completed' THEN 3 ELSE 4 END")
            ->orderBy('deadline')
            ->paginate(10)
            ->withQueryString();
        $progressByGoal = $goals->getCollection()->mapWithKeys(
            fn (Goal $goal) => [$goal->id => $progressService->for($goal)],
        );

        return view('goals.index', compact('goals', 'progressByGoal'));
    }

    public function create(): View
    {
        Gate::authorize('create', Goal::class);

        return view('goals.create');
    }

    public function store(StoreGoalRequest $request, CreateGoal $createGoal): RedirectResponse
    {
        $goal = $createGoal->handle(
            $request->user(),
            GoalData::fromArray($request->validated()),
        );

        return to_route('goals.show', $goal)->with('status', 'Цель создана');
    }

    public function show(Goal $goal, GoalProgressService $progressService): View
    {
        Gate::authorize('view', $goal);

        $progress = $progressService->for($goal);
        $contributions = $goal->contributions()
            ->orderByDesc('contributed_at')
            ->orderByDesc('id')
            ->paginate(10);

        return view('goals.show', compact('goal', 'progress', 'contributions'));
    }

    public function edit(Goal $goal): View
    {
        Gate::authorize('update', $goal);

        $statuses = GoalStatus::cases();

        return view('goals.edit', compact('goal', 'statuses'));
    }

    public function update(UpdateGoalRequest $request, Goal $goal, UpdateGoal $updateGoal): RedirectResponse
    {
        $updateGoal->handle($goal, GoalData::fromArray($request->validated()));

        return to_route('goals.show', $goal)->with('status', 'Цель обновлена');
    }

    public function destroy(DeleteGoalRequest $request, Goal $goal, DeleteGoal $deleteGoal): RedirectResponse
    {
        $deleteGoal->handle($goal);

        return to_route('goals.index')->with('status', 'Цель удалена');
    }
}
