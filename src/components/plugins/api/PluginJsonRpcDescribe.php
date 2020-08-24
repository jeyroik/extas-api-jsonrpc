<?php
namespace extas\components\plugins\api;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\App;

/**
 * Class PluginJsonRpcDescribe
 *
 * @package extas\components\plugins\api
 * @author jeyroik <jeyroik@gmail.com>
 */
class PluginJsonRpcDescribe extends PluginJsonRpc
{
    /**
     * @param App $app
     */
    public function __invoke(App &$app): void
    {
        $app->any(
            '/_describe[/{version}]',
            function (RequestInterface $request, ResponseInterface $response, array $args)  {
                return static::getApi($request, $response, '_describe', $args)->dispatch();
            }
        );
    }
}
