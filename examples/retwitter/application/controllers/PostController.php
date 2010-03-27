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
        	
        	$postData = $form->toArray();
            $postData['id'] = Post::fetchNextId();
            $postData['userId'] = $currentUser['id'];
            
            // save post
            $post = new Post($postData['id']);
            $post->setValue($form->toArray());
            
            // save post in the follower feeds
            $followers = new Followers($currentUser['id']);
            foreach ($followers as $followerId) {
            	$feed = new Feed($followerId);
            	$feed->prepend($postData['id']);
            }
            
            $this->_redirect('/');
        }
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
    		$this->view->posts[] = $post->getValue();
    	}
    }
}