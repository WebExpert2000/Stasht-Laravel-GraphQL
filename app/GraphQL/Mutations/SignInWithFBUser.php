<?php

namespace App\GraphQL\Mutations;

use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Joselfonseca\LighthouseGraphQLPassport\GraphQL\Mutations\BaseAuthResolver;
use Facebook\Facebook;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;

use App\Models\FacebookUser;
use App\Models\User;
use Carbon\Carbon;

class SignInWithFBUser extends BaseAuthResolver
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
        $appId = env('FBAPP_ID');
        $appSecret = env('FBAPP_SECRET_KEY');
        $fb = new Facebook([
            'app_id' => $appId,
            'app_secret' => $appSecret,
            'default_graph_version' => 'v3.1'
        ]);

        $access_token = $args['token'];

        // Get Facebook User Id, Name and email
        try {
            // Returns a `Facebook\FacebookResponse` object
            $response = $fb->get('/me?fields=id,name', $access_token);
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }
        $graphUser = $response->getGraphUser();

        // If this user already signed in, then retrieve item, and
        $prevUser = FacebookUser::where('facebook_user_id', $graphUser['id'])->first();
        if($prevUser !== null){
            $cred = array(
                'data' => array(
                    'username' => (User::select('email')->where('fb_user_id', $prevUser->id)->first())['email'],
                    'password' => 'facebook_default_password',
                ),
                'directive' => NULL,
            );

            $credentials = $this->buildCredentials($cred);
            return $this->makeRequest($credentials);
        }

        // Extend token
        $token_url = "https://graph.facebook.com/oauth/access_token?client_id=" . $appId . "&client_secret=" . $appSecret . "&grant_type=fb_exchange_token&fb_exchange_token=" . $access_token;

        $c = curl_init();
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($c, CURLOPT_URL, $token_url);
        $contents = curl_exec($c);
        $err  = curl_getinfo($c,CURLINFO_HTTP_CODE);
        curl_close($c);

        $params = null;
        parse_str($contents, $params);

        $keys = array_keys($params);
        $extendedTokenParams = json_decode($keys[0], true);

        // Save to table
        $facebook_user = new FacebookUser;
        $facebook_user->facebook_user_id = $graphUser['id'];
        $facebook_user->access_token = $extendedTokenParams['access_token'];
        $facebook_user->expires_in = $extendedTokenParams['expires_in'];
        $facebook_user->token_type = $extendedTokenParams['token_type'];

        $facebook_user->save();

        $user = new User;
        $user->name = $graphUser['name'];
        $user->email = $graphUser['email'];
        $user->password = bcrypt('facebook_default_password');
        $user->fb_user_id = $facebook_user->id;
        $user->save();

        $cred = array(
            'data' => array(
                'username' => $graphUser['email'],
                'password' => 'facebook_default_password',
            ),
            'directive' => NULL,
        );
        // $args['email'] = $args['username'];
        $credentials = $this->buildCredentials($cred);
        return $this->makeRequest($credentials);
    }
}
