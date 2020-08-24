<?php
namespace tests\api\misc;

use extas\components\http\THasJsonRpcRequest;
use extas\components\http\THasJsonRpcResponse;
use extas\components\items\SnuffItem;
use extas\components\plugins\Plugin;
use extas\interfaces\operations\IJsonRpcOperation;
use extas\interfaces\repositories\IRepository;
use extas\interfaces\stages\IStageBeforeJsonRpcOperation;

/**
 * Class BeforeAll
 *
 * @method IRepository snuff()
 *
 * @package tests\api\misc
 * @author jeyroik <jeyroik@gmail.com>
 */
class BeforeAll extends Plugin implements IStageBeforeJsonRpcOperation
{
    use THasJsonRpcRequest;
    use THasJsonRpcResponse;

    public function __invoke(IJsonRpcOperation $operation, string $endpoint): IJsonRpcOperation
    {
        $this->snuff()->create(new SnuffItem([
            'name' => 'before.all'
        ]));

        return $operation;
    }
}
