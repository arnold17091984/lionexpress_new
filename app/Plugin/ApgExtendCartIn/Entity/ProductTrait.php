<?php

namespace Plugin\ApgExtendCartIn\Entity;


use Doctrine\ORM\Mapping as ORM;
use Eccube\Annotation\EntityExtension;
use Eccube\Entity\Product;
use Eccube\Entity\ProductClass;
use Plugin\ApgExtendCartIn\Entity\Domain\CartType;

/**
 * @EntityExtension("Eccube\Entity\Product")
 * @ORM\EntityListeners()
 */
trait ProductTrait
{

    /**
     * @var integer
     *
     * @ORM\Column(name="plg_apg_cart_type", type="smallint", nullable=false, options={"default":1})
     */
    private $apg_cart_type = CartType::STANDARD;

    /**
     * @return int
     */
    public function getApgCartType(): int
    {
        return $this->apg_cart_type;
    }

    /**
     * @param int $apg_cart_type
     * @return ProductTrait
     */
    public function setApgCartType(int $apg_cart_type): Product
    {
        $this->apg_cart_type = $apg_cart_type;
        return $this;
    }

    /**
     * @param $classId1
     * @param null $classId2
     * @param bool $incTax
     * @return string
     */
    public function getProductClassPrice02($classId1, $classId2 = null, $incTax = true)
    {
        $class = $this->getProductClassByClassId($classId1, $classId2);
        return $incTax ? $class->getPrice02IncTax() : $class->getPrice02();
    }

    /**
     * @param $classId1
     * @param null $classId2
     * @param bool $incTax
     * @return null|string
     */
    public function getProductClassPrice01($classId1, $classId2 = null, $incTax = true)
    {
        $class = $this->getProductClassByClassId($classId1, $classId2);
        return $incTax ? $class->getPrice01IncTax() : $class->getPrice01();
    }

    protected function getProductClassByClassId($classId1, $classId2)
    {
        $classes = $this->getProductClasses();
        /** @var ProductClass $class */
        foreach ($classes as $class) {
            if (empty($classId2)) {
                if (
                    !empty($class->getClassCategory1())
                    && $class->getClassCategory1()->getId() === $classId1
                ) {
                    return $class;
                }
            } else {
                if (
                    !empty($class->getClassCategory1())
                    && !empty($class->getClassCategory2())
                    && $class->getClassCategory1()->getId() === $classId1
                    && $class->getClassCategory2()->getId() === $classId2
                ) {
                    return $class;
                }
            }
        }
        return null;
    }

    public function getClassCategoryNames1()
    {
        if (!$this->hasProductClass()) {
            return [];
        }
        $classCategories1 = $this->getClassCategories1();
        return $classCategories1;
    }

    public function getClassCategoryNames2()
    {
        if (!$this->hasProductClass()) {
            return [];
        }
        $classNames = [];
        $classCategories1 = $this->getClassCategories1();
        foreach ($classCategories1 as $classCategoryId1 => $classCategoryId2) {
            $classCategories = $this->getClassCategories2($classCategoryId1);
            foreach ($classCategories as $classCategoryId2 => $classCategoryName2) {
                if (!isset($classNames[$classCategoryId2])) {
                    $classNames[$classCategoryId2] = $classCategoryName2;
                }
            }
        }
        return $classNames;
    }

    public function hasProductClassByClassId($classCategoryId1, $classCategoryId2)
    {
        return !empty($this->getProductClassByClassId($classCategoryId1, $classCategoryId2));

    }

}