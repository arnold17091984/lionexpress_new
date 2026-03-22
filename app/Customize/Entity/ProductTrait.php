<?php

namespace Customize\Entity;

use Eccube\Annotation\EntityExtension;

/**
 * @EntityExtension("Eccube\Entity\Product")
 */
trait ProductTrait
{
    /**
     * 服用方法・使用方法
     * @var string|null
     *
     * @ORM\Column(name="take_or_use", type="text", nullable=true)
     */
    private $take_or_use;

    /**
     * 副作用
     * @var string|null
     *
     * @ORM\Column(name="side_effects", type="text", nullable=true)
     */
    private $side_effects;

    /**
     * 併用禁止薬
     * @var string|null
     *
     * @ORM\Column(name="prohibited", type="text", nullable=true)
     */
    private $prohibited;

    /**
     * 保管方法
     * @var string|null
     *
     * @ORM\Column(name="storage", type="text", nullable=true)
     */
    private $storage;

    /**
     * メーカー
     * @var string|null
     *
     * @ORM\Column(name="maker", type="text", nullable=true)
     */
    private $maker;

    /**
     * 発送国
     * @var string|null
     *
     * @ORM\Column(name="shipping_country", type="text", nullable=true)
     */
    private $shipping_country;


    /**
     * Set take_or_use.
     *
     * @param string|null $take_or_use
     *
     * @return ProductTrait
     */
    public function setTakeOrUse($take_or_use)
    {
        $this->take_or_use = $take_or_use;
        return $this;
    }

    /**
     * Get take_or_use.
     *
     * @return string
     */
    public function getTakeOrUse()
    {
        return $this->take_or_use;
    }

    /**
     * Set side_effects.
     *
     * @param string|null $side_effects
     *
     * @return ProductTrait
     */
    public function setSideEffects($side_effects)
    {
        $this->side_effects = $side_effects;
        return $this;
    }

    /**
     * Get side_effects.
     *
     * @return string
     */
    public function getSideEffects()
    {
        return $this->side_effects;
    }

    /**
     * Set prohibited.
     *
     * @param string|null $prohibited
     *
     * @return ProductTrait
     */
    public function setProhibited($prohibited)
    {
        $this->prohibited = $prohibited;
        return $this;
    }

    /**
     * Get prohibited.
     *
     * @return string
     */
    public function getProhibited()
    {
        return $this->prohibited;
    }

    /**
     * Set storage.
     *
     * @param string|null $storage
     *
     * @return ProductTrait
     */
    public function setStorage($storage)
    {
        $this->storage = $storage;
        return $this;
    }

    /**
     * Get storage.
     *
     * @return string
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * Set maker.
     *
     * @param string|null $maker
     *
     * @return ProductTrait
     */
    public function setMaker($maker)
    {
        $this->maker = $maker;
        return $this;
    }

    /**
     * Get maker.
     *
     * @return string
     */
    public function getMaker()
    {
        return $this->maker;
    }

    /**
     * Set shipping_country.
     *
     * @param string|null $shipping_country
     *
     * @return ProductTrait
     */
    public function setShippingCountry($shipping_country)
    {
        $this->shipping_country = $shipping_country;
        return $this;
    }

    /**
     * Get shipping_country.
     *
     * @return string
     */
    public function getShippingCountry()
    {
        return $this->shipping_country;
    }

}
