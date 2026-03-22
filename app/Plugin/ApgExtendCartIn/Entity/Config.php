<?php

namespace Plugin\ApgExtendCartIn\Entity;

use Doctrine\ORM\Mapping as ORM;
use Plugin\ApgExtendCartIn\Entity\Domain\CartType;
use Plugin\ApgExtendCartIn\Entity\Domain\ConfigSettingType;

/**
 * Config
 *
 * @ORM\Table(name="plg_apg_extend_cart_in_config")
 * @ORM\Entity(repositoryClass="Plugin\ApgExtendCartIn\Repository\ConfigRepository")
 */
class Config
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", options={"unsigned":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var integer|null
     *
     * @ORM\Column(name="cart_type", type="smallint", nullable=false, options={"default":1})
     */
    private $cart_type = CartType::STANDARD;

    /**
     * @var integer
     *
     * @ORM\Column(name="setting_type", type="smallint", nullable=false, options={"default":1})
     */
    private $setting_type = ConfigSettingType::ALL;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int|null
     */
    public function getCartType(): ?int
    {
        return $this->cart_type;
    }

    /**
     * @param int|null $cart_type
     * @return Config
     */
    public function setCartType(?int $cart_type): Config
    {
        $this->cart_type = $cart_type;
        return $this;
    }


    /**
     * @return int
     */
    public function getSettingType(): int
    {
        return $this->setting_type;
    }

    /**
     * @param int $setting_type
     * @return Config
     */
    public function setSettingType(int $setting_type): Config
    {
        $this->setting_type = $setting_type;
        return $this;
    }


}
