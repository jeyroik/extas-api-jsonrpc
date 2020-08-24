<?php
namespace extas\components\plugins\api;

use extas\components\api\ApiJsonRpc;
use extas\components\plugins\Plugin;
use extas\interfaces\api\IApiJsonRpc;
use extas\interfaces\stages\IStageApiAppInit;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\App;

/**
 * Class PluginJsonRpc
 *
 * @package extas\components\plugins\api
 * @author jeyroik <jeyroik@gmail.com>
 */
class PluginJsonRpc extends Plugin implements IStageApiAppInit
{
    public const PARAM__PATTERN = 'pattern';
    public const PARAM__ENDPOINT = 'endpoint';

    /**
     * @param App $app
     */
    public function __invoke(App &$app): void
    {
        $app->post(
            $this->getParameterValue(static::PARAM__PATTERN),
            function (RequestInterface $request, ResponseInterface $response, array $args) {
                return static::getApi(
                    $request,
                    $response,
                    $this->getParameterValue(static::PARAM__ENDPOINT),
                    $args
                )->dispatch();
            }
        );
    }

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param string $endpoint
     * @param array $args
     * @return IApiJsonRpc
     */
    protected static function getApi(
        RequestInterface $request,
        ResponseInterface $response,
        string $endpoint,
        array $args = []
    ): IApiJsonRpc
    {
        return new ApiJsonRpc([
            ApiJsonRpc::FIELD__PSR_REQUEST => $request,
            ApiJsonRpc::FIELD__PSR_RESPONSE => $response,
            ApiJsonRpc::FIELD__ENDPOINT => $endpoint,
            ApiJsonRpc::FIELD__ARGUMENTS => $args
        ]);
    }
}
