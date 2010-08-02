<?php

class Feed extends Rediska_Key_List
{
    public function __construct($userId = null)
    {
        return parent::__construct(self::_getKey($userId));
    }

    protected function _getKey($id) 
    {
        return 'user:' . $id . ':feed';
    }
}