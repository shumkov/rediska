Rediska (radish on russian) - PHP client for Redis.

Redis is an advanced fast key-value database written in C. It can be used like memcached, in front of a traditional database, or on its own thanks to the fact that the in-memory datasets are not volatile but instead persisted on disk. One of the cool features is that you can store not only strings, but lists and sets with atomic operations to push/pop elements.

More information and documentation on homepage: http://rediska.geometria-lab.net

Features:
    * Multiple servers support
    * Consistent hashing, crc32 or you personal algorythm for key distribution
    * Working with keys as objects
    * Use Lists, Sets and Sorted sets as native PHP arrays
    * Pipelining
    * Easy extending Rediska by adding you own commands or overwrite standart
    * Full Zend Framework integration:
          * Zend_Application resource for bootstraping and configure Redis in application.ini
          * Zend_Auth Redis adapter
          * Zend_Cache Redis backend
          * Zend_Log Redis writer
          * Zend_Queue Redis adapter
          * Zend_Session Redis save handler

Coming soon:
    * Symfony framework integration
    * Cloud key distribution
    * Ketama (fast C library for key distribution) support
    * Tags for group expiring keys
    * Benchmarks and performance optimization
    * Example application
    
Project structure:
    * benchmarks/ - Rediska benchmarks. In progress...
    * examples/   - Rediska expamples.
    * library/    - Rediska library. Put files from library to you include_path and use: require_once 'Rediska.php'.
    * package.xml - Install Rediska to PHP library dir: 'pear install package.xml'. Now use Rediska is easy (without include_path configuration): require_once 'Rediska.php'.
    * scripts/    - Maintenance scripts.
    * tests/      - PHPUnit tests. Use 'phpunit' console command or right click on bootstrap.php and 'Run As' -> 'PHPUnit Test' in Zend Studio.

Contributions:
Rediska is an open source project: you can participate in development or become an author of integration module for your favorite framework

Authors:
    * Ivan Shumkov <ivan@shumkov.ru>
    * Maxim Ivanov <maximiv@gmail.com>