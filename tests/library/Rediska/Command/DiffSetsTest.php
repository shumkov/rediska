<?php

class Rediska_Command_DiffSetsTest extends Rediska_TestCase
{
    public function testDiff()
    {
        $keys = $this->_createThreeSets();

        $diff = $this->rediska->diffSets($keys);
        sort($diff);
        
        $this->assertEquals($this->_diffViaPhp(), $diff);
    }
    
    public function testDiffWithManyConnection()
    {
        $this->_addSecondServerOrSkipTest();
        
        $this->testDiff();
    }
    
    public function testDiffAndSave()
    {
        $keys = $this->_createThreeSets();
        
        $reply = $this->rediska->diffSets($keys, 'new-set');
        $this->assertTrue($reply);

        $diff = $this->rediska->getSet('new-set');
        sort($diff);

        $this->assertEquals($this->_diffViaPhp(), $diff);
    }
    
    protected function _diffViaPhp()
    {
        $myDiff = array_diff($this->_sets['set1'], $this->_sets['set2'], $this->_sets['set3']);
        sort($myDiff);

        return $myDiff;
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