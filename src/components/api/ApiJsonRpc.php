<?php
namespace extas\components\api;

use extas\components\exceptions\MissedOrUnknown;
use extas\components\http\THasHttpIO;
use extas\components\http\THasJsonRpcRequest;
use extas\components\http\THasJsonRpcResponse;
use extas\components\Item;
use extas\interfaces\api\IApiJsonRpc;
use extas\interfaces\operations\IJsonRpcOperation;
use extas\interfaces\repositories\IRepository;
use extas\interfaces\stages\IStageAfterJsonRpcOperation;
use extas\interfaces\stages\IStageBeforeJsonRpcOperation;
use extas\interfaces\stages\IStageJsonRpcOperationRun;
use Psr\Http\Message\ResponseInterface;

/**
 * Class ApiJsonRpc
 *
 * @method IRepository jsonRpcOperations()
 *
 * @package extas\components\api
 * @author jeyroik <jeyroik@gmail.com>
 */
class ApiJsonRpc extends Item implements IApiJsonRpc
{
    use THasJsonRpcRequest;
    use THasJsonRpcResponse;
    use THasHttpIO;

    /**
     * @return ResponseInterface
     */
    public function dispatch(): ResponseInterface
    {
        $this->applyProtocols();

        try {
            $request = $this->getJsonRpcRequest();
            $method = $request->getMethod('operations.index');
            $operation = $this->getJsonRpcOperation($method);

            $operation = $this->runBeforeOperationStage($operation);
            $response = $this->runOperationStage($operation);
            $response = $this->runAfterOperationStage($operation, $response);
        } catch (\Exception $e) {
            $response = $this->errorResponse($request->getId(), $e->getMessage(), $e->getCode());
        }

        return $response;
    }

    /**
     * @return string
     */
    public function getEndpoint(): string
    {
        return $this->config[static::FIELD__ENDPOINT] ?? '';
    }

    /**
     * @param IJsonRpcOperation $operation
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    protected function runAfterOperationStage(
        IJsonRpcOperation $operation,
        ResponseInterface $response
    ): ResponseInterface
    {
        $endpoint = $this->getEndpoint();

        $stage = IStageAfterJsonRpcOperation::NAME;
        foreach ($this->getPluginsByStage($stage, $this->getHttpIO()) as $plugin) {
            /**
             * @var IStageAfterJsonRpcOperation $plugin
             */
            $response = $plugin($operation, $endpoint, $response);
            $this->setPsrResponse($response);
        }

        $stage .= '.' . $endpoint;
        foreach ($this->getPluginsByStage($stage, $this->getHttpIO()) as $plugin) {
            /**
             * @var IStageAfterJsonRpcOperation $plugin
             */
            $response = $plugin($operation, $endpoint, $response);
            $this->setPsrResponse($response);
        }

        return $response;
    }

    /**
     * @param IJsonRpcOperation $operation
     * @return ResponseInterface
     */
    protected function runOperationStage(IJsonRpcOperation $operation): ResponseInterface
    {
        $endpoint = $this->getEndpoint();
        $stage = IStageJsonRpcOperationRun::NAME . '.' . $endpoint;
        foreach ($this->getPluginsByStage($stage, $this->getHttpIO()) as $plugin) {
            /**
             * @var IStageJsonRpcOperationRun $plugin
             */
            $response = $plugin($operation, $endpoint);
            $this->setPsrResponse($response);
        }

        return $this->getPsrResponse();
    }

    /**
     * @param IJsonRpcOperation $operation
     * @return IJsonRpcOperation
     */
    protected function runBeforeOperationStage(IJsonRpcOperation $operation): IJsonRpcOperation
    {
        $endpoint = $this->getEndpoint();

        $stage = IStageBeforeJsonRpcOperation::NAME;
        foreach ($this->getPluginsByStage($stage, $this->getHttpIO()) as $plugin) {
            /**
             * @var IStageBeforeJsonRpcOperation $plugin
             */
            $operation = $plugin($operation, $endpoint);
        }

        $stage .= '.' . $endpoint;
        foreach ($this->getPluginsByStage($stage, $this->getHttpIO()) as $plugin) {
            /**
             * @var IStageBeforeJsonRpcOperation $plugin
             */
            $operation = $plugin($operation, $endpoint);
        }

        return $operation;
    }

    /**
     * @param string $name
     * @return IJsonRpcOperation
     * @throws MissedOrUnknown
     */
    protected function getJsonRpcOperation(string $name): IJsonRpcOperation
    {
        $args = $this->getArguments();
        $version = $args[IJsonRpcOperation::FILED__VERSION] ?? 0;
        $operation = $this->jsonRpcOperations()->one([
            IJsonRpcOperation::FIELD__NAME => $name,
            IJsonRpcOperation::FILED__VERSION => $version
        ]);

        if (!$operation) {
            throw new MissedOrUnknown('json rpc operation "' . $name . '"');
        }

        return $operation;
    }

    /**
     * @return string
     */
    protected function getSubjectForExtension(): string
    {
        return static::SUBJECT;
    }
}
