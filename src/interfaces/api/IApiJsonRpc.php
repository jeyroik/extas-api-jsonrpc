<?php
namespace extas\interfaces\api;

use extas\interfaces\http\IHasJsonRpcRequest;
use extas\interfaces\http\IHasJsonRpcResponse;
use extas\interfaces\IItem;

interface IApiJsonRpc extends IItem, IHasJsonRpcRequest, IHasJsonRpcResponse
{
    public const SUBJECT = 'extas.api.jsonrpc';

    public const FIELD__ENDPOINT = 'endpoint';

    public function getEndpoint(): string;
}
