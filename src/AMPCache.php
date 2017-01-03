<?php

namespace AMPCache;

use Alambic\Exception\ConnectorConfig;
use Goutte\Client;

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
        // Bypass cache
        if (isset($payload['pipelineParams']['noCache']) && $payload['pipelineParams']['noCache']) {
            return $payload;
        }
        // Get configuration
        $graphQLAmpApiRoute = isset($payload['connectorBaseConfig']['graphQLAmpApiRoute']) ? $payload['connectorBaseConfig']['graphQLAmpApiRoute'] : null;
        if (!$graphQLAmpApiRoute) {
            throw new ConnectorConfig('Insufficient configuration : graphQL AMP api route required');
        }
        if (!isset($payload['response'])) {
            $urlParts = parse_url($_SERVER['HTTP_HOST']);
            $host = utf8_encode(isset($urlParts['host']) ? $urlParts['host'] : $urlParts['path']);
            //$host = str_replace("-","--",$host);
            //$host = str_replace(".","-",$host);
            $secure = empty($_SERVER['HTTPS']) ? false : true;
            $baseUri = 'https://cdn.ampproject.org';
            if ($secure) {
                $uri = $baseUri.'/c/s/';
            } else {
                $uri = $baseUri.'/c/';
            }
            $uri .= $host.$graphQLAmpApiRoute.'?query='.$payload['pipelineParams']['parentRequestString'];
            $client = new Client();
            try {
                $crawler = $client->request('GET', $uri);
                $response = json_decode(base64_decode($crawler->filterXPath("//*[@id='data']")->attr('data-response')), true);
                $payload['response'] = $response[array_keys($response)[0]];
            } catch (RequestException $e) {
                return $payload;
            }
        }

        return $payload;
    }
}
