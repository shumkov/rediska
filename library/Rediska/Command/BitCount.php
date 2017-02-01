<?php

/**
 * Returns the bit count at offset in the string value stored at key
 *
 * @package    Rediska
 * @subpackage Commands
 * @version    @package_version@
 * @link       http://rediska.geometria-lab.net
 * @license    http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_BitCount extends Rediska_Command_Abstract
{
    protected $_version = '2.6.0';

    /**
     * Create command
     *
     * @param string  $key   Key name
     * @param integer $start
     * @param integer $end
     * @return Rediska_Connection_Exec
     */
    public function create($key, $start = null, $end = null)
    {
        $connection = $this
            ->getRediska()
            ->getConnectionByKeyName($key);

        $command = array(
            'BITCOUNT',
            $this->getRediska()
                ->getOption('namespace') . $key
        );
        if($start !== null){
            $end = ($end === null) ? $start : $end;
            array_push($command, $start);
            array_push($command, $end);
        }
        return new Rediska_Connection_Exec($connection, $command);
    }
}
