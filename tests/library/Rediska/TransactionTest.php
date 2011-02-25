<?php

class Rediska_TransactionTest extends Rediska_TestCase
{   
    /**
     * 
     * @var Rediska_Transaction
     */
    protected $transaction;
    
    public function setUp()
    {
        parent::setUp();
        
        $this->transaction = $this->rediska->transaction();
    }
    
    public function tearDown()
    {
        parent::tearDown();

        $this->transaction->discard();
    }

    public function testAddCommands()
    {
        $response = $this->transaction->set('a', 1)->get('a')->delete('a')->set('b', 2);
        $this->assertEquals($this->rediska->get('b'), null);
    }

    public function testExecute()
    {
        $response = $this->transaction->set('a', 1)->get('a')->delete('a')->execute();
        $this->assertEquals(array(true, 1, 1), $response);
    }

    public function testDiscard()
    {
        $response = $this->transaction->set('a', 1)->get('a')->delete('a')->set('b', 2)->discard();
        $this->assertEquals($this->rediska->get('b'), null);
    }
}