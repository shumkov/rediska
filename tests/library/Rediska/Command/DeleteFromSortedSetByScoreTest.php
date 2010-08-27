<?php

/**
 * Remove all the elements in the sorted set at key with a score between min and max (including elements with score equal to min or max).
 * 
 * @param string  $name  Key name
 * @param numeric $min   Min value
 * @param numeric $max   Max value
 * @return integer
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_DeleteFromSortedSetByScoreTest extends Rediska_TestCase
{
    public function testDeleteFromNotExistsSetReturnFalse()
    {
        $reply = $this->rediska->deleteFromSortedSetByScore('test', 0, 1);
        $this->assertEquals(0, $reply);
    }
    
    public function testDeleteFromSortedSetByScore()
    {
        $this->rediska->addToSortedSet('test', 'aaa', 1);
        $this->rediska->addToSortedSet('test', 'bbb', 2);
        $this->rediska->addToSortedSet('test', 'ccc', 3);
        $this->rediska->addToSortedSet('test', 'ddd', 4);

        $this->rediska->deleteFromSortedSetByScore('test', 2, 3);

        $values = $this->rediska->getSortedSet('test');
        $this->assertContains('aaa', $values);
        $this->assertNotContains('bbb', $values);
        $this->assertNotContains('ccc', $values);
        $this->assertContains('ddd', $values);
    }
}