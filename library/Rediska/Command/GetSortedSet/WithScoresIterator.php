<?php

class Rediska_Command_GetSortedSet_WithScoresIterator extends Rediska_Connection_Exec_MultiBulkIterator
{
    public function current()
    {
        if ($this->_count === null || $this->_count == 0) {
            throw new Rediska_Connection_Exception('call valid before');
        }

        $value = Rediska_Connection_Exec::readResponseFromConnection($this->_connection);

        parent::next();
        parent::valid();

        $score = Rediska_Connection_Exec::readResponseFromConnection($this->_connection);

        $response = array($value, $score);

        if ($this->_callback !== null) {
            $response = call_user_func($this->_callback, $response);
        }

        return $response;
    }

    public function count()
    {
        $count = parent::count();

        if ($count === 0) {
            return 0;
        }

        return $count / 2;
    }
}