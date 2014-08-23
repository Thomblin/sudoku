<?php

/**
 * Class Board contains all sudoku Fields and observes all Field changes to trigger events between each Field
 */
class Board implements FieldObserver
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
     * @param int $range
     */
    public function __construct($range = 9)
    {
        $this->range = $range;

        $wrap = sqrt($this->range);

        for ( $i = 1; $i <= $range; ++$i ) {
            $this->groups["x_{$i}"] = new Group();
            $this->groups["x_{$i}"]->addFieldObserver($this);
            $this->groups["y_{$i}"] = new Group();
            $this->groups["y_{$i}"]->addFieldObserver($this);
            $this->groups["cell_{$i}"] = new Group();
            $this->groups["cell_{$i}"]->addFieldObserver($this);
        }

        for ($x = 1; $x <= $range; ++$x) {
            for ($y = 1; $y <= $range; ++$y) {

                $field = new Field();

                $cell = ((ceil($x / $wrap) - 1) * $wrap + ceil($y / $wrap));

                $this->fields[$x][$y] = $field;

                $field->addGroup($this->groups["x_{$x}"]);
                $field->addGroup($this->groups["y_{$y}"]);
                $field->addGroup($this->groups["cell_{$cell}"]);

                $this->groups["x_{$x}"]->addFieldObserver($field);
                $this->groups["y_{$y}"]->addFieldObserver($field);
                $this->groups["cell_{$cell}"]->addFieldObserver($field);
            }
        }
    }
    /**
     * @param int $value
     */
    public function deleteValue($value)
    {
        $this->cleanPrint();
        usleep(10000);
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
                $values[$x][$y] = str_replace("\n", "", (string) $this->getField($x, $y));
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
                        for ( $i = 0; $i < $wrap; ++$i ) {
                            $string .= implode(
                                    '',
                                    array_fill(1, $this->range + $wrap -1, self::VERICAL_LINE)
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

    /**
     * clear screen and print this board
     */
    public function cleanPrint()
    {
        $output = (string) $this; // buffer to prevent flickering

        passthru('clear');
        echo $output;
    }
}