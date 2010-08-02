<?php

class UserController extends Zend_Controller_Action
{
    /**
     * Signup
     */
    public function signupAction()
    {
        $form = new Form_User;
        
        if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
            
            $userData = $form->getValues();
            $userData['id'] = User::fetchNextId();
            
            // save user
            $user = new User($userData['id']);
            $user->setValue($userData);
            
            $users = new Users();
            $users->add($userData['id']);
            
            // save login to id link
            User::setLoginToIdLink($userData['login'], $userData['id']);
            
            $this->_redirect('/user/login');
        }
        
        $this->view->form = $form;
    }
    
    /**
     * Login
     */
    public function loginAction()
    {
        $form = new Form_UserLogin;
        
        if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
            $auth = Zend_Auth::getInstance();

            $options = array(
                'userIdKey'   => 'userIdKey:*',
                'userDataKey' => 'users:*',
                'userDataIsArray' => true
            );
            $adapter = new Rediska_Zend_Auth_Adapter_Redis($options);

            // Set login and password
            $adapter->setIdentity($form->getElement('login')->getValue())
                    ->setCredential($form->getElement('password')->getValue());
                    
            // Authorization
            $result = $auth->authenticate($adapter);
            if ($result->isValid()) {
                $userData = $adapter->getResultUserData();

                $session = new Zend_Session_Namespace('Zend_Auth');
                
                $storage = $auth->getStorage();
                $storage->write($userData);
                
                $this->_redirect('/post/index/');
            } else {
                $form->getElement('login')->addError('Wrong login/password combination');
            }
        }
        
        $this->view->form = $form;
    }
    
    /**
     * Users who follow given userId
     */
    public function followersAction()
    {
        $userId = $this->_getParam('userId');
        
        $user = new User($userId);
        $this->view->user = $user->getValue();
        
        $followers = new Followers($userId);
        
        $this->view->users = User::getMultiple($followers->toArray());
        $this->_setUsersIFollow();
    }
    
    /**
     * Users who are followed by given userId
     */
    public function followingAction()
    {
        $userId = $this->_getParam('userId');
        
        $following = new Following($userId);
        
        $this->view->users = User::getMultiple($following->toArray());
        $this->_setUsersIFollow();
    }
    
    /**
     * Start following given userId
     */
    public function followAction()
    {
        $auth = Zend_Auth::getInstance();
        if (!$auth->hasIdentity()) {
            throw new Zend_Auth_Exception("You're not authorized to see this page");
        }
        
        $userId = $this->_getParam('userId');
        $follower = $auth->getStorage()->read();
        
        if ($userId != $follower['id']) {
            $followers = new Followers($userId);
            $followers[] = $follower['id'];
            
            $following = new Following($follower['id']);
            $following[] = $userId;
        }
        $this->_redirect('/user/followers/userId/' . $userId);
    }
    
    /**
     * Stop following given userId
     */
    public function unfollowAction()
    {
        $auth = Zend_Auth::getInstance();
        if (!$auth->hasIdentity()) {
            throw new Zend_Auth_Exception("You're not authorized to see this page");
        }
        
        $userId = $this->_getParam('userId');
        $follower = $auth->getStorage()->read();
        
        if ($userId != $follower['id']) {
            $followers = new Followers($userId);
            $followers->remove($follower['id']);
            
            $following = new Following($follower['id']);
            $following->remove($userId);
        }
        $this->_redirect('/user/followers/userId/' . $userId);
    }
    
    public function logoutAction()
    {
        Zend_Session::destroy();
        $this->_redirect('/user/login');
    }
    
    public function indexAction()
    {
        $this->view->users = array();
        
        $users = new Users();
        
        // just for example, better use multiget
        foreach ($users as $userId) {
            $user = new User($userId);
            $this->view->users[] = $user->getValue();
        }
        $this->_setUsersIFollow();
    }
    
    protected function _setUsersIFollow()
    {
        $auth = Zend_Auth::getInstance();
        if (!$auth->hasIdentity()) {
            throw new Zend_Auth_Exception("You're not authorized to see this page");
        }
        
        $currentUser = $auth->getStorage()->read();
        $following = new Following($currentUser['id']);
        
        $this->view->userIdsIFollow = $following->toArray();
    }
}