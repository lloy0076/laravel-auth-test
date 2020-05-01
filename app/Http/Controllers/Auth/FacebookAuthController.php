<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class FacebookAuthController extends Controller
{
    /**
     * Redirect to the socialite github provider.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirectToProvider()
    {
        return Socialite::driver('facebook')->scopes(['email'])->redirect();
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function handleProviderCallback()
    {
        try {
            $user = Socialite::driver('facebook')->user();
            $authUser = $this->findOrCreateUser($user);

            Auth::login($authUser, true);

            return Redirect::to('home');
        } catch (\Exception $e) {
            return Redirect::to('auth/facebook');
        }
    }

    /**
     * Finds or creates the user.
     *
     * @param $facebookUser
     * @return User
     * @throws \Exception
     */
    protected function findOrCreateUser($facebookUser)
    {
        $action = 'update';
        $email = $facebookUser->getEmail();

        if (!$email) {
            throw new \Exception('A valid facebook login was found but no e-mail address.');
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            $action = 'create';
            $user = new User();
            $user->password = Hash::make(Str::random(32));
        }

        $user->name = $facebookUser->getName();
        $user->email = $email;
        $user->facebook_id = $facebookUser->getId();
        $user->avatar = $facebookUser->getAvatar();

        $didSave = $user->save();

        if (!$didSave) {
            throw new \Exception("Failed to $action user: " . $facebookUser->getEmail());
        }

        return $user;
    }
}
