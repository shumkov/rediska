<?php

class Rediska_Command_UnionSetsTest extends Rediska_TestCase
{
    public function testUnion()
    {
        $keys = $this->_createThreeSets();

        $union = $this->rediska->unionSets($keys);
        sort($union);

        $this->assertEquals($this->_unionViaPhp(), $union);
    }

    public function testUnionWithManyConnection()
    {
        $this->_addSecondServerOrSkipTest();
        
        $this->testUnion();
    }

    public function testUnionAndSave()
    {
        $keys = $this->_createThreeSets();
        
        $reply = $this->rediska->unionSets($keys, 'new-set');
        $this->assertTrue($reply);
        
        $union = $this->rediska->getSet('new-set');
        sort($union);
        $this->assertEquals($this->_unionViaPhp(), $union);
    }

    public function testUnionAndSaveWithManyConnection()
    {
        $this->_addSecondServerOrSkipTest();
        
        $this->testUnionAndSave();
    }

    protected function _unionViaPhp()
    {
        $myUnion = array_unique(array_merge($this->_sets['set1'], $this->_sets['set2'], $this->_sets['set3']));
        
        sort($myUnion);

        return $myUnion;
    }

    protected $_sets = array(
        'set1' => array(1, 3, 6, 4, 2, 'fds', 312, array('1a', 1, 2)),
        'set2' => array(1, 5, 3, 7, 'aaa', 534),
        'set3' => array('asdas', 1, 6, 3, 'y', 'aaa', 4)
    );

    protected function _createThreeSets()
    {
        foreach($this->_sets as $key => $values) {
            foreach($values as $value) {
                $this->rediska->addToSet($key, $value);
            }
        }
        
        return array_keys($this->_sets);
    }
}