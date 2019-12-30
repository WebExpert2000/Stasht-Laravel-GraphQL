<?php

namespace App\GraphQL\Mutations;

use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Joselfonseca\LighthouseGraphQLPassport\GraphQL\Mutations\BaseAuthResolver;

use App\Models\InstagramUser;
use App\Models\User;
use Carbon\Carbon;

class SignInWithIGUser extends BaseAuthResolver
{
    /**
     * Return a value for the field.
     *
     * @param  null  $rootValue Usually contains the result returned from the parent field. In this case, it is always `null`.
     * @param  mixed[]  $args The arguments that were passed into the field.
     * @param  \Nuwave\Lighthouse\Support\Contracts\GraphQLContext  $context Arbitrary data that is shared between all fields of a single query.
     * @param  \GraphQL\Type\Definition\ResolveInfo  $resolveInfo Information about the query itself, such as the execution state, the field name, path to the field from the root, and more.
     * @return mixed
     */
    public function resolve($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        // TODO implement the resolver
        $access_token = $args['token'];

        // Get Instagram User info
        $userEndpoint = "https://api.instagram.com/v1/users/self/?access_token=" . $access_token;

        $c = curl_init();
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($c, CURLOPT_URL, $userEndpoint);
        $contents = curl_exec($c);
        $err  = curl_getinfo($c,CURLINFO_HTTP_CODE);
        curl_close($c);

        $params = null;
        parse_str($contents, $params);

        //error_type=OAuthAccessTokenException

        $igUserInfo = (\json_decode($contents))->data;

        // If this user already signed in, then retrieve item, and
        $prevUser = InstagramUser::where('instagram_user_id', $igUserInfo->id)->first();
        if($prevUser !== null){
            $cred = array(
                'data' => array(
                    'username' => (User::select('email')->where('ig_user_id', $prevUser->id)->first())['email'],
                    'password' => 'instagram_default_password',
                ),
                'directive' => NULL,
            );

            $credentials = $this->buildCredentials($cred);
            return $this->makeRequest($credentials);
        }

        $instagram_user = new InstagramUser;
        $instagram_user->instagram_user_id = $igUserInfo->id;
        $instagram_user->instagram_user_name = $igUserInfo->username;
        $instagram_user->access_token = $access_token;

        $instagram_user->save();

        $user = new User;
        $user->name = $igUserInfo->username;
        $user->email = $igUserInfo->username;
        $user->password = bcrypt('instagram_default_password');
        $user->ig_user_id = $instagram_user->id;
        $user->save();

        $cred = array(
            'data' => array(
                'username' => $igUserInfo->username,
                'password' => 'instagram_default_password',
            ),
            'directive' => NULL,
        );

        $credentials = $this->buildCredentials($cred);
        return $this->makeRequest($credentials);
    }
}
