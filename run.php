<?php

function __autoload($className) {
    require strtolower($className) . '.php';
}

$options = new Options($argv);

if ( !$options->issetFilename() ) {
    die("usage php " . $options->getScript() . "[--debug] filename\n");
}

$filename = $options->getFilename();

if ( !file_exists($filename) ) {
    die("file '" . $filename . "' not found\n");
}

try {
    $solver = new Solver();
    $solver->setDebug($options->issetOption('debug'));
    $solver->solve(file_get_contents($filename));
    $solver->getBoard()->cleanPrint(3);

} catch (RuntimeException $e) {

    $solver->getBoard()->cleanPrint(3);

    echo $e->getMessage() . PHP_EOL;
    exit(0);
} catch (InvalidArgumentException $e) {

    echo $e->getMessage() . PHP_EOL;
    exit(0);
}





