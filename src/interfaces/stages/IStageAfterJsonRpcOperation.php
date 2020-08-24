<?php
namespace extas\interfaces\stages;

use extas\interfaces\http\IHasJsonRpcRequest;
use extas\interfaces\http\IHasJsonRpcResponse;
use extas\interfaces\operations\IJsonRpcOperation;
use Psr\Http\Message\ResponseInterface;

/**
 * Interface IStageAfterJsonRpcOperation
 *
 * @package extas\interfaces\stages
 * @author jeyroik <jeyroik@gmail.com>
 */
interface IStageAfterJsonRpcOperation extends IHasJsonRpcResponse, IHasJsonRpcRequest
{
    public const NAME = 'extas.after.jsonrpc.operation';

    /**
     * @param IJsonRpcOperation $operation
     * @param string $endpoint
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function __invoke(
        IJsonRpcOperation $operation,
        string $endpoint,
        ResponseInterface $response
    ): ResponseInterface;
}
