<?php

class PostController extends Zend_Controller_Action
{
    public function init()
    {
        parent::init();
        
        $auth = Zend_Auth::getInstance();
        if (!$auth->hasIdentity()) {
            throw new Zend_Auth_Exception("You're not authorized to see this page");
        }
    }
    
    /**
     * Create new post
     */
    public function newAction()
    {
        $currentUser = Zend_Auth::getInstance()->getStorage()->read();
        
        $form = new Form_Post;
        
        if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
            
            $postData = $form->getValues();
            $postData['id'] = Post::fetchNextId();
            $postData['userId'] = $currentUser['id'];
            
            // save post
            $post = new Post($postData['id']);
            $post->setValue($postData);
            
            $userPosts = new UserPosts($currentUser['id']);
            $userPosts->add($postData['id']);
            
            // save post in the follower feeds
            $followers = new Followers($currentUser['id']);
            foreach ($followers as $followerId) {
                $feed = new Feed($followerId);
                $feed->prepend($postData['id']);
            }
            
            $this->_redirect('/post/my');
        }
        
        $this->view->form = $form;
    }
    
    /**
     * Read your posts
     */
    public function indexAction()
    {
        $currentUser = Zend_Auth::getInstance()->getStorage()->read();
        
        $feed = new Feed($currentUser['id']);
        
        $this->view->posts = array();
        
        // just for example, better use multiget
        foreach ($feed as $postId) {
            $post = new Post($postId);
            $postData = $post->getValue();
            
            $user = new User($postData['userId']);
            $userData = $user->getValue();
            
            $this->view->posts[] = array('post' => $postData, 'user' => $userData);
        }
    }
    
    public function myAction()
    {
        $currentUser = Zend_Auth::getInstance()->getStorage()->read();
        
        $userPosts = new UserPosts($currentUser['id']);
        
        $this->view->posts = array();
        
        foreach ($userPosts as $postId) {
            $post = new Post($postId);
            $postData = $post->getValue();
            
            $user = new User($postData['userId']);
            $userData = $user->getValue();
            
            $this->view->posts[] = array('post' => $postData, 'user' => $userData);
        }
    }
}