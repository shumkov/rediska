<?php

/**
 * Publish message to pubsub channel
 * 
 * @param string $name Key name
 * @param mixin  $value Value
 * @return boolean
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_Publish extends Rediska_Command_Abstract
{
    public function create($channelOrChannels, $message)
    {
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

    public function parseResponses($responses)
    {
        return array_sum($responses);
    }
}