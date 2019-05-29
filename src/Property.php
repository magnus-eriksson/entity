<?php namespace Maer\Entity;

class Property
{
    /**
     * The property's data type
     * @var integer
     */
    protected $type  = 0;

    /**
     * The property's default value
     * @var null
     */
    protected $value = null;


    /**
     * @param int   $type
     * @param mixed $value
     */
    public function __construct(int $type, $value)
    {
        $this->type  = $type;
        $this->value = $value;
    }


    /**
     * Get the property type
     *
     * @return int
     */
    public function type(): int
    {
        return $this->type;
    }


    /**
     * Get the property's default value
     *
     * @return mixed
     */
    public function value()
    {
        return $this->value;
    }
}
