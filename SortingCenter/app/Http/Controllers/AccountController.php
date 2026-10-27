<?php

namespace App\Http\Controllers;

use App\Http\Requests\AccountUpdateRequest;
use App\Models\Area;
use App\Models\HubProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class AccountController extends Controller
{
    public function edit()
    {
        return view('sorting.account', [
            'hub' => HubProfile::current(),
            'areas' => Area::orderBy('name')->get(),
        ]);
    }

    public function update(AccountUpdateRequest $request): JsonResponse|RedirectResponse
    {
        $data = $request->validated();
        $hub = HubProfile::current();

        if ($data['entity_name'] !== $hub->name && ! $request->boolean('confirm')) {
            $message = 'Changing the registered entity name requires confirmation.';

            if ($request->expectsJson()) {
                return response()->json([
                    'ok' => false,
                    'message' => $message,
                    'errors' => ['entity_name' => [$message]],
                ], 409);
            }

            return back()
                ->withInput()
                ->withErrors(['entity_name' => $message])
                ->setStatusCode(409);
        }

        $data['contact_phone'] = $this->formatPhone($data['contact_phone'] ?? null);
        $data['name'] = $data['entity_name'];
        $data['staff_name'] = trim($data['first_name'].' '.$data['last_name']);
        $data['staff_initials'] = strtoupper(
            substr($data['first_name'], 0, 1).substr($data['last_name'], 0, 1)
        );

        unset($data['entity_name'], $data['confirm']);

        $hub->update($data);

        $message = 'Account profile updated successfully.';

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => $message,
                'hub' => $hub->fresh(),
            ]);
        }

        return back()->with('status', $message)->with('toast', $message);
    }

    private function formatPhone(?string $phone): ?string
    {
        if (blank($phone)) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $phone);

        if (str_starts_with($digits, '09') && strlen($digits) === 11) {
            return substr($digits, 0, 4).' '.substr($digits, 4, 3).' '.substr($digits, 7, 4);
        }

        if (strlen($digits) === 10) {
            return '('.substr($digits, 0, 3).') '.substr($digits, 3, 3).'-'.substr($digits, 6, 4);
        }

        return $phone;
    }
}
