<?php

require_once 'Rediska.php';

// Abstract test class
require_once 'PHPUnit/Framework/TestCase.php';

class Rediska_TestCase extends PHPUnit_Framework_TestCase
{
    /**
     * @var Rediska
     */
    protected $rediska;

    protected function setUp()
    {
        $this->rediska = new Rediska(array('namespace' => 'Rediska_Tests_', 'servers' => array(array('host' => REDISKA_HOST, 'port' => REDISKA_PORT))));
    }

    protected function tearDown()
    {
        $this->rediska->flushDb(true);
        $this->rediska = null;
    }

    protected function _addSecondServerOrSkipTest()
    {
        $socket = @fsockopen(REDISKA_SECOND_HOST, REDISKA_SECOND_PORT);

        if (is_resource($socket)) {
            @fclose($socket);
            $this->rediska->addServer(REDISKA_SECOND_HOST, REDISKA_SECOND_PORT, array('persistent' => true));
        } else {
            $this->markTestSkipped("You must start server " . REDISKA_SECOND_HOST . ":" . REDISKA_SECOND_PORT . " before run test");
        }
    }
}