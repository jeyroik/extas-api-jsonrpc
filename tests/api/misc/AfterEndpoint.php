<?php
namespace tests\api\misc;

use extas\components\http\THasJsonRpcRequest;
use extas\components\http\THasJsonRpcResponse;
use extas\components\items\SnuffItem;
use extas\components\plugins\Plugin;
use extas\interfaces\operations\IJsonRpcOperation;
use extas\interfaces\repositories\IRepository;
use extas\interfaces\stages\IStageAfterJsonRpcOperation;
use Psr\Http\Message\ResponseInterface;

/**
 * Class AfterEndpoint
 *
 * @method IRepository snuff()
 *
 * @package tests\api\misc
 * @author jeyroik <jeyroik@gmail.com>
 */
class AfterEndpoint extends Plugin implements IStageAfterJsonRpcOperation
{
    use THasJsonRpcRequest;
    use THasJsonRpcResponse;

    public function __invoke(
        IJsonRpcOperation $operation,
        string $endpoint,
        ResponseInterface $response
    ): ResponseInterface
    {
        $this->snuff()->create(new SnuffItem([
            'name' => 'after.endpoint'
        ]));

        return $response;
    }
}
