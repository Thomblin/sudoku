<?php

/**
 * Class Field represents one square on the sudoku board which has to be filled with a specific number
 */
class Field
{
    /**
     * @var int
     */
    private $range;
    /**
     * @var int[]
     */
    private $allowedValues;
    /**
     * @var int[]
     */
    private $snapshot;
    /**
     * @var Group[]
     */
    private $groups;

    /**
     * @param int $range
     */
    public function __construct($range = 9)
    {
        $this->range = $range;
        $this->allowedValues = array_combine(
            range(1, $range, 1),
            range(1, $range, 1)
        );
    }

    /**
     * @param Group $group
     */
    public function addGroup($group)
    {
        $this->groups[] = $group;
    }

    /**
     * @param int $value
     */
    public function setValue($value)
    {
        $this->allowedValues = array($value => $value);

        $this->sendDelete($value);
        $this->sendCheck();
    }

    /**
     * @param int $value
     */
    public function sendDelete($value)
    {
        foreach ( $this->groups as $group ) {
            $group->sendDelete($this, $value);
        }
    }

    public function sendCheck()
    {
        foreach ( $this->groups as $group ) {
            $group->sendCheck();
        }
    }

    /**
     * @param int $value
     */
    public function deleteValue($value)
    {
        if ( isset($this->allowedValues[$value]) ) {
            unset($this->allowedValues[$value]);

            if ( 0 === count($this->allowedValues) ) {
                throw new Exception("no values left");
            }
        }
    }

    /**
     * @param string $name
     */
    public function createSnapshot($name)
    {
        $this->snapshot[$name] = $this->allowedValues;
    }

    /**
     * @param string $name
     */
    public function rollback($name)
    {
        $this->allowedValues = $this->snapshot[$name];
    }

    /**
     * @return string
     */
    public function getColor()
    {
        return $this->isSolved()
            ? 'green'
            : 'blue';
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $string = "";

        $wrap = sqrt($this->range);

        if (1 === count($this->allowedValues)) {
            $string = implode('', array_fill(1, $this->range, " "));

            $string[(int) floor($this->range / 2)] = current($this->allowedValues);
        } else {
            for ($number = 1; $number <= $this->range; ++$number) {
                if (in_array($number, $this->allowedValues)) {
                    $string .= $number;
                } else {
                    $string .= ' ';
                }
                if (0 === $number % $wrap) {
                    $string .= "\n";
                }
            }
        }

        return $string;
    }

    /**
     * @return bool
     */
    public function isSolved()
    {
        return 1 === count($this->allowedValues);
    }

    /**
     * @return int[]
     */
    public function getAllowedValues()
    {
        return $this->allowedValues;
    }
}