<?php

/**
 * Perform a bitwise operation between multiple keys (containing string values)
 * and store the result in the destination key.
 *
 * @package    Rediska
 * @subpackage Commands
 * @version    @package_version@
 * @link       http://rediska.geometria-lab.net
 * @license    http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_Bitop extends Rediska_Command_Abstract
{
    protected $_version = '2.6.0';

    /**
     * @param string        $operation OR|XOR|AND|NOT
     * @param string        $destkey
     * @param string|array  $srckeys
     * @return Rediska_Connection_Exec
     * @throws Rediska_Command_Exception
     */
    public function create($operation, $destkey, $srckeys)
    {
        $srckeys = (array) $srckeys;

        $destkey = $this->getRediska()
                ->getOption('namespace') . $destkey;
        $connection = $this
            ->getRediska()
            ->getConnectionByKeyName($destkey);

        $command = array('BITOP', $operation, $destkey);

        foreach ($srckeys as $src) {
            $src = $this->getRediska()
                ->getOption('namespace') . $src;
            array_push($command, $src);
        }
        return new Rediska_Connection_Exec($connection, $command);
    }
}
