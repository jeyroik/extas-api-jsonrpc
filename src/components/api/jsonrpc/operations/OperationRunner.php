<?php
namespace extas\components\api\jsonrpc\operations;

use extas\components\http\THasHttpIO;
use extas\interfaces\operations\IJsonRpcOperation;
use extas\interfaces\repositories\IRepository;
use extas\components\http\THasJsonRpcRequest;
use extas\components\http\THasJsonRpcResponse;
use extas\components\operations\OperationDispatcher;

/**
 * Class OperationDispatcher
 *
 * @package extas\components\jsonrpc\operations
 * @author jeyroik@gmail.com
 */
abstract class OperationRunner extends OperationDispatcher
{
    use THasJsonRpcRequest;
    use THasJsonRpcResponse;
    use THasHttpIO;

    /**
     * @param array $httpIO
     * @return array
     */
    public function __invoke(array $httpIO): array
    {
        $httpIO[static::FIELD__OPERATION] = $this->getOperation();
        $this->config = $httpIO;

        return $this->run();
    }
}
