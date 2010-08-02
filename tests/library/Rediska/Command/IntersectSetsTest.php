<?php

class Rediska_Command_IntersectSetsTest extends Rediska_TestCase
{
    public function testIntersect()
    {      
        $keys = $this->_createThreeSets();

        $intersection = $this->rediska->intersectSets($keys);
        sort($intersection);
        
        $this->assertEquals($this->_intersectViaPhp(), $intersection);
    }
    
    public function testIntersectWithManyConnection()
    {
        $this->_addSecondServerOrSkipTest();
        
        $this->testIntersect();
    }

    public function testIntersetAndSave()
    {
        $keys = $this->_createThreeSets();

        $reply = $this->rediska->intersectSets($keys, 'new-set');
        $this->assertTrue($reply);

        $intersection = $this->rediska->getSet('new-set');
        sort($intersection);

        $this->assertEquals($this->_intersectViaPhp(), $intersection);
    }

    public function testIntersetAndSaveWithManyConnection()
    {
        $this->_addSecondServerOrSkipTest();
        
        $this->testIntersetAndSave();
    }

    protected function _intersectViaPhp()
    {
        $myIntersection = array_intersect($this->_sets['set1'], $this->_sets['set2'], $this->_sets['set2']);
        sort($myIntersection);

        return $myIntersection;
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