<?php

class UserPosts extends Rediska_Key_Set
{
    public function __construct($userId)
    {
        parent::__construct('user:' . $userId . ':posts');
    }
    
    public function addPost($postId)
    {
        $this->add($postId);
    }
}