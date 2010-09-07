<?php

/**
 * Publish message to pubsub channel
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage Commands
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_Publish extends Rediska_Command_Abstract
{
    /**
     * Create command
     *
     * @param array|string $channelOrChannels Channel or array of channels
     * @param mixed        $message           Message
     * @return Rediska_Connection_Exec
     */
    public function create($channelOrChannels, $message)
    {
        $channels = array();
        if (!is_array($channelOrChannels)) {
            $channels = array($channelOrChannels);
        } else {
            $channels = $channelOrChannels;
        }

        // TODO: Param may be a Rediska_PubSub_Channel object

        $execs = array();
        foreach($channels as $channel) {
            $connection = $this->_rediska->getConnectionByKeyName($channel);

            $command = array('PUBLISH');
            $command[] = $this->_rediska->getOption('namespace') . $channel;
            $command[] = $this->_rediska->getSerializer()->serialize($message);

            $execs[] = new Rediska_Connection_Exec($connection, $command);
        }

        return $execs;
    }

    /**
     * Parse response
     *
     * @param array $responses
     * @return integer
     */
    public function parseResponses($responses)
    {
        return array_sum($responses);
    }
}