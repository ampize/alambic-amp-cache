<?php
namespace AMPCache;
use Alambic\Exception\ConnectorArgs;
use Alambic\Exception\ConnectorConfig;
use Alambic\Exception\ConnectorUsage;
use GuzzleHttp\Ring\Client\CurlHandler;
use Illuminate\Http\Request;
class AMPCache
{
    /**
     * Default call of AMPCache.
     *
     * @param array $payload
     *
     * @return array
     */
    public function __invoke($payload = [])
    {
        // Get configuration
        $graphQLAmpApiRoute = isset($payload["connectorBaseConfig"]["graphQLAmpApiRoute"]) ? $payload["connectorBaseConfig"]["graphQLAmpApiRoute"] : null;
        if (!$graphQLAmpApiRoute){
            throw new ConnectorConfig('Insufficient configuration : graphQL AMP api route required');
        }
        if (!isset($payload['response'])) {
            $host = preg_replace('#^https?://#', '', rtrim(Request::root(),'/'));
            $secure = Request::secure();
            $uri = "https://cdn.ampproject.org/c";
            if (Request::secure()) $uri. = "/s";
            $uri.=$graphQLAmpApiRoute."?query=".$payload['pipelineParams']['currentRequestString'];
            $handler = new CurlHandler();
            $response = $handler([
                'http_method' => 'GET',
                'uri'         => $uri
            ]);
            $response->then(function (array $response) {
                if ($response['status']=="200") {
                    $payload['response'] = json_decode($response['body']);
                };
            });

            $response->wait();
        }
        return $payload;
    }
}
