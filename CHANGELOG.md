Changelog
======

Version 0.5.7
---
Unreleased

 * Feature: Add Rediska package to Composer
 * Feature: Add to Travis CI
 * Feature: Info object. Robert Allen
 * Feature: Implement `PING`. Robert Allen
 * Feature: Socket functions support. New connection option: useSocket. Ruan Chunping
 * Improvement: Transactions and tags support for Zend Cache backend. Robert Allen
 * Bug: Redis invalid argument error in `Rediska::popFromListBlocking` with `$pushToKey` argument and many connections
 * Bug #11654: Profiler option can't get a string

Version 0.5.6
---
April 13, 2011

 * Bug #10886: Remove `json_last_error()` from JSON serialzier
 * Bug #10887: Subscribe don't unserialize values

Version 0.5.5
---
March 8, 2011

 * Feature #4214: Profiler
 * Feature #9921: Implement `SETBIT`, `GETBIT`, `SETRANGE`, `GETRANGE`, `STRLEN`
 * Feature #9932: Implement `LINSERT`, `LPUSHX`, `RPUSHX`
 * Feature #9933: Implement `BRPOPLPUSH`
 * Feature #3531: Implement `WATCH`, `UNWATCH`
 * Feature #9876: Add `Rediska#removeServer` method
 * Improvement #10020: Move all command to new binary safe protocol
 * Improvement #9920: Deprecate old expire behavior
 * Improvement #8863: Add optional response iterator for sets, sortedsets and lists with realtime reading from socket
 * Improvement #3967: Add timeout argument to getCommand and getMessage in monitor and pub/sub
 * Improvement #4379: Remove case insensitive from options
 * Improvement #4902: Set connection to blocking mode if only one connection in pub/sub channel
 * Improvement #6751: Change Set with online sessions to SortedSet for optimization
 * Improvement #6778: Autoloader optimization
 * Improvement #9021: New method `Rediska_Key_SortedSet#getByRank`
 * Improvement #9045: New method `Rediska_Key_Hash#getFieldsAndValues`
 * Improvement #9052: New methods `Rediska_Key_Set#getValues` and `Rediska_Key_List#getValues`
 * Improvement #10093: Add expire argument to `Rediska_Key::getOrSetValue`
 * Bug #4900: Require Rediska bug in `Rediska_PubSub_Channel`
 * Bug #6745: Pub/sub channel can't get `Rediska_Connections`
 * Bug #10061: Subscribe throws exception by timeout if it not specified
 * Bug #6746: True subscribe response throws exception
 * Bug #6747: Add specified connection to publish
 * Bug #6748: Fix memory leaks
 * Bug #6777: Fix test bootstrap
 * Bug #10159: Break typo in consitent hashing

Version 0.5.1
---
October 28, 2010
 
 * Feature #2033: Implement `ZCOUNT`
 * Improvement #4054: Add append and substring command to `Rediska_Key`
 * Improvement #4055: New command `Rediska_Key::setAndExpire`
 * Bug #4290: Warning with empty namespace
 * Bug #4476: Get parameter in sort command

Version 0.5.0
---
September 7, 2010

 * Feature #2033: Implement hash commands: `HSET`, `HGET`, `HDEL`, `HEXISTS`, `HLEN`, `HKEYS`, `HVALS`, `HGETALL`, `HMSET`, `HINCRBY`, `HMGET`, `HSETNX`
 * Feature #2035: Hash object
 * Feature #2036: Transactions. Implement `MULTI`, `EXEC`, `DISCARD`
 * Feature #2037: Publish/Subscribe. Implement `SUBSCRIBE`, `UNSUBSCRIBE`, `PUBLISH`
 * Feature #1955: Multiple instance manager
 * Feature #2325: Autoloader
 * Feature #2028: Implement `BLPOP` and `BRPOP`
 * Feature #2342: Implement `SETEX`
 * Feature #3444: Implement `APPEND`
 * Feature #3445: Implement `SUBSTR`
 * Feature #3446: Implement `CONFIG`
 * Feature #3526: Implement `MONITOR`
 * Feature #3621: PhpDoc documentaion
 * Improvement #2405: New serialzier. `phpSerialize` and `json` adapters. Rediska now can't serialize strings.
 * Improvement #3246: Add read timeout to connection
 * Improvement #3248: Add pushTo argument to pop method of List object
 * Improvement #3250: Method generator for IDE autocomplete and phpDoc
 * Improvement #3199: Refactor commands
 * Bug #2812: Bad assignation in `Rediska_Key_Abstract#setName`
 * Bug #3017: Fix cache multiload
 * Bug #3442: Disconnect on destruct break persistent connection
 * Bug #3547: `Rediska_Key_SortedSet#union` does not work properly

