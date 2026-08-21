<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Contracts\VerifyEmailResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the user's email as verified and log them in.
     */
    public function __invoke(Request $request, string $id, string $hash): VerifyEmailResponse
    {
        $user = User::query()->findOrFail($id);

        if (! hash_equals(sha1($user->getEmailForVerification()), (string) $hash)) {
            abort(403);
        }

        if (! $user->isActive()) {
            abort(403, __('auth.inactive'));
        }

        if (! $user->hasVerifiedEmail()) {
            if ($user->markEmailAsVerified()) {
                event(new Verified($user));
            }
        }

        Auth::guard(config('fortify.guard', 'web'))->login($user, true);

        $request->session()->regenerate();

        return app(VerifyEmailResponse::class);
    }
}
