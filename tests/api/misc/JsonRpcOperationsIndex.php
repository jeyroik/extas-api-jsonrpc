<?php
namespace tests\api\misc;

use extas\components\api\jsonrpc\operations\OperationRunner;
use extas\interfaces\repositories\IRepository;

/**
 * Class JsonRpcOperationsIndex
 *
 * @method IRepository jsonRpcOperations()
 *
 * @package tests\api\misc
 * @author jeyroik <jeyroik@gmail.com>
 */
class JsonRpcOperationsIndex extends OperationRunner
{
    /**
     * @return array
     */
    protected function run(): array
    {
        $ops = $this->jsonRpcOperations()->all([]);
        $result = [];
        foreach ($ops as $op) {
            $result[] = $op->__toArray();
        }

        return $result;
    }
}
