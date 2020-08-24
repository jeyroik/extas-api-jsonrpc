<?php
namespace extas\components\plugins\api;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\App;

/**
 * Class PluginJsonRpcApi
 *
 * @package extas\components\plugins\api
 * @author jeyroik <jeyroik@gmail.com>
 */
class PluginJsonRpcApi extends PluginJsonRpc
{
    /**
     * @param App $app
     */
    public function __invoke(App &$app): void
    {
        $app->post(
            '/api/jsonrpc[/{version}]',
            function (RequestInterface $request, ResponseInterface $response, array $args) {
                return static::getApi($request, $response, 'api/jsonrpc', $args)->dispatch();
            }
        );
    }
}
