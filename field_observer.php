<?php

/**
 * Interface FieldObserver must be implemented to get updates if a Field gets to know its only possible value
 */
interface FieldObserver
{
    /**
     * @param int $value
     */
    public function deleteValue($value);
}