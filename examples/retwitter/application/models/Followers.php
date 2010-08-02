<?php

class Followers extends Rediska_Key_Set
{
    public function __construct($userId)
    {
        parent::__construct('user:' . $userId . ':followers');
    }
}