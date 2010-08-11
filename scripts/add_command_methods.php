<?php

/**
 * Add command methods to Rediska, Rediska_Transaction, Rediska_Pipeline and Rediska_Connection_Specified
 */

define('REDISKA', __DIR__ . '/../library/Rediska.php');
define('DOCBLOCK', "/**
     * Generated command methods by 'scripts/add_command_methods.php'
     */");

function getReturn($classReflection, $method)
{
    $createReflection = $classReflection->getMethod($method);
    $methodDocBlock = $createReflection->getDocComment();
    preg_match('/@return\s+(\S+)/i', $methodDocBlock, $matches);

    return $matches[1];
}

function getParams($classReflection, $class)
{
    $command = file_get_contents(__DIR__ . '/../library/Rediska/Command/' . substr($class, 16) . '.php');
    preg_match('/public function create\((.+)/i', $command, $matches);

    if (isset($matches[1])) {
        return trim($matches[1]);
    } else {
        if ($classReflection->getParentClass()) {
            return getParams($classReflection, $classReflection->getParentClass()->getName());
        } else {
            die("$class doesnot have a create method");
        }
    }
}

function updateMethods($file, $methods)
{
    $content = file_get_contents($file);

    $newContent = substr($content, 0, strpos($content, DOCBLOCK));
    $newContent .= DOCBLOCK . "\n" . $methods . "\n}";

    file_put_contents($file, $newContent);
}

// Start!
require_once REDISKA;

$rediskaMethods = '';
$transactionMethods = '';
$connectionMethods = '';
$pipelineMethods = '';

$count = 0;
foreach(Rediska_Commands::getList() as $name => $class) {
    // Get name
    $name = lcfirst(substr($class, 16));

    // Get description
    $classReflection = new ReflectionClass($class);
    $docBlockLines = explode("\n", $classReflection->getDocComment());
    array_shift($docBlockLines);
    $description = '';
    foreach($docBlockLines as $line) {
        if (strpos($line, ' * @') !== false) {
            break;
        }
        $description .= '    ' . $line . "\n";
    }
    $createReflection = $classReflection->getMethod('create');
    $createDocBlockLines = explode("\n", $createReflection->getDocComment());
    $createDocBlockLines[1] = rtrim($description);
    unset($createDocBlockLines[2]);
    $createDocBlock = implode("\n", $createDocBlockLines);

    // Get params
    $params = getParams($classReflection, $class);
    $params = str_replace('self::', $class . '::', $params);

    // Get return
    $return = getReturn($classReflection, 'parseResponse');
    if ($return == 'mixed') {
        getReturn($classReflection, 'parseResponses');
    }

    // Rediska
    $createDocBlock = preg_replace('/@return .+/', "@return $return", $createDocBlock);
    $rediskaMethods .= "\n    $createDocBlock
    public function $name($params { \$args = func_get_args(); return \$this->_executeCommand('$name', \$args); }\n";

    // Connection
    $createDocBlock = preg_replace('/@return .+/', "@return $return", $createDocBlock);
    $connectionMethods .= "\n    $createDocBlock
    public function $name($params { \$args = func_get_args(); return \$this->_executeCommand('$name', \$args); }\n";

    // Transaction
    $createDocBlock = preg_replace('/@return .+/', "@return Rediska_Transaction", $createDocBlock);
    $transactionMethods .= "\n    $createDocBlock
    public function $name($params { \$args = func_get_args(); return \$this->_addCommand('$name', \$args); }\n";

    // Pipeline
    $createDocBlock = preg_replace('/@return .+/', "@return Rediska_Pipeline", $createDocBlock);
    $pipelineMethods .= "\n    $createDocBlock
    public function $name($params { \$args = func_get_args(); return \$this->_addCommand('$name', \$args); }\n";

    $count++;
}

updateMethods(REDISKA, $rediskaMethods);
updateMethods(__DIR__ . '/../library/Rediska/Transaction.php', $transactionMethods);
updateMethods(__DIR__ . '/../library/Rediska/Pipeline.php', $pipelineMethods);
updateMethods(__DIR__ . '/../library/Rediska/Connection/Specified.php', $connectionMethods);

// End :)
print "$count methods added.\n";