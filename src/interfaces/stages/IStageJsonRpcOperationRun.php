<?php
namespace extas\interfaces\stages;

use extas\interfaces\http\IHasJsonRpcRequest;
use extas\interfaces\http\IHasJsonRpcResponse;
use extas\interfaces\operations\IJsonRpcOperation;
use Psr\Http\Message\ResponseInterface;

/**
 * Interface IStageJsonRpcOperationRun
 *
 * @package extas\interfaces\stages
 * @author jeyroik <jeyroik@gmail.com>
 */
interface IStageJsonRpcOperationRun extends IHasJsonRpcRequest, IHasJsonRpcResponse
{
    public const NAME = 'extas.jsonrpc.operation.run';

    /**
     * @param IJsonRpcOperation $operation
     * @param string $endpoint
     * @return ResponseInterface
     */
    public function __invoke(IJsonRpcOperation $operation, string $endpoint): ResponseInterface;
}
