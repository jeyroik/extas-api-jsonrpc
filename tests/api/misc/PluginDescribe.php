<?php
namespace tests\api\misc;

use extas\components\plugins\Plugin;
use extas\interfaces\stages\IStageApiJsonRpcDescribe;

/**
 * Class PluginDescribe
 *
 * @package tests\api\misc
 * @author jeyroik <jeyroik@gmail.com>
 */
class PluginDescribe extends Plugin implements IStageApiJsonRpcDescribe
{
    /**
     * @param array $result
     * @return array
     */
    public function __invoke(array $result): array
    {
        foreach ($result as $index => $item) {
            $result[$index] = [
                'source' => $item
            ];
        }

        return $result;
    }
}
