<?php
namespace extas\components\plugins\api;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\App;

/**
 * Class PluginJsonRpcMetrics
 *
 * @package extas\components\plugins\api
 * @author jeyroik <jeyroik@gmail.com>
 */
class PluginJsonRpcMetrics extends PluginJsonRpc
{
    /**
     * @param App $app
     */
    public function __invoke(App &$app): void
    {
        $app->any(
            '/metrics[/{version}]',
            function (RequestInterface $request, ResponseInterface $response, array $args)  {
                return static::getApi($request, $response, 'metrics', $args)->dispatch();
            }
        );
    }
}
