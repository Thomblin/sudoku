<?php

/**
 * Class Group receives Field changes and triggers them to all other Fields of the same group
 */
class Group
{
    /**
     * @var FieldObserver[]
     */
    private $fields = array();

    /**
     * @param FieldObserver $field
     */
    public function addFieldObserver(FieldObserver $field)
    {
        $this->fields[] = $field;
    }

    /**
     * @param FieldObserver $field
     * @param int           $value
     */
    public function sendUpdate(FieldObserver $field, $value)
    {
        foreach ( $this->fields as $observer ) {
            if ( $observer !== $field ) {
                $observer->deleteValue($value);
            }
        }
    }
}