<?php

class Rediska_Command_UnionSortedSetsTest extends Rediska_TestCase
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

    protected function _compareSets($weights = array(), $aggregation = 'sum')
    {
        if (empty($weights)) {
            $weights = array(
                'set1' => 1,
                'set2' => 1,
                'set3' => 1,
            );
        }
         
        $values = array();
        $valuesWithScores = array();
        foreach (array('set1', 'set2', 'set3') as $name) {
            $values[$name] = array();
            foreach ($this->_sets[$name] as $value => $score) {
                $values[$name][] = $value;
                if (isset($valuesWithScores[$value])) {
                    $valuesWithScores[$value] = array();
                }
                $valuesWithScores[$value][] = $score * $weights[$name];
            }
        }
        
        $comparedValues = array();
        foreach($values as $name => $value) {
            $value = array_merge($comparedValues, $value);
        }
        $comparedValues = array_unique($comparedValues);

        foreach($comparedValues as &$value) {
            $scores = $valuesWithScores[$value];
            switch ($aggregation) {
                case self::SUM:
                    $score = array_sum($scores);
                    break;
                case self::MIN:
                    $score = min($scores);
                    break;
                case self::MAX:
                    $score = max($scores);
                    break;
                default:
                    throw new Exception('Unknown aggregation method ' . $aggregation);
            }

            $value = $this->_rediska->unserialize($value);
        }
        
        return $comparedValues;
    }

    protected $_sets = array(
        'set1' => array(1 => 3, 3 => 1, 6 => 431, 4 => 1, 2 => 53, 'fds' => 2),
        'set2' => array(1 => 123, 'aaa' => 143, 534 => 132),
        'set3' => array('asdas' => 12, 1 => 1, 'aaa' => 13, 4 => 1.1)
    );

    protected function _createThreeSets()
    {
        foreach($this->_sets as $key => $values) {
            foreach($values as $value => $weight) {
                $this->rediska->addToSortedSet($key, $value, $weight);
            }
        }

        return array_keys($this->_sets);
    }
}