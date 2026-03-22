<?php

namespace Customize\Entity;

use Eccube\Annotation\EntityExtension;

/**
 * @EntityExtension("Eccube\Entity\ProductClass")
 */
trait ProductClassTrait
{

    /**
     * @var string|null
     *
     * @ORM\Column(name="unit", type="string", nullable=true)
     */
    private $unit;

    /**
     * @var string
     *
     * @ORM\Column(name="add_point", type="decimal", precision=12, scale=0, options={"unsigned":true,"default":0})
     */
    private $add_point = '0';

    /**
     * @var string|null
     *
     * @ORM\Column(name="remarks", type="string", length=255, nullable=true)
     */
    private $remarks;

    /**
     * Set unit.
     *
     * @param string|null $unit
     *
     * @return self
     */
    public function setUnit($unit)
    {
        $this->unit = $unit;

        return $this;
    }

    /**
     * Get unit.
     *
     * @return string|null
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * Set addPoint.
     *
     * @param string $addPoint
     *
     * @return self
     */
    public function setAddPoint($addPoint)
    {
        $this->add_point = $addPoint;

        return $this;
    }

    /**
     * Get addPoint.
     *
     * @return string
     */
    public function getAddPoint()
    {
        return $this->add_point;
    }

    /**
     * Set remarks.
     *
     * @param string|null $remarks
     *
     * @return self
     */
    public function setRemarks($remarks)
    {
        $this->remarks = $remarks;

        return $this;
    }

    /**
     * Get remarks.
     *
     * @return string|null
     */
    public function getRemarks()
    {
        return $this->remarks;
    }

}
