<?php

require_once __DIR__ . '/../library/Rediska.php';

foreach(Rediska::getCommands() as $name => $class) {
    // Get description
    $classReflection = new ReflectionClass($class);
    $docBlockLines = explode("\n", $classReflection->getDocComment());
    $description = trim(substr($docBlockLines[1], 2));

    // Get params
    $createReflection = $classReflection->getMethod('create');
    $methodDocBlock = $createReflection->getDocComment();
    preg_match_all('/@param\s+(\S+)\s+(\S+)\s+(.*)/i', $methodDocBlock, $matches, PREG_SET_ORDER);
    if (!empty($matches)) {
        
    }

    // Get return
    $createReflection = $classReflection->getMethod('parseResponse');
    $methodDocBlock = $createReflection->getDocComment();
    preg_match('/@return\s+(\S+)/i', $methodDocBlock, $matches);
    var_dump($matches);
}