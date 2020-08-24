<?php
namespace tests\api;

use Dotenv\Dotenv;
use extas\components\api\App;
use extas\components\console\TSnuffConsole;
use extas\components\http\TSnuffHttp;
use extas\components\items\SnuffItem;
use extas\components\operations\JsonRpcOperation;
use extas\components\packages\Initializer;
use extas\components\packages\Installer;
use extas\components\plugins\api\PluginJsonRpcApi;
use extas\components\plugins\api\PluginJsonRpcDescribe;
use extas\components\plugins\init\Init;
use extas\components\plugins\init\InitItem;
use extas\components\plugins\init\InitSection;
use extas\components\plugins\install\InstallItem;
use extas\components\plugins\install\InstallPackage;
use extas\components\plugins\jsonrpc\ApiJsonRpc;
use extas\components\plugins\jsonrpc\Describe;
use extas\components\plugins\TSnuffPlugins;
use extas\components\protocols\Protocol;
use extas\components\protocols\ProtocolRepository;
use extas\components\repositories\TSnuffRepositoryDynamic;
use extas\components\THasMagicClass;
use extas\interfaces\stages\IStageAfterJsonRpcOperation;
use extas\interfaces\stages\IStageBeforeJsonRpcOperation;
use PHPUnit\Framework\TestCase;
use tests\api\misc\AfterAll;
use tests\api\misc\AfterEndpoint;
use tests\api\misc\BeforeAll;
use tests\api\misc\BeforeEndpoint;
use tests\api\misc\JsonRpcOperationsIndex;

/**
 * Class JsonRpcTest
 *
 * @package tests\api
 * @author jeyroik <jeyroik@gmail.com>
 */
