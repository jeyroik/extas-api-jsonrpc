<?php
namespace tests\api;

use Dotenv\Dotenv;
use extas\components\api\App;
use extas\components\console\TSnuffConsole;
use extas\components\http\TSnuffHttp;
use extas\components\operations\JsonRpcOperation;
use extas\components\packages\Initializer;
use extas\components\packages\Installer;
use extas\components\plugins\api\PluginJsonRpcApi;
use extas\components\plugins\init\Init;
use extas\components\plugins\init\InitItem;
use extas\components\plugins\init\InitSection;
use extas\components\plugins\install\InstallItem;
use extas\components\plugins\install\InstallPackage;
use extas\components\plugins\jsonrpc\ApiJsonRpc;
use extas\components\plugins\TSnuffPlugins;
use extas\components\protocols\Protocol;
use extas\components\protocols\ProtocolRepository;
use extas\components\repositories\TSnuffRepositoryDynamic;
use extas\components\THasMagicClass;
use PHPUnit\Framework\TestCase;
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
        ]);
    }

    protected function tearDown(): void
    {
        $this->deleteSnuffDynamicRepositories();
        $this->deleteSnuffPlugins();
    }

    public function testApiJsonRpc()
    {
        $output = $this->getOutput(true);
        $this->installPackage($output);

        $app = App::create();
        $routes = $app->getRouteCollector()->getRoutes();
        $dispatcher = null;
        foreach ($routes as $route) {
           if ($route->getPattern() == '/api/jsonrpc[/{version}]') {
               $dispatcher = $route->getCallable();
               break;
           }
        }

        $outputText = $output->fetch();

        $this->assertNotEmpty(
            $dispatcher,
            'can not find route /api/jsonrpc[/{version}], output: ' . $outputText
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
           [
               'id' => '2f5d0719-5b82-4280-9b3b-10f23aff226b',
               'jsonrpc' => '2.0',
               'result' => [
                   [
                       JsonRpcOperation::FIELD__NAME => 'jsonrpc.operation.index',
                       JsonRpcOperation::FIELD__CLASS => JsonRpcOperationsIndex::class,
                       JsonRpcOperation::FILED__VERSION => 1
                   ]
               ]
           ],
           $jsonRpcResponse,
            'Current response: ' . print_r($jsonRpcResponse, true) . PHP_EOL
            . 'Output: ' . $outputText . PHP_EOL
        );
    }

    protected function installPackage($cOutput): void
    {
        $this->createSnuffPlugin(PluginJsonRpcApi::class, ['extas.api.app.init']);
        $this->createSnuffPlugin(ApiJsonRpc::class, ['extas.jsonrpc.operation.run.api/jsonrpc']);
        $this->getMagicClass('jsonRpcOperations')->create(new JsonRpcOperation([
            JsonRpcOperation::FIELD__NAME => 'jsonrpc.operation.index',
            JsonRpcOperation::FIELD__CLASS => JsonRpcOperationsIndex::class,
            JsonRpcOperation::FILED__VERSION => 1
        ]));
    }
}
