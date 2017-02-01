<?php
/**
 * @group BitCount
 */
class Rediska_Command_BitCountTest extends Rediska_TestCase
{
    public function testBitCount()
    {
        $this->rediska->set('test','aaa');
        $reply = $this->rediska->bitCount('test');
        $this->assertEquals(9, $reply);
        $reply = $this->rediska->bitCount('test', 0, 0);
        $this->assertEquals(3, $reply);
        $reply = $this->rediska->bitCount('test', 0, 3);
        $this->assertEquals(9, $reply);
        $reply = $this->rediska->bitCount('test', 0, 12);
        $this->assertEquals(9, $reply);
        $reply = $this->rediska->bitCount('test', 4, 12);
        $this->assertEquals(0, $reply);
    }
    public function testBitCountWithStartNoEnd()
    {
        $this->rediska->set('test','aaa');
        $reply = $this->rediska->bitCount('test', 0);
        $this->assertEquals(3, $reply);
        $reply = $this->rediska->bitCount('test', 4);
        $this->assertEquals(0, $reply);
    }
}
