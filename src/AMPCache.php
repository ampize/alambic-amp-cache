<?php
namespace AMPCache;
use Alambic\Exception\ConnectorConfig;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
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
            $urlParts = parse_url($_SERVER["HTTP_HOST"]);
            $host = utf8_encode(isset($urlParts["host"]) ? $urlParts["host"] : $urlParts["path"]);
            $host = str_replace("-","--",$host);
            $host = str_replace(".","-",$host);
            $secure = empty($_SERVER["HTTPS"]) ? false : true;
            $baseUri = "https://cdn.ampproject.org";
            if ($secure) {
                $uri = "/c/s";
            } else {
                $uri = "/c";
            }
            $uri.="/".$host.$graphQLAmpApiRoute."?query=".$payload['pipelineParams']['currentRequestString'];
            $client = new Client(['base_uri' => $baseUri]);
            try {
                $response = $client->request('GET', $uri);
                if ($response->getStatusCode()=="200") {
                    $payload['response'] = json_decode($response->getBody());
                }
            } catch (RequestException $e) {
                return $payload;
            }

        }
        return $payload;
    }
}
