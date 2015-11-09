<?php

namespace Galilee\PPM\Tests\SDK\Chili\Manager;


use Galilee\PPM\SDK\Chili\Config\ConfigService;
use Galilee\PPM\SDK\Chili\Entity\Task;
use Galilee\PPM\SDK\Chili\Manager\Document as DocumentManager;
use Galilee\PPM\SDK\Chili\Manager\Task as TaskManager;
use Galilee\PPM\Tests\SDK\Chili\Mock\SoapCall;

class TaskTest extends \PHPUnit_Framework_TestCase {


    /**@var SoapCall $soapcall */
    private $soapCallMock = null;

    private $config;

    private $apiKey = '111111111111111';

    public function setUp(){
        parent::setUp();

        $confType = 'php_array';
        $configArr = [
            'login'       => 'login',
            'password'    => '1234',
            'wsdlUrl'     => 'http://test.wsdlurl.fr/testService?wsdl',
            'environment' => 'test',
            'privateUrl'  => 'http://private.test.fr',
            'publicUrl'   => 'http://public.test.fr',
        ];

        $configService = new ConfigService($confType, $configArr);
        $mockDirectoryPath = __DIR__ . DIRECTORY_SEPARATOR . 'data';

        $this->config = $configService->getConfig();
        $this->soapCallMock = new SoapCall($this->config, $mockDirectoryPath, $this->apiKey);
    }

    public function tearDown(){
        parent::tearDown();

        $this->soapCallMock = null;
        $this->config = null;
    }

    /**
     * Test 1 : TaskManager->getStatus(...) returns task status
     *
     */
    public function testGetStatusShouldReturnArrayWithStatusInfo()
    {
        // Set scenario name
        $this->soapCallMock->setScenarioName('ok');

        $docManager = new DocumentManager($this->config, $this->apiKey, false);
        $docManager->setSoapCall($this->soapCallMock);

        $document = $docManager->getDocument('3728e7ef-adcb-44a3-83f7-d2949edd9cbe');
        $settingsId = 'e794f36e-a0f3-450d-8ce5-bbc637056d7b';
        $exportProfile = $docManager->getDocumentSettings($settingsId);

        $task = $docManager->buildPdf($document, $exportProfile);

        $taskManager = new TaskManager($this->config, $this->apiKey, false);
        $taskManager->setSoapCall($this->soapCallMock);
        $statusInfo = $taskManager->getStatus($task);

        $this->assertNotEmpty($statusInfo);
        $this->assertArrayHasKey(Task::STATUS_FINISHED, $statusInfo);
        $this->assertArrayHasKey(Task::STATUS_SUCCEEDED, $statusInfo);
        $this->assertArrayHasKey(Task::STATUS_ERROR_MESSAGE, $statusInfo);
        $this->assertArrayHasKey(Task::STATUS_ERROR_STACK, $statusInfo);

        $this->assertTrue($statusInfo[Task::STATUS_FINISHED]);
        $this->assertTrue($statusInfo[Task::STATUS_SUCCEEDED]);
    }
}