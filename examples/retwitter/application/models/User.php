<?php

class User extends Rediska_Key
{
    public function __construct($id = null)
    {
        return parent::__construct(self::_getKey($id));
    }

    public static function fetchNextId()
    {
        $key = new Rediska_Key('users:nextId');
        return $key->increment();
    }
    
    public static function setLoginToIdLink($login, $id)
    {
        $key = new Rediska_Key('userIdKey:' . $login);
        $key->setValue($id);
    }
    
    public static function getMultiple($ids = array()) 
    {
        if (!empty($ids)) {
            foreach ($ids as &$id) {
                $id = self::_getKey($id);
            }
            
            $rediska = Rediska::getDefaultInstance();
            return $rediska->get($ids);
        }
        
        return array();
    }
    
    protected function _getKey($id) 
    {
        return 'users:' . $id;
    }
}