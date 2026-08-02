<?php

namespace App\Http\Controllers\Settings;

use App\Actions\Settings\UpdatePassword;
use App\Actions\Settings\UpdateProfile;
use App\Actions\Settings\UpdateSettings;
use App\DTOs\Settings\PasswordData;
use App\DTOs\Settings\ProfileData;
use App\DTOs\Settings\SettingsData;
use App\Enums\Currency;
use App\Enums\DashboardPeriod;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdatePasswordRequest;
use App\Http\Requests\Settings\UpdateProfileRequest;
use App\Http\Requests\Settings\UpdateSettingsRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(Request $request): View
    {
        return view('settings.index', [
            'settings' => $request->user()->settings,
            'currencies' => Currency::cases(),
            'dashboardPeriods' => DashboardPeriod::cases(),
        ]);
    }

    public function update(UpdateSettingsRequest $request, UpdateSettings $updateSettings): RedirectResponse
    {
        $data = SettingsData::fromArray(
            $request->validated(),
        );

        $updateSettings->handle($request->user(), $data);

        return back()->with(
            'status',
            'settings-updated',
        );
    }

    public function updateProfile(UpdateProfileRequest $request, UpdateProfile $updateProfile): RedirectResponse
    {
        $emailChanged = $updateProfile->handle(
            $request->user(),
            ProfileData::fromArray($request->validated()),
        );

        if ($emailChanged) {
            return to_route('verification.notice');
        }

        return back()->with('status', 'profile-updated');
    }

    public function updatePassword(UpdatePasswordRequest $request, UpdatePassword $updatePassword): RedirectResponse
    {
        $updatePassword->handle($request->user(), PasswordData::fromArray($request->validated()));

        return back()->with('status', 'password-updated');
    }
}
