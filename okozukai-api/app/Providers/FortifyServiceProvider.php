<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Http\Responses\FortifyLoginResponse;
use App\Http\Responses\FortifyLogoutResponse;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Fortify\Contracts\LogoutResponse;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(LoginResponse::class, FortifyLoginResponse::class);
        $this->app->singleton(LogoutResponse::class, FortifyLogoutResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        Fortify::loginView(fn (Request $request) => view('auth.parent-login', [
            'savedEmail' => $request->cookie('saved_parent_email'),
        ]));
        Fortify::registerView(fn () => view('auth.parent-register'));
        Fortify::requestPasswordResetLinkView(fn () => view('auth.forgot-password'));
        Fortify::resetPasswordView(fn (Request $request) => view('auth.reset-password', [
            'request' => $request,
        ]));

        Fortify::authenticateUsing(function (Request $request): ?User {
            $parent = User::query()
                ->where('email', $request->string('email')->toString())
                ->where('role', 'parent')
                ->first();

            if ($parent === null || ! Hash::check($request->string('password')->toString(), $parent->password)) {
                throw ValidationException::withMessages([
                    Fortify::username() => 'メールアドレスまたはパスワードが正しくありません。保護者用アカウントをご確認ください。',
                ]);
            }

            if ($request->boolean('save_email')) {
                Cookie::queue('saved_parent_email', $parent->email, 60 * 24 * 30);
            } else {
                Cookie::queue(Cookie::forget('saved_parent_email'));
            }

            return $parent;
        });

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

    }
}