class JsonRpcTest extends TestCase
{
    use TSnuffHttp;
    use TSnuffConsole;
    use TSnuffRepositoryDynamic;
    use THasMagicClass;
    use TSnuffPlugins;

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
    }

    protected function tearDown(): void
    {
        $this->deleteSnuffDynamicRepositories();
        $this->deleteSnuffPlugins();
    }

    public function testApiJsonRpc()
    {
        $this->installPackage();
        $dispatcher = $this->getDispatcher('/api/jsonrpc[/{version}]');

        $this->assertNotEmpty(
            $dispatcher,
            'can not find route /api/jsonrpc[/{version}]'
        );

        $response = $dispatcher(
           $this->getPsrRequest('.api.jsonrpc', [], '', 'POST', '/api/jsonrpc'),
           $this->getPsrResponse(),
           [
               'version' => 1
           ]
        );

        $this->assertNotEmpty($response, 'Missed response');

        $jsonRpcResponse = $this->getJsonRpcResponse($response);
        $this->assertEquals(
            $this->getJsonRpcSuccess([
               [
                   JsonRpcOperation::FIELD__NAME => 'jsonrpc.operation.index',
                   JsonRpcOperation::FIELD__CLASS => JsonRpcOperationsIndex::class,
                   JsonRpcOperation::FILED__VERSION => 1,
                   JsonRpcOperation::FIELD__SPECS => ['specs']
               ]
            ]),
            $jsonRpcResponse,
            'Current response: ' . print_r($jsonRpcResponse, true) . PHP_EOL
        );
    }

    public function testMissedOperation()
    {
        $this->installPackage();
        $dispatcher = $this->getDispatcher('/api/jsonrpc[/{version}]');

        $this->assertNotEmpty(
            $dispatcher,
            'can not find route /api/jsonrpc[/{version}]'
        );

        $response = $dispatcher(
            $this->getPsrRequest('.missed.operation', [], '', 'POST', '/api/jsonrpc'),
            $this->getPsrResponse(),
            [
                'version' => 1
            ]
        );

        $this->assertNotEmpty($response, 'Missed response');

        $jsonRpcResponse = $this->getJsonRpcResponse($response);
        $this->assertEquals(
            $this->getJsonRpcError('Missed or unknown json rpc operation "unknown"', 404),
            $jsonRpcResponse,
            'Current response: ' . print_r($jsonRpcResponse, true) . PHP_EOL
        );
    }

    public function testStagesBeforeAndAfter()
    {
        $this->createSnuffPlugin(BeforeAll::class, [IStageBeforeJsonRpcOperation::NAME]);
        $this->createSnuffPlugin(BeforeEndpoint::class, [IStageBeforeJsonRpcOperation::NAME . '.api/jsonrpc']);
        $this->createSnuffPlugin(AfterAll::class, [IStageAfterJsonRpcOperation::NAME]);
        $this->createSnuffPlugin(AfterEndpoint::class, [IStageAfterJsonRpcOperation::NAME . '.api/jsonrpc']);

        $this->installPackage();
        $dispatcher = $this->getDispatcher('/api/jsonrpc[/{version}]');

        $this->assertNotEmpty(
            $dispatcher,
            'can not find route /api/jsonrpc[/{version}]'
        );

        $response = $dispatcher(
            $this->getPsrRequest('.api.jsonrpc', [], '', 'POST', '/api/jsonrpc'),
            $this->getPsrResponse(),
            [
                'version' => 1
            ]
        );

        $this->assertNotEmpty($response, 'Missed response');

        $records = $this->getMagicClass('snuff')->all([]);
        $this->assertCount(4, $records, 'Current records: ' . print_r($records, true));
    }

    public function testDescribe()
    {
        $this->installPackage();
        $dispatcher = $this->getDispatcher('/_describe[/{version}]');

        $this->assertNotEmpty(
            $dispatcher,
            'can not find route /_describe[/{version}]'
        );

        $response = $dispatcher(
            $this->getPsrRequest('.api.jsonrpc', [], '', 'POST', '/_describe'),
            $this->getPsrResponse(),
            [
                'version' => 1
            ]
        );

        $this->assertNotEmpty($response, 'Missed response');

        $jsonRpcResponse = $this->getJsonRpcResponse($response);
        $this->assertEquals(
            $this->getJsonRpcSuccess(['specs']),
            $jsonRpcResponse,
            'Current response: ' . print_r($jsonRpcResponse, true) . PHP_EOL
        );
    }

    public function testDescribeAll()
    {
        $this->getMagicClass('jsonRpcOperations')->create(new JsonRpcOperation([
            JsonRpcOperation::FIELD__NAME => Describe::OPERATION__ALL,
            JsonRpcOperation::FIELD__CLASS => JsonRpcOperationsIndex::class,
            JsonRpcOperation::FILED__VERSION => 1,
            JsonRpcOperation::FIELD__SPECS => ['specs']
        ]));

        $this->installPackage();
        $dispatcher = $this->getDispatcher('/_describe[/{version}]');

        $this->assertNotEmpty(
            $dispatcher,
            'can not find route /_describe[/{version}]'
        );

        $response = $dispatcher(
            $this->getPsrRequest('.describe.all', [], '', 'POST', '/_describe'),
            $this->getPsrResponse(),
            [
                'version' => 1
            ]
        );

        $this->assertNotEmpty($response, 'Missed response');

        $jsonRpcResponse = $this->getJsonRpcResponse($response);
        $this->assertEquals(
            $this->getJsonRpcSuccess([
                'jsonrpc.operation.index' => ['specs'],
                Describe::OPERATION__ALL => ['specs'],
            ]),
            $jsonRpcResponse,
            'Current response: ' . print_r($jsonRpcResponse, true) . PHP_EOL
        );
    }

    protected function getJsonRpcError(
        string $message,
        int $code,
        string $id = '2f5d0719-5b82-4280-9b3b-10f23aff226b'
    ): array
    {
        return [
            'id' => $id,
            'jsonrpc' => '2.0',
            'error' => [
                'message' => $message,
                'code' => $code,
                'data' => []
            ]
        ];
    }

    /**
     * @param array $result
     * @param string $id
     * @return array
     */
    protected function getJsonRpcSuccess(array $result, string $id = '2f5d0719-5b82-4280-9b3b-10f23aff226b'): array
    {
        return [
            'id' => $id,
            'jsonrpc' => '2.0',
            'result' => $result
        ];
    }

    protected function getDispatcher(string $pattern): callable
    {
        $app = App::create();
        $routes = $app->getRouteCollector()->getRoutes();
        $dispatcher = null;
        foreach ($routes as $route) {
            if ($route->getPattern() == $pattern) {
                $dispatcher = $route->getCallable();
                break;
            }
        }

        return $dispatcher;
    }

    protected function installPackage(): void
    {
        $this->createSnuffPlugin(PluginJsonRpcApi::class, ['extas.api.app.init']);
        $this->createSnuffPlugin(PluginJsonRpcDescribe::class, ['extas.api.app.init']);

        $this->createSnuffPlugin(ApiJsonRpc::class, ['extas.jsonrpc.operation.run.api/jsonrpc']);
        $this->createSnuffPlugin(Describe::class, ['extas.jsonrpc.operation.run._describe']);

        $this->getMagicClass('jsonRpcOperations')->create(new JsonRpcOperation([
            JsonRpcOperation::FIELD__NAME => 'jsonrpc.operation.index',
            JsonRpcOperation::FIELD__CLASS => JsonRpcOperationsIndex::class,
            JsonRpcOperation::FILED__VERSION => 1,
            JsonRpcOperation::FIELD__SPECS => ['specs']
        ]));
    }
}
