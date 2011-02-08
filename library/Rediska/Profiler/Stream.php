<?php

/**
 * Rediska stream profiler
 *
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage Profiler
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Profiler_Stream extends Rediska_Profiler
{
    /**
     * PHP stream
     *
     * @var stream|null
     */
    protected $_stream;

    /**
     * Access mode to stream
     *
     * @var stream
     */
    protected $_mode = 'a';

    /**
     * Output format
     *
     * @var string
     */
    protected $_format = '[%timestamp%] %profile% => %elapsedTime%';

    /**
     * Constructor
     *
     * @param array $options
     */
    public function __construct($options = array())
    {
        if (isset($options['mode'])) {
            $this->setMode($options['mode']);
        }

        if (isset($options['stream'])) {
            $this->setStream($options['stream']);
        } else {
            throw new Rediska_Profiler_Exception("You must specify option 'stream'");
        }

        if (isset($options['format'])) {
            $this->setFormat($options['format']);
        }
    }

    /**
     * Set stream
     *
     * @param stream|string $stream Stream or Url
     * @return Rediska_Profiler_Stream
     */
    public function setStream($stream)
    {
        $this->_stream = $stream;

        return $this;
    }

    /**
     * Get stream
     *
     * @return stream
     */
    public function getStream()
    {
        if (!is_resource($this->_stream)) {
            $stream = $this->_stream;
            if (is_array($stream) && isset($stream['stream'])) {
                $stream = $stream['stream'];
            }

            if (! $this->_stream = @fopen($stream, $this->getMode(), false)) {
                throw new Rediska_Profiler_Exception("'$stream' cannot be opened with mode '{$this->getMode()}'");
            }
        }

        return $this->_stream;
    }

    /**
     * Set access mode
     *
     * @param string $mode
     * @return Rediska_Profiler_Stream
     */
    public function setMode($mode)
    {
        if (is_resource($this->_stream) && $this->_mode != $mode) {
            $meta = stream_get_meta_data($this->_stream);
            $this->setStream($meta['uri']);
        }

        $this->_mode = $mode;

        return $this;
    }

    /**
     * Get access mode
     *
     * @return string
     */
    public function getMode()
    {
        return $this->_mode;
    }

    /**
     * Set output format
     *
     * @param string $format
     * @return Rediska_Profiler_Stream
     */
    public function setFormat($format)
    {
        $this->_format = $format;

        return $this;
    }

    /**
     * Get output format
     *
     * @return string
     */
    public function getFormat()
    {
        return $this->_format;
    }

    /**
     * Stop callback. Called from profile
     *
     * @param Rediska_Profiler_Profile $profile
     */
    public function stopCallback(Rediska_Profiler_Profile $profile)
    {
        $placeHolders = array(
            '%timestamp%'   => date('Y-m-d H:i:s'),
            '%profile%'     => $profile->getContext(),
            '%elapsedTime%' => $profile->getElapsedTime(4)
        );

        $data = str_replace(
            array_keys($placeHolders),
            array_values($placeHolders),
            $this->_format . Rediska::EOL
        );

        $this->_write($data);
    }

    /**
     * Reset profiler
     *
     * @return Rediska_Profiler_Stream
     */
    public function reset()
    {
        parent::reset();

        $prevMode = $this->getMode();

        $this->setMode('w');

        $this->_write('');

        $this->setMode($prevMode);

        return $this;
    }

    /**
     * Write profile to string
     *
     * @param string $data
     */
    protected function _write($data)
    {
        if (false === @fwrite($this->getStream(), $data)) {
            throw new Rediska_Profiler_Exception("Unable to write to stream");
        }
    }

    /**
     * Close stream on destruct
     */
    public function __destruct()
    {
        if (is_resource($this->_stream)) {
            fclose($this->_stream);
        }
    }
}