Version 0.4.2
---
April 21, 2010

 * Feature #2236: Add Redis server version specification (added `redisVersion` option)
 * Feature #2029: Implement `ZRANK` and `ZREVRANK`
 * Feature #2030: Implement `WITHSCORES` argument for `ZRANGEBYSCORE`
 * Feature #2031: Implement `ZREMRANGEBYRANK`
 * Feature #2032: Implement `ZUNION` and `ZINTER`
 * Feature #2195: Implement `SORT` command as standalone
 * Improvement #2233: Refactor configuration of test suite (now requires Zend Framework)
 * Improvement #2314: Deprecate `SORT` attributes in `Rediska#getList` and `Rediska#getSet`
 * Bug #2197: Limit and offset broke inverted selects (arguments changed for `Rediska#getList`, `Rediska#getSortedSet`, `Rediska#truncateList`, `Rediska#getFromSortedSetByScore`)
 * Bug #2290: If sessions set is empty trown exception
 * Bug #2321: Null lifetime not supported in cache backend

Version 0.4.0
---
April 1, 2010

 * Feature #583: Create expample application
 * Feature #766: Create pear package
 * Feature #1926: Symfony integration
 * Feature #591: Implement `BGREWRITEAOF`
 * Feature #594: Implement `ZINCRBY`
 * Feature #802: Implement `ZREMRANGEBYSCORE`
 * Feature #803: Implement slaveof no one
 * Feature #902: Add README and CHANGELOG
 * Improvement #760: Optimize consistent hashing
 * Improvement #763: Move to `stream_socket_client`
 * Improvement #797: Add timeout for connection
 * Improvement #582: Specify connection alias for key objects
 * Improvement #640: Add with scores to `ZRANGE`
 * Improvement #675: Throw exceptions if empty arguments
 * Improvement #835: On method can get connection object
 * Improvement #581: Add multiple values to set and list by pipeline
 * Improvement #595: Add getScoreFromSortedSet to sorted set object
 * Improvement #765: New test suite
 * Improvement #794: Add `EXPIREAT`
 * Bug #641: Broken `RPOPLPUSH` command
 * Bug #648: Add increment and decrement to rediska key wrapper
 * Bug #669: Create exception if connection not found
 * Bug #670: Warning: Invalid argument supplied for foreach() in .../library/Rediska/Command/Abstract.php on line 128 (111)
 * Bug #813: `QUIT` must disconnect connections
 * Bug #825: Save Handler throw exception on Rediska options

Version 0.3.0
---
January 22, 2010

 * Feature #355: Alias for servers
 * Feature #356: Operate with keys on specified (by alias) server
 * Feature #471: Documentation
 * Feature #472: Pipeline
 * Improvement #520: Refactor commands
 * Improvement #521: Support Redis 1.2 API
 * Improvement #522: Lazy loading command classes
 * Bug #524: Messages count return queues count
 * Bug #559: Select specified db after connect

Version 0.2.2
---
November 27, 2009

 * Feature #577: Persistent connection
 * Feature #578: Move to BSD license

Version 0.2.1
---
November 25, 2009

 * Feature #363: Writer for `Zend_Log`
 * Feature #364: Adapter for `Zend_Queue`
