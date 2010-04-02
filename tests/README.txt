Tests

Once shared Rediska with you, we want to be sure it works well. To achieve this, we cover it with comprehensive number of unit tests.

We use PHPUnit testing library (installation: http://www.phpunit.de/manual/3.4/en/installation.html).

Before running the tests you need to setup environment: rename `config.ini-dist` to `config.ini` in tests folder and specify where your redis servers are. You don't have to specify 2nd server, but some tests will be ignored in this case.

Warning: Data on both servers will be cleared!

To run tests use commands phpunit in tests folder or if you use Zend Studio click with right mouse button on `bootstrap.php` and pick `Run As.. -> PHPUnit Test`.