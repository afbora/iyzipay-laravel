<?php

namespace Afbora\IyzipayLaravel\StorableClasses;

abstract class StorableClass
{

    /**
     * StorableClass constructor.
     *
     * @param array $attributes
     *
     * @throws \Exception
     */
    public function __construct(array $attributes = [])
    {
        if (!empty($attributes)) {
            $this->setAttributes($attributes)
                ->validate();
        }
    }

    public function __toString(): string
    {
        return json_encode($this);
    }

    private function setAttributes(array $attributes = []): StorableClass
    {
        foreach ($attributes as $attr => $value) {
            $attr = camel_case($attr);
            $this->$attr = $value;
        }

        return $this;
    }

    /**
     * @throws \Exception
     */
    private function validate()
    {
        foreach ($this as $attr => $value) {
            if (empty($value)) {
                $exceptionClassName = $this->getFieldExceptionClass();
                throw new $exceptionClassName($attr . ' cannot be blank!');
            }
        }
    }

    abstract protected function getFieldExceptionClass(): string;
}
