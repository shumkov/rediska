<?php

class Rediska_Command_QuitTest extends Rediska_TestCase
{
    public function testQuit()
    {
        $this->rediska->quit();
        $connections = $this->rediska->getConnections();
        $this->assertFalse($connections[0]->isConnected());
    }
}