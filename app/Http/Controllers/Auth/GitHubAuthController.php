<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GitHubAuthController extends Controller
{
    /**
     * Redirect to the socialite github provider.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirectToProvider()
    {
        return Socialite::driver('github')->redirect();
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function handleProviderCallback()
    {
        try {
            $user = Socialite::driver('github')->user();
            $authUser = $this->findOrCreateUser($user);

            Auth::login($authUser, true);

            return Redirect::to('home');
        } catch (\Exception $e) {
            return Redirect::to('auth/github');
        }
    }

    /**
     * Finds or creates the user.
     *
     * @param $githubUser
     * @return User
     * @throws \Exception
     */
    protected function findOrCreateUser($githubUser)
    {
        $action = 'update';
        $user = User::where('email', $githubUser->getEmail())->first();

        $email = $githubUser->getEmail();

        if (!$email) {
            throw new \Exception('A valid github login was found but no e-mail address.');
        }

        if (!$user) {
            $action = 'create';
            $user = new User();
            $user->password = Hash::make(Str::random(32));
        }

        $user->name = $githubUser->getName();
        $user->email = $email;
        $user->github_id = $githubUser->getId();
        $user->avatar = $githubUser->getAvatar();

        $didSave = $user->save();

        if (!$didSave) {
            throw new \Exception("Failed to $action user: " . $githubUser->getEmail());
        }

        return $user;
    }
}
