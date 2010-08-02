<?php

class Rediska_Command_GetHashFieldsTest extends Rediska_TestCase
{
    public function testGetFieldsFromNotExists()
    {
        $fields = $this->rediska->getHashFields('test');
        $this->assertEquals(array(), $fields);
    }

    public function testGetFields()
    {
        $this->rediska->setToHash('test', array('a' => 1, 'b' => 2, 3 => 'c'));
        $fields = $this->rediska->getHashFields('test');
        $this->assertEquals(array('a', 'b', 3), $fields);
    }
}