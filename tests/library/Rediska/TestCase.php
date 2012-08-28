<?php

class Rediska_TestCase extends PHPUnit_Framework_TestCase
{
    /**
     * @var Rediska
     */
    protected $rediska;

    protected function setUp()
    {
        $this->rediska = new Rediska($GLOBALS['rediskaConfigs'][0]);
    }

    protected function tearDown()
    {
        if ($this->rediska !== null) {
            $this->rediska->flushDb(true);
            foreach($this->rediska->getConnections() as $connection) {
                $connection->disconnect();
            }
            $this->rediska = null;
        }
    }

    protected function _addSecondServerOrSkipTest()
    {
        if (isset($GLOBALS['rediskaConfigs'][1])) {
            $config = $GLOBALS['rediskaConfigs'][1];
            $socket = @fsockopen($config['servers'][1]['host'], $config['servers'][1]['port']);

            if (is_resource($socket)) {
                @fclose($socket);
                $this->rediska = new Rediska($config);

                return true;
            }
        }

        $this->markTestSkipped("You must add to config.ini and start second redis server before run test");

        return false;
    }
}