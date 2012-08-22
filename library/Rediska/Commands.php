<?php

/**
 * Rediska commands
 *
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage Commands
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Commands
{
    /**
     * Redis commands
     *
     * @var array
     */
    protected static $_commands = array(
        // Connection handling
        'quit' => 'Rediska_Command_Quit',

        // Commands operating on all value types
        'exists'           => 'Rediska_Command_Exists',
        'delete'           => 'Rediska_Command_Delete',
        'gettype'          => 'Rediska_Command_GetType',
        'getkeysbypattern' => 'Rediska_Command_GetKeysByPattern',
        'getrandomkey'     => 'Rediska_Command_GetRandomKey',
        'rename'           => 'Rediska_Command_Rename',
        'getkeyscount'     => 'Rediska_Command_GetKeysCount',
        'expire'           => 'Rediska_Command_Expire',
        'getlifetime'      => 'Rediska_Command_GetLifetime',
        'selectdb'         => 'Rediska_Command_SelectDb',
        'movetodb'         => 'Rediska_Command_MoveToDb',
        'flushdb'          => 'Rediska_Command_FlushDb',

        // Commands operating on string values
        'set'          => 'Rediska_Command_Set',
        'setandexpire' => 'Rediska_Command_SetAndExpire',
        'setandget'    => 'Rediska_Command_SetAndGet',
        'get'          => 'Rediska_Command_Get',
        'append'       => 'Rediska_Command_Append',
        'increment'    => 'Rediska_Command_Increment',
        'decrement'    => 'Rediska_Command_Decrement',
        'setrange'     => 'Rediska_Command_SetRange',
        'getrange'     => 'Rediska_Command_GetRange',
        'substring'    => 'Rediska_Command_GetRange',
        'setbit'       => 'Rediska_Command_SetBit',
        'getbit'       => 'Rediska_Command_GetBit',
        'getlength'    => 'Rediska_Command_GetLength',

        // Commands operating on lists
        'appendtolist'          => 'Rediska_Command_AppendToList',
        'prependtolist'         => 'Rediska_Command_PrependToList',
        'getlistlength'         => 'Rediska_Command_GetListLength',
        'getlist'               => 'Rediska_Command_GetList',
        'truncatelist'          => 'Rediska_Command_TruncateList',
        'getfromlist'           => 'Rediska_Command_GetFromList',
        'settolist'             => 'Rediska_Command_SetToList',
        'deletefromlist'        => 'Rediska_Command_DeleteFromList',
        'shiftfromlist'         => 'Rediska_Command_ShiftFromList',
        'shiftfromlistblocking' => 'Rediska_Command_ShiftFromListBlocking',
        'popfromlist'           => 'Rediska_Command_PopFromList',
        'popfromlistblocking'   => 'Rediska_Command_PopFromListBlocking',
        'inserttolist'          => 'Rediska_Command_InsertToList',
        'inserttolistafter'     => 'Rediska_Command_InsertToListAfter',
        'inserttolistbefore'    => 'Rediska_Command_InsertToListBefore',

        // Commands operating on sets
        'addtoset'         => 'Rediska_Command_AddToSet',
        'deletefromset'    => 'Rediska_Command_DeleteFromSet',
        'getrandomfromset' => 'Rediska_Command_GetRandomFromSet',
        'getsetlength'     => 'Rediska_Command_GetSetLength',
        'existsinset'      => 'Rediska_Command_ExistsInSet',
        'intersectsets'    => 'Rediska_Command_IntersectSets',
        'unionsets'        => 'Rediska_Command_UnionSets',
        'diffsets'         => 'Rediska_Command_DiffSets',
        'getset'           => 'Rediska_Command_GetSet',
        'movetoset'        => 'Rediska_Command_MoveToSet',

        // Commands operating on sorted sets
        'addtosortedset'             => 'Rediska_Command_AddToSortedSet',
        'deletefromsortedset'        => 'Rediska_Command_DeleteFromSortedSet',
        'getsortedset'               => 'Rediska_Command_GetSortedSet',
        'getfromsortedsetbyscore'    => 'Rediska_Command_GetFromSortedSetByScore',
        'getsortedsetlength'         => 'Rediska_Command_GetSortedSetLength',
        'getsortedsetlengthbyscore'  => 'Rediska_Command_GetSortedSetLengthByScore',
        'incrementscoreinsortedset'  => 'Rediska_Command_IncrementScoreInSortedSet',
        'deletefromsortedsetbyscore' => 'Rediska_Command_DeleteFromSortedSetByScore',
        'deletefromsortedsetbyrank'  => 'Rediska_Command_DeleteFromSortedSetByRank',
        'getscorefromsortedset'      => 'Rediska_Command_GetScoreFromSortedSet',
        'getrankfromsortedset'       => 'Rediska_Command_GetRankFromSortedSet',
        'unionsortedsets'            => 'Rediska_Command_UnionSortedSets',
        'intersectsortedsets'        => 'Rediska_Command_IntersectSortedSets',

        // Commands operating on hashes
        'settohash'        => 'Rediska_Command_SetToHash',
        'getfromhash'      => 'Rediska_Command_GetFromHash',
        'incrementinhash'  => 'Rediska_Command_IncrementInHash',
        'existsinhash'     => 'Rediska_Command_ExistsInHash',
        'deletefromhash'   => 'Rediska_Command_DeleteFromHash',
        'gethashlength'    => 'Rediska_Command_GetHashLength',
        'gethash'          => 'Rediska_Command_GetHash',
        'gethashfields'    => 'Rediska_Command_GetHashFields',
        'gethashvalues'    => 'Rediska_Command_GetHashValues',

        // Sorting
        'sort' => 'Rediska_Command_Sort',

        // Publish/Subscribe
        'publish' => 'Rediska_Command_Publish',

        // Persistence control commands
        'save'                  => 'Rediska_Command_Save',
        'getlastsavetime'       => 'Rediska_Command_GetLastSaveTime',
        'shutdown'              => 'Rediska_Command_Shutdown',
        'rewriteappendonlyfile' => 'Rediska_Command_RewriteAppendOnlyFile',

        // Remote server control commands
        'info'    => 'Rediska_Command_Info',
        'ping'    => 'Rediska_Command_Ping',
        'slaveof' => 'Rediska_Command_SlaveOf'
    );

    /**
     * Add command
     *
     * @param string $name      Command name
     * @param string $className Name of class
     */
    public static function add($name, $className)
    {
        if (!class_exists($className)) {
            throw new Rediska_Exception("Class '$className' not found. You must include before or setup autoload");
        }

        $classReflection = new ReflectionClass($className);
        if (!in_array('Rediska_Command_Interface', $classReflection->getInterfaceNames())) {
            throw new Rediska_Exception("Class '$className' must implement Rediska_Command_Interface interface");
        }

        $lowerName = strtolower($name);
        self::$_commands[$lowerName] = $className;

        return true;
    }

    /**
     * Remove command
     *
     * @param string $name Command name
     */
    public static function remove($name)
    {
        $lowerName = self::_getCommandLowerNameAndThrowIfNotPresent($name);

        unset(self::$_commands[$lowerName]);

        return true;
    }

    /**
     * Get command instance
     *
     * @param Rediska $rediska   Rediska instance
     * @param string  $name      Command name
     * @param array   $arguments Command arguments
     * @return Rediska_Command_Abstract
     */
    public static function get(Rediska $rediska, $name, $arguments)
    {
        $lowerName = self::_getCommandLowerNameAndThrowIfNotPresent($name);

        return new self::$_commands[$lowerName]($rediska, $name, $arguments);
    }

    /**
     * Get command list
     *
     * @return array
     */
    public static function getList()
    {
        return self::$_commands;
    }

    /**
     * Get command lower name and throw exception if command not present
     *
     * @param <type> $name
     * @return <type>
     */
    protected static function _getCommandLowerNameAndThrowIfNotPresent($name)
    {
        $lowerName = strtolower($name);

        if (!isset(self::$_commands[$lowerName])) {
            throw new Rediska_Exception("Command '$name' not found");
        }

        return $lowerName;
    }
}
