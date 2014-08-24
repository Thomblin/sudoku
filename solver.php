<?php

/**
 * Class Solver is used to read a sudoku board, given by a .sdk file, initialize all sudoku classes and to solve the puzzle
 */
class Solver
{
    /**
     * @var Board
     */
    private $board;
    /**
     * @var bool
     */
    private $debug;

    /**
     * @param bool $debug
     */
    public function setDebug($debug)
    {
        $this->debug = $debug;
    }

    /**
     * @param string $sudoku
     */
    public function solve($sudoku)
    {
        $this->createBoard($sudoku);

        if ( !$this->board->isSolved() ) {
            $this->board->findSolution();
        }

        if ( !$this->board->isSolved() ) {
            $this->board->guessSolution();
        }
    }

    private function createBoard($sudoku)
    {
        $rows = explode("\n", trim($sudoku));

        $range = count($rows);

        if ( ceil(sqrt($range)) !== floor(sqrt($range)) ) {
            throw new InvalidArgumentException("sudoku board has to be a square");
        }

        $this->board = new Board($range, $this->debug);

        foreach ($rows as $x => $row) {
            if ($range !== strlen($row)) {
                throw new InvalidArgumentException("invalid row '$row'. Expected length $range");
            }

            for ($i = 0; $i < $range; ++$i) {
                $this->board->setFieldValue($x + 1, $i + 1, $row[$i]);
            }
        }
    }

    public function getBoard()
    {
        return $this->board;
    }
}