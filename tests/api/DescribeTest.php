<?php
namespace tests\api;

use Dotenv\Dotenv;
use extas\components\http\TSnuffHttp;
use extas\components\items\SnuffItem;
use extas\components\operations\JsonRpcOperation;
use extas\components\plugins\jsonrpc\Describe;
use extas\components\plugins\TSnuffPlugins;
use extas\components\protocols\Protocol;
use extas\components\repositories\TSnuffRepositoryDynamic;
use extas\components\THasMagicClass;
use extas\interfaces\http\IHasHttpIO;
use extas\interfaces\samples\parameters\ISampleParameter;
use extas\interfaces\stages\IStageApiJsonRpcDescribe;
use PHPUnit\Framework\TestCase;
use tests\api\misc\PluginDescribe;

/**
 * Class DescribeTest
 *
 * @package tests\api
 * @author jeyroik <jeyroik@gmail.com>
 */
class DescribeTest extends TestCase
{
    use TSnuffHttp;
    use TSnuffRepositoryDynamic;
    use TSnuffPlugins;
    use THasMagicClass;

    protected function setUp(): void
    {
        parent::setUp();
        $env = Dotenv::create(getcwd() . '/tests/');
        $env->load();

        $this->createSnuffDynamicRepositories([
            ['jsonRpcOperations', 'name', JsonRpcOperation::class],
            ['protocols', 'name', Protocol::class],
            ['snuff', 'name', SnuffItem::class]
        ]);
        $this->getMagicClass('jsonRpcOperations')->create(new JsonRpcOperation([
            JsonRpcOperation::FIELD__SPECS => [
                'request' => [],
                'response' => []
            ],
            JsonRpcOperation::FIELD__NAME => 'test'
        ]));
    }

    protected function tearDown(): void
    {
        $this->deleteSnuffDynamicRepositories();
        $this->deleteSnuffPlugins();
    }

    public function testPlugin()
    {
        $this->createSnuffPlugin(PluginDescribe::class, [IStageApiJsonRpcDescribe::NAME]);

        $plugin = new Describe([
            IHasHttpIO::FIELD__PSR_REQUEST => $this->getPsrRequest('.api.jsonrpc'),
            IHasHttpIO::FIELD__PSR_RESPONSE => $this->getPsrResponse(),
            IHasHttpIO::FIELD__ARGUMENTS => [
                'version' => 0
            ],
            Describe::FIELD__PARAMETERS => [
                Describe::PARAM__OPERATION_ALL => [
                    ISampleParameter::FIELD__NAME => Describe::PARAM__OPERATION_ALL,
                    ISampleParameter::FIELD__VALUE => ['test.all']
                ]
            ]
        ]);

        $operation = new JsonRpcOperation([
            JsonRpcOperation::FIELD__NAME => 'test.all'
        ]);

        $response = $plugin($operation, '_describe');

        $jsonrpcResponse = $this->getJsonRpcResponse($response);
        $this->assertEquals(
            [
                'id' => '2f5d0719-5b82-4280-9b3b-10f23aff226b',
                'jsonrpc' => '2.0',
                'result' => [
                    'test' => [
                        'source' => [
                            'request' => [],
                            'response' => []
                        ]
                    ]
                ]
            ],
            $jsonrpcResponse,
            'Incorrect response: ' . print_r($jsonrpcResponse, true)
        );
    }
}
