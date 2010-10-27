<?php

class Rediska_Profiler_File extends Rediska_Profiler
{
    protected $_path;

    public function __construct($options = array())
    {
        if (isset($options['path'])) {
            $this->setPath($options['path']);
        } else {
            throw new Rediska_Profiler_Exception("You must specify profiler option 'path'");
        }
    }

    public function setPath($path)
    {
        $this->_path = $path;

        return $this;
    }

    public function getPath()
    {
        return $this->_path;
    }

    public function stop()
    {
        $profile = parent::stop();

        $data = '[' . date('Y-m-d H:i') . '] ' . $profile;

        if (file_put_contents($this->getPath(), $data, FILE_APPEND | LOCK_EX) === false) {
            throw new Rediska_Profiler_Exception("Can't write profile to '$path'");
        }

        return $profile;
    }

    public function reset()
    {
        return parent::reset();
    }
}