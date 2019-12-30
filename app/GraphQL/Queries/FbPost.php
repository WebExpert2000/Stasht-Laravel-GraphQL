<?php

namespace App\GraphQL\Queries;

use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

use Facebook\Facebook;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
use Auth;
use App\Models\FacebookUser;
use App\Models\User;
use GuzzleHttp\Client as GuzzleClient;

class FbPost
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
            'default_graph_version' => 'v3.3'
        ]);

        // $accessToken = env('FBAPP_ACCESS_TOKEN');
        $fbUser = Auth::user()->fbUser;
        $fb_uid = $fbUser->facebook_user_id;
        $fb_token = $fbUser->access_token;

        // Get User's Post list
        $feedData = "";
        try {
            $userFeed = $fb->get("/$fb_uid/posts", $fb_token);
            $feedBody = $userFeed->getDecodedBody();
            $feedData = $feedBody["data"];
        } catch (FacebookResponseException $e) {
            echo 'Facebook returned an error: ' . $e->getMessage();
            exit();
        } catch (FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit();
        }

        // Structure
        /* PostBody = {
            data: [{created_time, id, message}]
            paging: { previous: https:// ...,
                      next: https://
                  }
          }
        */
        $detailedPosts = array();

        $client = new GuzzleClient([
            'headers' => [
                'Accept' => 'application/json',
                // 'Content-Type' => 'application/json',
                // 'AccessToken' => $fb_token,
                'Authorization' => 'Bearer ' . $fb_token,
            ]
        ]);
        //
        // $requestURL = "https://graph.facebook.com/v3.3/10159856273765024_10158591386285024?fields=message,created_time,attachments,type&access_token=" . $fb_token;
        //
        // $graphResponse = $client->request('GET', $requestURL);
        // $body = $graphResponse->getBody();


        // var_dump($res->getBody());
        // exit(1);

        // $success = $res->getStatusCode();
        // // "200"
        // // echo $res->getHeader('content-type')[0];
        //
        // if($success !== 200){
        //     echo $success . " error ";
        //     echo $res->getHeader('content-type')[0];
        //     exit(1);
        // }
        // $response = $res->getBody();

        // {"type":"User"...'

        // foreach ($feedData as $feed) {

        // $detailedPosts[] = $graphNode;
        //     break;
        // }

        // Get Post data from Its ID

        /*
         * https://developers.facebook.com/docs/graph-api/reference/post/
         */

         /* Header Output */
        //  foreach ($graphResponse->getHeaders() as $name => $values) {
        //     echo $name . ': ' . implode(', ', $values) . "\r\n";
        // }
        //
        /* Response output */
        // echo $graphResponse->getStatusCode() . "\r\n"; // 200
        // echo $graphResponse->getReasonPhrase() . "\r\n"; // OK
        // echo $graphResponse->getProtocolVersion() . "\r\n"; // 1.1
        //
        // $body = $graphResponse->getBody();
        // echo $body . "\r\n";

        return array(
            "success" => true,
            "posts" => \json_encode($feedData),
        );
    }
}
