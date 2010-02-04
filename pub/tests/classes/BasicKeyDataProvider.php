<?php

class BasicKeyDataProvider
{
    public $data = 123;

    public function getData()
    {
    	return $this->data;
    }
    
    public function getOtherDataForTest()
    {
    	return 456;
    }

    public function __toString()
    {
    	return (string)$this->getData();
    }
}