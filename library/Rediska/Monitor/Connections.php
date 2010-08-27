<?php

/**
 * This iterator is used by Rediska_PubSub_Channel
 * to repeatedly iterate through available connections
 *
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Monitor_Connections implements IteratorAggregate, Countable
{
    /**
     * Rediska Monitor
     * 
     * @var Rediska_Monitor
     */
    protected $_monitor;

    /**
     * Connections
     *
     * @var array
     */
    protected $_connections = array();

    /**
     * Pool of connections
     * 
     * @var array
     */
    static protected $_allConnections = array();

    /**
     * Constructor
     *
     * @param Rediska_Monitor $monitor
     */
    public function __construct(Rediska_Monitor $monitor)
    {
        $this->_monitor = $monitor;

        if ($monitor->getServerAlias() !== null) {
            $this->add($monitor->getServerAlias());
        } else {
            foreach($monitor->getRediska()->getConnections() as $connection) {
                $this->add($connection);
            }
        }
    }

    public function add($aliasOrConnection)
    {
        if (!$aliasOrConnection instanceof Rediska_Connection) {
            $connection = $this->_monitor->getRediska()->getConnectionByAlias($aliasOrConnection);
        } else {
            $connection = $aliasOrConnection;
        }

        if (!array_key_exists($connection->getAlias(), $this->_connections)) {
            if (!array_key_exists($connection->getAlias(), self::$_allConnections)) {
                self::$_allConnections[$connection->getAlias()] = clone $connection;
                self::$_allConnections[$connection->getAlias()]->setBlockingMode(false);

                $connection = self::$_allConnections[$connection->getAlias()];

                $monitor = new Rediska_Connection_Exec($connection, 'MONITOR');
                $monitor->execute();
            } else {
                $connection = self::$_allConnections[$connection->getAlias()];
            }

            $this->_connections[$connection->getAlias()] = $connection;

            return true;
        }

        return false;
    }

    /* IteratorAggregate implementation */

    public function getIterator()
    {
        return new ArrayObject($this->_connections);
    }

    /* Countable implementation */

    public function count()
    {
        return count($this->_connections);
    }
}