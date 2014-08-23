<?php

/**
 * Class Field represents one square on the sudoku board which has to be filled with a specific number
 */
class Field implements FieldObserver
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

        foreach ( $this->groups as $group ) {
            $group->sendUpdate($this, $value);
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

            if ( 1 === count($this->allowedValues) ) {
                $this->setValue(current($this->allowedValues));
            }
        }
    }

    /**
     * @return string
     */
    public function getColor()
    {
        return 1 === count($this->allowedValues)
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

            $string[(int)floor($this->range / 2)] = current($this->allowedValues);
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
}