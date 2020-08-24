<?php
namespace extas\interfaces\stages;

use extas\interfaces\http\IHasJsonRpcRequest;
use extas\interfaces\http\IHasJsonRpcResponse;
use extas\interfaces\operations\IJsonRpcOperation;

/**
 * Interface IStageBeforeJsonRpcOperation
 *
 * @package extas\interfaces\stages
 * @author jeyroik <jeyroik@gmail.com>
 */
interface IStageBeforeJsonRpcOperation extends IHasJsonRpcResponse, IHasJsonRpcRequest
{
    public const NAME = 'extas.before.jsonrpc.operation';

    /**
     * @param IJsonRpcOperation $operation
     * @param string $endpoint
     * @return IJsonRpcOperation
     */
    public function __invoke(IJsonRpcOperation $operation, string $endpoint): IJsonRpcOperation;
}
