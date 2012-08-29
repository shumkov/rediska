Rediska (radish on russian) - PHP client for Redis.
============

Redis is an advanced fast key-value database written in C. It can be used like memcached, in front of a traditional database, or on its own thanks to the fact that the in-memory datasets are not volatile but instead persisted on disk. One of the cool features is that you can store not only strings, but lists and sets with atomic operations to push/pop elements.

More information and documentation on homepage: [http://rediska.geometria-lab.net](http://rediska.geometria-lab.net)

Features
---

* Multiple servers support
* Consistent hashing, crc32 or you personal algorythm for key distribution
* Working with keys as objects
* Use Lists, Sets, Sorted sets and Hashes as native PHP arrays
* Transactions
* Publish/Subscribe
* Profiler
* Pipelining
* Easy extending Rediska by adding you own commands or overwrite standart
* [Zend Framework](http://framework.zend.com/) integration
* [Symfony](http://www.symfony-project.org/) framework integration
* Full [documentation](http://rediska.geometria-lab.ru/documentation)
* Example application
* [PHPUnit](http://phpunit.de/) tests [![Master branch build status](https://secure.travis-ci.org/Shumkov/Rediska.png?branch=master)](http://travis-ci.org/Shumkov/Rediska)

Coming soon
---

* PHP extension
* Cloud key distribution
* Ketama (fast C library for key distribution) support
* Benchmarks and performance optimization

Get started!
---

1. **Get Rediska**
   
   You can install Rediska from PEAR, download zip archive or get from git repository.

     1.1. **Install via composer**

     [Get composer](http://getcomposer.org/) and add [Rediska package](http://packagist.org/packages/geometria-lab/rediska) to dependencies.

     1.2. **Install from PEAR**

     For begining you need to discover our PEAR channel:

     `pear channel-discover pear.geometria-lab.net`

     And install package:

     `pear install geometria-lab/Rediska-beta`

     1.3. **Download or get from repository**

     [Download zip archive](http://rediska.geometria-lab.net/download/latest) with latest version or get last **unstable** version from git repository:

     `git clone http://github.com/shumkov/rediska.git`

     For adding Rediska to your applcation you need copy Rediska from library folder to you application library folder
     * [Add Rediska to your Zend Framework application](http://rediska.geometria-lab.net/documentation/integration-with-frameworks/zend-framework/configuration-and-bootstraping)
     * [Add Rediska plugin to your Symfony application](http://rediska.geometria-lab.net/documentation/integration-with-frameworks/symfony)



2. **Configure Rediska**

         <?php

         $options = array(
           'namespace' => 'Application_',
           'servers'   => array(
             array('host' => '127.0.0.1', 'port' => 6379),
             array('host' => '127.0.0.1', 'port' => 6380)
           )
         );

         require_once 'Rediska.php';
         $rediska = new Rediska($options);

         ?>

      [All configuration options](http://rediska.geometria-lab.net/documentation/configuration).

3. **Use Rediska**

         <?php

         // Set 'value' to key 'keyName'
         $key = new Rediska_Key('keyName');
         $key->setValue('value');

         ?>
     * [Full usage documentation](http://rediska.geometria-lab.net/documentation/usage)
     * Using Rediska with frameworks:
        * [Zend Framework](http://rediska.geometria-lab.net/documentation/integration-with-frameworks/zend-framework)
        * [Symfony](http://rediska.geometria-lab.net/documentation/integration-with-frameworks/symfony)

    
Project structure
---

* __CHANGELOG.txt__ - Histroy of Rediska
* __README.txt__    - This document
* __VERSION.txt__   - Current version of Rediska
* __benchmarks/__   - Rediska benchmarks. In progress...
* __examples/__     - Rediska expamples
* __library/__      - Rediska library. Put files from library to you include_path and use: `require_once "Rediska.php"`
* __package.xml__   - Install Rediska to PHP library dir: `pear install package.xml`. Now use Rediska is easy (without `include_path` configuration): `require_once "Rediska.php"`
* __scripts/__      - Maintenance scripts
* __tests/__        - PHPUnit tests. Use `phpunit` console command or right click on bootstrap.php and `Run As -> PHPUnit Test` in Zend Studio

Contributions
---

Rediska is an open source project: you can participate in development or become an author of integration module for your favorite framework.

Authors:
---

* [Ivan Shumkov](mailto:ivan@shumkov.ru)
* [Maxim Ivanov](mailto:maximiv@gmail.com)
* [Ryan Grenz](mailto:info@ryangrenz.com) (Symfony integration)
* [Till Klampaeckel](mailto:till@php.net) (PEAR package)