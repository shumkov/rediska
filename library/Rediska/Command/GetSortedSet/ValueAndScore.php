<?php

class Rediska_Command_GetSortedSet_ValueAndScore extends ArrayObject
{
    public function __set($name, $value)
    {
        $this[$name] = $value;
    }

    public function __get($name)
    {
        return $this[$name];
    }

    public static function combine($valuesAndScores)
    {
        $isValue = true;
        $valuesWithScores = array();
        foreach($valuesAndScores as $valueOrScore) {
            if ($isValue) {
                $value = $this->_rediska->unserialize($valueOrScore);
            } else {
                $score = $valueOrScore;
                $valuesWithScores[] = new self(array('value' => $value, 'score' => $score));
            }

            $isValue = !$isValue;
        }

        return $valuesWithScores;
    }
}