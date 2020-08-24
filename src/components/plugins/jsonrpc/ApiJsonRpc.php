<?php
namespace extas\components\plugins\jsonrpc;

use extas\components\http\THasHttpIO;
use extas\components\http\THasJsonRpcRequest;
use extas\components\http\THasJsonRpcResponse;
use extas\components\plugins\Plugin;
use extas\interfaces\operations\IJsonRpcOperation;
use extas\interfaces\stages\IStageJsonRpcOperationRun;
use Psr\Http\Message\ResponseInterface;

/**
 * Class ApiJsonRpc
 *
 * @package extas\components\plugins\jsonrpc
 * @author jeyroik <jeyroik@gmail.com>
 */
class ApiJsonRpc extends Plugin implements IStageJsonRpcOperationRun
{
    use THasJsonRpcResponse;
    use THasJsonRpcRequest;
    use THasHttpIO;

    /**
     * @param IJsonRpcOperation $operation
     * @param string $endpoint
     * @return ResponseInterface
     */
    public function __invoke(IJsonRpcOperation $operation, string $endpoint): ResponseInterface
    {
        return $this->successResponse(
            $this->getJsonRpcRequest()->getId(),
            $operation->run($this->getHttpIO())
        );
    }
}
