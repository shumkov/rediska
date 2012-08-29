<?php
/**
 * Rediska Info
 *
 *
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage Info
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 *
 * @property-read string $redis_version
 * @property-read string $redis_git_sha1
 * @property-read string $redis_git_dirty
 * @property-read string $os
 * @property-read string $arch_bits
 * @property-read string $multiplexing_api
 * @property-read string $gcc_version
 * @property-read string $process_id
 * @property-read string $run_id
 * @property-read string $tcp_port
 * @property-read string $uptime_in_seconds
 * @property-read string $uptime_in_days
 * @property-read string $lru_clock
 * @property-read string $connected_clients
 * @property-read string $client_longest_output_list
 * @property-read string $client_biggest_input_buf
 * @property-read string $blocked_clients
 * @property-read string $used_memory
 * @property-read string $used_memory_human
 * @property-read string $used_memory_rss
 * @property-read string $used_memory_peak
 * @property-read string $used_memory_peak_human
 * @property-read string $used_memory_lua
 * @property-read string $mem_fragmentation_ratio
 * @property-read string $mem_allocator
 * @property-read string $loading
 * @property-read string $rdb_changes_since_last_save
 * @property-read string $rdb_bgsave_in_progress
 * @property-read string $rdb_last_save_time
 * @property-read string $rdb_last_bgsave_status
 * @property-read string $rdb_last_bgsave_time_sec
 * @property-read string $rdb_current_bgsave_time_sec
 * @property-read string $aof_enabled
 * @property-read string $aof_rewrite_in_progress
 * @property-read string $aof_rewrite_scheduled
 * @property-read string $aof_last_rewrite_time_sec
 * @property-read string $aof_current_rewrite_time_sec
 * @property-read string $total_connections_received
 * @property-read string $total_commands_processed
 * @property-read string $instantaneous_ops_per_sec
 * @property-read string $rejected_connections
 * @property-read string $expired_keys
 * @property-read string $evicted_keys
 * @property-read string $keyspace_hits
 * @property-read string $keyspace_misses
 * @property-read string $pubsub_channels
 * @property-read string $pubsub_patterns
 * @property-read string $latest_fork_usec
 * @property-read string $role
 * @property-read string $connected_slaves
 * @property-read string $used_cpu_sys
 * @property-read string $used_cpu_user
 * @property-read string $used_cpu_sys_children
 * @property-read string $used_cpu_user_children
 * @property-read string $db
 */
class Rediska_Info implements IteratorAggregate
{
    /**
     * Data
     *
     * @var array
     */
    protected $_data = array();

    /**
     * Constructor
     *
     * @param array $data
     */
    public function __construct($data)
    {
        $this->_data = $data;
    }

    /**
     * Get iterator
     *
     * @return ArrayIterator|Traversable
     */
    public function getIterator()
    {
        return new ArrayIterator($this->_data);
    }

    /**
     * Magic for get data
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        $value = null;
        if(array_key_exists($name, $this->_data)){
            $value = $this->_data[$name];
        }
        return $value;
    }
}
