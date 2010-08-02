<?php

class Following extends Rediska_Key_Set
{
    public function __construct($userId)
    {
        parent::__construct('user:' . $userId . ':following');
    }
}