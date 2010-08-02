<?php

class Post extends Rediska_Key
{
    public function __construct($id = null)
    {
        return parent::__construct(self::_getKey($id));
    }

    public static function fetchNextId()
    {
        $key = new Rediska_Key('posts:nextId');
        return $key->increment();
    }
    
    protected function _getKey($id) 
    {
        return 'posts:' . $id;
    }
    
}