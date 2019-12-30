<?php

namespace App\GraphQL\Queries;

use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Auth;

class IgPost
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

        // Get current user's instagram user information
        $igUser = Auth::user()->igUser;
        $ig_uid = $igUser->instagram_user_id;
        $ig_token = $igUser->access_token;

        // Get Instagram User info
        $mediaEndpoint = "https://api.instagram.com/v1/users/self/media/recent/?access_token=" . $ig_token;

        $c = curl_init();
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($c, CURLOPT_URL, $mediaEndpoint);
        $contents = curl_exec($c);
        $err  = curl_getinfo($c,CURLINFO_HTTP_CODE);
        curl_close($c);

        $params = null;

        //error_type=OAuthAccessTokenException

        $posts = (\json_decode($contents))->data;

        return array(
            'success' => true,
            'posts' => \json_encode($posts)
        );
    }
}
