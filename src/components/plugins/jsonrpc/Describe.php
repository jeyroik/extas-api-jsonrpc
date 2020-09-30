<?php
namespace extas\components\plugins\jsonrpc;

use extas\components\http\THasJsonRpcRequest;
use extas\components\http\THasJsonRpcResponse;
use extas\components\plugins\Plugin;
use extas\interfaces\operations\IJsonRpcOperation;
use extas\interfaces\repositories\IRepository;
use extas\interfaces\stages\IStageApiJsonRpcDescribe;
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
    public const PARAM__OPERATION_ALL = 'all';

    /**
     * @param IJsonRpcOperation $operation
     * @param string $endpoint
     * @return ResponseInterface
     */
    public function __invoke(IJsonRpcOperation $operation, string $endpoint): ResponseInterface
    {
        $all = $this->getParameterValue(static::PARAM__OPERATION_ALL, []);
        $all[] = static::OPERATION__ALL;

        $operations = in_array($operation->getName(), $all)
            ? $this->jsonRpcOperations()->all([IJsonRpcOperation::FILED__VERSION => $this->getVersion()])
            : [$operation];

        $result = $this->extractOperationsSpecs($operations);

        foreach ($this->getPluginsByStage(IStageApiJsonRpcDescribe::NAME) as $plugin) {
            $result = $plugin($result);
        }

        return $this->successResponse($this->getJsonRpcRequest()->getId(), $result);
    }

    /**
     * @param array $operations
     * @return array
     */
    protected function extractOperationsSpecs(array $operations): array
    {
        $result = [];

        foreach ($operations as $operation) {
            $result[$operation->getName()] = $operation->getSpecs();
        }

        return $result;
    }

    /**
     * @return int
     */
    protected function getVersion(): int
    {
        $args = $this->getArguments();

        return $args['version'] ?? 0;
    }
}
