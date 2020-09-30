<?php
namespace extas\interfaces\stages;

/**
 * Interface IStageApiJsonRpcDescribe
 *
 * @package extas\interfaces\stages
 * @author jeyroik <jeyroik@gmail.com>
 */
interface IStageApiJsonRpcDescribe
{
    public const NAME = 'extas.api.jsonrpc.describe';

    /**
     * @param array $result
     * @return array
     */
    public function __invoke(array $result): array;
}
