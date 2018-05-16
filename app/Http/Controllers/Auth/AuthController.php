<?php
/**
 * Created by PhpStorm.
 * User: Chepur
 * Date: 13.04.2018
 * Time: 11:34
 */

namespace App\Http\Controllers\Auth;


use App\Http\Controllers\Controller;
use App\User;
use App\UserProperty;
use Couchbase\UserSettings;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    private $redirectTo = '/home';

    /**
     * Redirect the user to the OAuth Provider.
     *
     * @return Response
     */
    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    /**
     * Obtain the user information from provider.  Check if the user already exists in our
     * database by looking up their provider_id in the database.
     * If the user exists, log them in. Otherwise, create a new user then log them in. After that
     * redirect them to the authenticated users homepage.
     *
     * @return Response
     */
    public function handleProviderCallback($provider)
    {
        $user = Socialite::driver($provider)->user();

        $authUser = $this->findOrCreateUser($user, $provider);
        Auth::login($authUser, true);

        return redirect($this->redirectTo);
    }

    /**
     * If a user has registered before using social auth, return the user
     * else, create a new user object.
     *
     * @param  $user     Socialite user object
     * @param  $provider Social auth provider
     *
     * @return  User
     */
    public function findOrCreateUser($user, $provider)
    {
        $authUser = User::where('provider_id', $user->id)->first();
        if ($authUser) {
            $property = new UserProperty(['status' => 'active']);
            $authUser->update(
                [
                    'avatar'       => $user->avatar,
                    'provider'     => $provider,
                    'provider_id'  => $user->id,
                    'access_token' => $user->token,
                ]
            );
            if (!$authUser->hasRole('user')) {
                $authUser->assignRole('user');
            }
            if (!isset($authUser->property->status)){
                $authUser->property()->save($property);
            }

            return $authUser;
        }
        /** @var User $user */
        $user = User::create(
            [
                'name'         => $user->name,
                'email'        => $user->email,
                'avatar'       => $user->avatar,
                'provider'     => $provider,
                'provider_id'  => $user->id,
                'access_token' => $user->token,
            ]
        );
        $user->property()->save(new UserProperty(['status' => 'active']));
        $user->assignRole('user');

        return $user;
    }


}