<?php

interface Rediska_Profiler_Interface
{
    public function start();

    public function stop($context);
}