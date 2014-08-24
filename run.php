<?php

require 'colors.php';
require 'board.php';
require 'field.php';
require 'group.php';

if ( empty($argv[1]) ) {
    die("usage php " . $argv[0] . " [filename]\n");
}

$filename = $argv[1];


if ( !file_exists($filename) ) {
    die("file '" . $filename . "' not found\n");
}

$board = new Board(9);

$sudoku = file_get_contents($filename);
$rows = explode("\n", $sudoku);

try {
    if (9 !== count($rows)) {
        throw new Exception("invalid row count");
    }

    foreach ($rows as $x => $row) {
        if (9 !== strlen($row)) {
            throw new Exception("invalid row '$row");
        }

        for ($i = 0; $i < 9; ++$i) {
            $board->setFieldValue($x + 1, $i + 1, $row[$i]);
        }
    }

    if ( !$board->isSolved() ) {
        $board->findSolution();
    }

    if ( !$board->isSolved() ) {
        $board->guessSolution();
    }

    $board->cleanPrint(3);

} catch ( Exception $e ) {

    $board->cleanPrint();

    echo $e->getMessage() . PHP_EOL;
}






