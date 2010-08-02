<?php

class Rediska_Key_HashTest extends Rediska_TestCase
{
    /**
     * @var Rediska_Key_Hash
     */
    private $hash;

    protected function setUp()
    {
        parent::setUp();
        $this->hash = new Rediska_Key_Hash('test');
    }

    public function testSet()
    {
        $reply = $this->hash->set('a', '1');
        $this->assertTrue($reply);

        $reply = $this->rediska->getFromHash('test', 'a');
        $this->assertEquals('1', $reply);
    }

    public function testMultiSet()
    {
        $reply = $this->hash->set(array('a' => '1', 'b' => 2));
        $this->assertTrue($reply);

        $reply = $this->rediska->getFromHash('test', array('a', 'b'));
        $this->assertEquals(array('a' => '1', 'b' => 2), $reply);
    }

    public function testMagicSet()
    {
        $reply = $this->hash->a = '1';
        $this->assertEquals('1', $reply);

        $reply = $this->rediska->getFromHash('test', 'a');
        $this->assertEquals('1', $reply);
    }

    public function testOffsetSet()
    {
        $reply = $this->hash['a'] = '1';
         $this->assertEquals('1', $reply);

        $reply = $this->rediska->getFromHash('test', 'a');
        $this->assertEquals('1', $reply);
    }

    public function testGet()
    {
        $this->rediska->setToHash('test', 'a', 1);

        $reply = $this->hash->get('a');
        $this->assertEquals('1', $reply);
    }

    public function testMultiGet()
    {
        $this->rediska->setToHash('test', array('a' => 1, 'b' => 2));

        $reply = $this->hash->get(array('a', 'b'));
        $this->assertEquals(array('a' => 1, 'b' => 2), $reply);
    }

    public function testMagicGet()
    {
        $this->rediska->setToHash('test', 'a', 1);

        $reply = $this->hash->a;
        $this->assertEquals('1', $reply);
    }

    public function testOffsetGet()
    {
        $this->rediska->setToHash('test', 'a', 1);

        $reply = $this->hash['a'];
        $this->assertEquals('1', $reply);
    }

    public function testIncrement()
    {
        $reply = $this->hash->increment('a');
        $this->assertEquals(1, $reply);

        $reply = $this->hash->increment('a');
        $this->assertEquals(2, $reply);

        $reply = $this->hash->increment('a', 8);
        $this->assertEquals(10, $reply);

        $reply = $this->hash->get('a');
        $this->assertEquals(10, $reply);
    }

    public function testExists()
    {
        $reply = $this->hash->exists('a');
        $this->assertFalse($reply);

        $this->rediska->setToHash('test', 'a', 1);

        $reply = $this->hash->exists('a');
        $this->assertTrue($reply);
    }

    public function testMagicExists()
    {
        $reply = isset($this->hash->a);
        $this->assertFalse($reply);

        $this->rediska->setToHash('test', 'a', 1);

        $reply = isset($this->hash->a);
        $this->assertTrue($reply);
    }

    public function testOffsetExists()
    {
        $reply = isset($this->hash['a']);
        $this->assertFalse($reply);

        $this->rediska->setToHash('test', 'a', 1);

        $reply = isset($this->hash['a']);
        $this->assertTrue($reply);
    }

    public function testRemove()
    {
        $this->rediska->setToHash('test', 'a', 1);

        $reply = $this->hash->remove('a');
        $this->assertTrue($reply);

        $reply = $this->rediska->getFromHash('test', 'a');
        $this->assertNull($reply);
    }

    public function testMagicRemove()
    {
        $this->rediska->setToHash('test', 'a', 1);

        unset($this->hash->a);

        $reply = $this->rediska->getFromHash('test', 'a');
        $this->assertNull($reply);
    }

    public function testOffsetUnset()
    {
        $this->rediska->setToHash('test', 'a', 1);

        unset($this->hash['a']);

        $reply = $this->rediska->getFromHash('test', 'a');
        $this->assertNull($reply);
    }

    public function testGetFields()
    {
        $this->rediska->setToHash('test', array('a' => '1', 'b' => 2));

        $reply = $this->hash->getFields();
        $this->assertEquals(array('a', 'b'), $reply);
    }

    public function testGetValues()
    {
        $this->rediska->setToHash('test', array('a' => '1', 'b' => 2));

        $reply = $this->hash->getValues();
        $this->assertEquals(array('1', 2), $reply);
    }

    public function testToArray()
    {
        $this->rediska->setToHash('test', array('a' => 1, 'b' => 2));

        $test = $this->hash->toArray();
        $this->assertEquals(array('a' => 1, 'b' => 2), $test);
    }

    public function testCount()
    {
        $this->rediska->setToHash('test', array('a' => 1, 'b' => 2));

        $this->assertEquals(2, $this->hash->count());
        $this->assertEquals(2, count($this->hash));
    }

    public function testGetIterator()
    {
        $this->rediska->setToHash('test', array('a' => 1, 'b' => 2));

        $test = array();
        foreach($this->hash as $field => $value) {
            $test[$field] = $value;
        }
        $this->assertEquals(array('a' => 1, 'b' => 2), $test);
    }
}