<?php

/**
 * Class Group receives Field changes and triggers them to all other Fields of the same group
 */
class Group
{
    /**
     * @var int
     */
    private $range;
    /**
     * @var Field[]
     */
    private $fields = array();
    /**
     * @var callback
     */
    private $callback;


    /**
     * @param int      $range
     * @param callback $callback
     */
    public function __construct($range = 9, $callback)
    {
        $this->range = $range;
        $this->callback = $callback;
    }

    /**
     * @param Field $field
     */
    public function addField(Field $field)
    {
        $this->fields[] = $field;
    }

    /**
     * @param Field $field
     * @param int $value
     */
    public function sendDelete(Field $field, $value)
    {
        $this->sendUpdateToObservers($field, $value);

        $this->assertThatGroupIsValid();

        call_user_func($this->callback);
    }

    /**
     * @param Field $field
     * @param int           $value
     */
    private function sendUpdateToObservers(Field $field, $value)
    {
        foreach ($this->fields as $observer) {
            if ($observer !== $field) {
                $observer->deleteValue($value);
            }
        }
    }

    /**
     * @return Field
     *
     * @throws Exception
     */
    private function assertThatGroupIsValid()
    {
        $allowedValues = array();
        foreach ($this->fields as $observer) {
            $allowedValues = array_merge($allowedValues, $observer->getAllowedValues());
        }

        if ($this->range != count(array_unique($allowedValues))) {
            throw new Exception('impossible group ' . print_r($allowedValues, true));
        }
    }

    public function sendCheck()
    {
        $this->findSingleValuesInGroup();
    }

    public function findSingleValuesInGroup()
    {
        $allowedValues = array_fill(1, $this->range, 0);
        /** @var Field[] $map */
        $map = array();

        foreach ($this->fields as $observe) {
            $observer = clone $observe;
            if (1 < count($observer->getAllowedValues())) {
                foreach ($observer->getAllowedValues() as $checkValue) {
                    if (isset($allowedValues[$checkValue])) {
                        ++$allowedValues[$checkValue];
                        $map[$checkValue] = $observe;
                    }
                }
            } else {
                unset($allowedValues[current($observer->getAllowedValues())]);
            }
        }

        foreach ($allowedValues as $checkValue => $count) {
            if (1 === $count) {
                $map[$checkValue]->setValue($checkValue);
            }
        }
    }
}