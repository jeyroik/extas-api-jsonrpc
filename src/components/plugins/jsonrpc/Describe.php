<?php
namespace extas\components\plugins\jsonrpc;

use extas\components\http\THasJsonRpcRequest;
use extas\components\http\THasJsonRpcResponse;
use extas\components\plugins\Plugin;
use extas\interfaces\operations\IJsonRpcOperation;
use extas\interfaces\repositories\IRepository;
use extas\interfaces\stages\IStageJsonRpcOperationRun;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Describe
 *
 * @method IRepository jsonRpcOperations()
 *
 * @package extas\components\plugins\jsonrpc
 * @author jeyroik <jeyroik@gmail.com>
 */
class Describe extends Plugin implements IStageJsonRpcOperationRun
{
    use THasJsonRpcRequest;
    use THasJsonRpcResponse;

    public const OPERATION__ALL = 'specs.operations';

    /**
     * @param IJsonRpcOperation $operation
     * @param string $endpoint
     * @return ResponseInterface
     */
    public function __invoke(IJsonRpcOperation $operation, string $endpoint): ResponseInterface
    {
        if ($operation->getName() == static::OPERATION__ALL) {
            $result = [];
            /**
             * @var IJsonRpcOperation[] $ops
             */
            $ops = $this->jsonRpcOperations()->all([]);
            foreach ($ops as $op) {
                $result[$op->getName()] = $op->getSpecs();
            }
        } else {
            $result = $operation->getSpecs();
        }

        return $this->successResponse($this->getJsonRpcRequest()->getId(), $result);
    }
}
