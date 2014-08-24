<?php

/**
 * Class Board contains all sudoku Fields and observes all Field changes to trigger events between each Field
 */
class Board
{
    const BOLD_LINE = '*';
    const VERICAL_LINE = '-';
    const HORIZONTAL_LINE = '|';

    /**
     * @var int
     */
    private $range;

    /**
     * @var Field[]
     */
    private $fields = array();

    /**
     * @var Group[]
     */
    private $groups = array();
    /**
     * @var int[]
     */
    private $path = array();
    /**
     * @var int
     */
    private $errors = 0;

    /**
     * @param int $range
     */
    public function __construct($range = 9)
    {
        $this->range = $range;

        $wrap = sqrt($this->range);

        $self = $this;
        $callback = function () use($self) {
            $self->cleanPrint();
        };

        for ($i = 1; $i <= $range; ++$i) {
            $this->groups["x_{$i}"] = new Group($range, $callback);
            $this->groups["y_{$i}"] = new Group($range, $callback);
            $this->groups["cell_{$i}"] = new Group($range, $callback);
        }

        for ($x = 1; $x <= $range; ++$x) {
            for ($y = 1; $y <= $range; ++$y) {

                $field = new Field();

                $cell = ((ceil($x / $wrap) - 1) * $wrap + ceil($y / $wrap));

                $this->fields[$x][$y] = $field;

                $field->addGroup($this->groups["x_{$x}"]);
                $field->addGroup($this->groups["y_{$y}"]);
                $field->addGroup($this->groups["cell_{$cell}"]);

                $this->groups["x_{$x}"]->addField($field);
                $this->groups["y_{$y}"]->addField($field);
                $this->groups["cell_{$cell}"]->addField($field);
            }
        }
    }

    /**
     * clear screen and print this board
     */
    public function cleanPrint($level = 0)
    {
        static $best = 0, $buffer;

        if ( $level > 2 ) {

            $solved = $this->getSolvedCount();
            if ( $best < $solved ) {
                $buffer = (string) $this;
                $best = $solved;
            }

            $output = (string)$this; // buffer to prevent flickering

            $linesBuffer = explode("\n", $buffer);
            $linesOutput = explode("\n", $output);

            $rows = count($linesBuffer);

            $out = '';
            for ( $i = 0; $i < $rows; ++$i ) {
                $out .= $linesOutput[$i] . '     ' . $linesBuffer[$i] . "\n";
            }

            passthru('clear');
            echo $out;
            usleep(1);
        }

        if ( $level > 1 ) {
            echo implode('; ', $this->path) . "\n";
            echo "[{$this->errors} / $solved / $best]\n";
        }
    }

    /**
     * @param int $value
     */
    public function deleteValue($value)
    {
        $this->cleanPrint();
    }

    /**
     * @param int $value
     */
    public function setValue($value)
    {

    }

    /**
     * @return int[]
     */
    public function getAllowedValues()
    {
        return array();
    }

    /**
     * @param int $x
     * @param int $y
     * @param int $value
     */
    public function setFieldValue($x, $y, $value)
    {
        if ('#' !== $value) {
            $this->getField($x, $y)->setValue($value);
        }
    }

    /**
     * @param int $x
     * @param int $y
     *
     * @return Field
     */
    private function getField($x, $y)
    {
        return $this->fields[$x][$y];
    }

    public function sendCheck($x, $y)
    {
        $this->getField($x, $y)->sendCheck();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $colors = new Colors();

        $string = "";

        $wrap = sqrt($this->range);

        $values = array();

        for ($x = 1; $x <= $this->range; ++$x) {
            for ($y = 1; $y <= $this->range; ++$y) {
                $values[$x][$y] = str_replace("\n", "", (string)$this->getField($x, $y));
            }
        }

        $string .= implode('', array_fill(1, $this->range * $wrap + $this->range + 1, self::BOLD_LINE));
        $string .= "\n" . self::BOLD_LINE;

        $x = $y = $z = 1;
        do {
            $string .= $colors->getColoredString(
                substr($values[$x][$y], ($z - 1) * $wrap, $wrap),
                $this->getField($x, $y)->getColor()
            );

            if (0 === $y % $wrap) {
                $string .= self::BOLD_LINE;
            } else {
                $string .= self::HORIZONTAL_LINE;
            }

            ++$y;
            if ($y > $this->range) {
                ++$z;
                $y = 1;

                $string .= "\n" . self::BOLD_LINE;

                if ($z > $wrap) {
                    $z = 1;
                    ++$x;

                    if (1 === $x % $wrap) {
                        $string .= implode('', array_fill(1, $this->range * $wrap + $this->range, self::BOLD_LINE));
                    } else {
                        for ($i = 0; $i < $wrap; ++$i) {
                            $string .= implode(
                                    '',
                                    array_fill(1, $this->range + $wrap - 1, self::VERICAL_LINE)
                                ) . self::BOLD_LINE;
                        }
                    }
                    $string .= "\n";

                    if ($x > $this->range) {
                        break;
                    }

                    $string .= self::BOLD_LINE;
                }
            }
        } while (true);

        $string .= "\n";

        return $string;
    }


