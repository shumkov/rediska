<?php

/**
 * @group Bitop
 */
class Rediska_Command_BitopTest extends Rediska_TestCase
{
    public function testBitopAnd()
    {
        $this->rediska->set('testa', 4);
        $this->rediska->set('testb', 5);

        $this->rediska->bitop('AND', 'result', array('testa', 'testb'));

        $reply = $this->rediska->get('result');

        $this->assertEquals(4, $reply);
    }

    public function testBitopXor()
    {
        $this->rediska->set('testa', 4);
        $this->rediska->set('testb', 5);

        $this->rediska->bitop('XOR', 'result', array('testa', 'testb'));

        $reply = $this->rediska->get('result');
        $reply = array_shift(unpack("C*", $reply));

        $this->assertEquals(1, $reply);
    }

    public function testBitopOr()
    {
        $this->rediska->set('testa', 4);
        $this->rediska->set('testb', 5);

        $this->rediska->bitop('OR', 'result', array('testa', 'testb'));

        $reply = $this->rediska->get('result');

        $this->assertEquals(5, $reply);
    }

    public function testBitopNot()
    {
        $this->rediska->set('testa', 1);
        $this->rediska->bitop('NOT', 'testb', 'testa');

        $reply = $this->rediska->get('testb');
        $reply = array_shift(unpack("C*", $reply));

        $this->assertEquals(206, $reply);
    }
}
