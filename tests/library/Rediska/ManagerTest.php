<?php


/**
 * Tests if Manager works as expected.
 */
class ManagerTest extends Rediska_TestCase
{
    function testDefaultInstance()
    {
        $rediska=new Rediska();

        $this->assertSame($rediska,Rediska_Manager::get());
    }

    function testAddAlias()
    {
        $rediska=new Rediska();
        Rediska_Manager::addAlias($rediska,"testalias");
        $this->assertSame($rediska,Rediska_Manager::get());
        $this->assertSame($rediska,Rediska_Manager::get("testalias"));

        $rediska2=new Rediska();
        Rediska_Manager::addAlias($rediska2,"secondinstance");
        $this->assertSame($rediska2,Rediska_Manager::get("secondinstance"));
        $this->assertNotSame($rediska2,Rediska_Manager::get());
        $this->assertNotSame($rediska2,Rediska_Manager::get("testalias"));
    }
}