    public function findSolution()
    {
        foreach ( $this->groups as $group ) {
            $group->findSingleValuesInGroup();
        }
    }

    public function guessSolution($try = 1, $path = array())
    {
        if ( $this->isSolved() ) {
            return;
        }

        $path = $this->setDebugInfo($try, $path);

        $this->triggerSnapshot($try);

        $guess = $this->getValuesToBeTested();

        $this->setTestValue($try, $path, $guess);
    }

    /**
     * @return bool
     */
    public function isSolved()
    {
        $solved = true;

        for ($x = 1; $x <= $this->range; ++$x) {
            for ($y = 1; $y <= $this->range; ++$y) {
                if (!$this->getField($x, $y)->isSolved()) {
                    $solved = false;
                    break 2;
                }
            }
        }

        return $solved;
    }

    /**
     * @return bool
     */
    public function getSolvedCount()
    {
        $count = 0;

        for ($x = 1; $x <= $this->range; ++$x) {
            for ($y = 1; $y <= $this->range; ++$y) {
                if ($this->getField($x, $y)->isSolved()) {
                    ++$count;
                }
            }
        }

        return $count;
    }

    /**
     * @param int   $try
     * @param array $path
     *
     * @return array
     */
    private function setDebugInfo($try, $path)
    {
        $path[] = $try;

        $this->path = $path;

        return $path;
    }

    /**
     * @param $try
     * @return array
     */
    private function triggerSnapshot($try)
    {
        for ($x = 1; $x <= $this->range; ++$x) {
            for ($y = 1; $y <= $this->range; ++$y) {
                $this->getField($x, $y)->createSnapshot($try);
            }
        }
        return array($x, $y);
    }

    /**
     * @return Field[]
     */
    private function getValuesToBeTested()
    {
        /** @var Field[] $guess */
        $guess = array();

        for ($x = 1; $x <= $this->range; ++$x) {
            for ($y = 1; $y <= $this->range; ++$y) {
                $field = $this->getField($x, $y);

                if (!$field->isSolved()) {
                    $guess[] = $field;
                }
            }
        }

        uasort($guess, function (Field $field1, Field $field2) {
            $c1 = count($field1->getAllowedValues());
            $c2 = count($field2->getAllowedValues());

            if ($c1 === $c2) {
                return 0;
            }

            return $c1 < $c2 ? -1 : 1;
        });

        return $guess;
    }

    /**
     * @param int     $try
     * @param array   $path
     * @param Field[] $guess
     */
    private function setTestValue($try, $path, $guess)
    {
        foreach ($guess as $indexField => $field) {
            foreach ($field->getAllowedValues() as $value) {
                array_push($this->path, "[$indexField; $value]");
                try {

                    $field->setValue($value);

                    if ($this->isSolved()) {
                        break 2;
                    }

                    array_pop($this->path);

                    $this->guessSolution($this->getNextTry(), $path);

                    if ($this->isSolved()) {
                        break 2;
                    }

                } catch (Exception $e) {
                    array_pop($this->path);
                    ++$this->errors;
                }

                for ($x = 1; $x <= $this->range; ++$x) {
                    for ($y = 1; $y <= $this->range; ++$y) {
                        $this->getField($x, $y)->rollback($try);
                    }
                }
            }
        }
    }

    private function getNextTry()
    {
        static $try = 1;

        return ++$try;
    }
}