<?php

namespace Plugin\SEOAllOne\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Config
 *
 * @ORM\Table(name="plg_seoallone_config")
 * @ORM\Entity(repositoryClass="Plugin\SEOAllOne\Repository\ConfigRepository")
 */
class Config extends \Eccube\Entity\AbstractEntity
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
     * @var boolean
     *
     * @ORM\Column(name="rich_snippet_flg", type="boolean", options={"default":false})
     */
    private $rich_snippet_flg;

    /**
     * @var boolean
     *
     * @ORM\Column(name="sns_flg", type="boolean", options={"default":false})
     */
    private $sns_flg;

    /**
     * @var boolean
     *
     * @ORM\Column(name="sitemap_flg", type="boolean", options={"default":false})
     */
    private $sitemap_flg;

    /**
     * @var boolean
     *
     * @ORM\Column(name="pagination_flg", type="boolean", options={"default":false})
     */
    private $pagination_flg;

    /**
     * @var boolean
     *
     * @ORM\Column(name="breadcrumb_flg", type="boolean", options={"default":false})
     */
    private $breadcrumb_flg;

    /**
     * @var int
     *
     * @ORM\Column(name="facebook_flg", type="boolean", options={"default":false})
     */
    private $facebook_flg;

    /**
     * @var int
     *
     * @ORM\Column(name="twitter_flg", type="boolean", options={"default":false})
     */
    private $twitter_flg;

    /**
     * @var int
     *
     * @ORM\Column(name="line_flg", type="boolean", options={"default":false})
     */
    private $line_flg;

    /**
     * @var int
     *
     * @ORM\Column(name="shop_name_top_flg", type="boolean", options={"default":true})
     */
    private $shop_name_top_flg;

    /**
     * @var int
     *
     * @ORM\Column(name="global_id_flg", type="boolean", options={"default":false})
     */
    private $global_id_flg;

    /**
     * @var int
     *
     * @ORM\Column(name="global_id_type", type="integer", nullable=true)
     */
    private $global_id_type;

    /**
     * @var int
     *
     * @ORM\Column(name="valid_price_flg", type="boolean", options={"default":false})
     */
    private $valid_price_flg;

    /**
     * @var int
     *
     * @ORM\Column(name="valid_price_month", type="integer", nullable=true)
     */
    private $valid_price_month;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return boolean
     */
    public function getRichSnippetFlg()
    {
        return $this->rich_snippet_flg;
    }

    /**
     * @param boolean $rich_snippet_flg
     *
     * @return $this;
     */
    public function setRichSnippetFlg($rich_snippet_flg)
    {
        $this->rich_snippet_flg = $rich_snippet_flg;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getSnsFlg()
    {
        return $this->sns_flg;
    }

    /**
     * @param boolean $sns_flg
     *
     * @return $this;
     */
    public function setSnsFlg($sns_flg)
    {
        $this->sns_flg = $sns_flg;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getSitemapFlg()
    {
        return $this->sitemap_flg;
    }

    /**
     * @param boolean $sitemap_flg
     *
     * @return $this;
     */
    public function setSitemapFlg($sitemap_flg)
    {
        $this->sitemap_flg = $sitemap_flg;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getPaginationFlg()
    {
        return $this->pagination_flg;
    }

    /**
     * @param boolean $pagination_flg
     *
     * @return $this;
     */
    public function setPaginationFlg($pagination_flg)
    {
        $this->pagination_flg = $pagination_flg;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getBreadCrumbFlg()
    {
        return $this->breadcrumb_flg;
    }

    /**
     * @param boolean $breadcrumb_flg
     *
     * @return $this;
     */
    public function setBreadCrumbFlg($breadcrumb_flg)
    {
        $this->breadcrumb_flg = $breadcrumb_flg;

        return $this;
    }

    /**
     * Set facebook_flg
     *
     * @param boolean $facebook_flg
     * @return $this;
     */
    public function setFacebookFlg($facebook_flg)
    {
        $this->facebook_flg = $facebook_flg;

        return $this;
    }

    /**
     * Get facebook_flg
     *
     * @return boolean 
     */
    public function getFacebookFlg()
    {
        return $this->facebook_flg;
    }

    /**
     * Set twitter_flg
     *
     * @param boolean $twitter_flg
     * @return $this;
     */
    public function setTwitterFlg($twitter_flg)
    {
        $this->twitter_flg = $twitter_flg;

        return $this;
    }

    /**
     * Get twitter_flg
     *
     * @return boolean 
     */
    public function getTwitterFlg()
    {
        return $this->twitter_flg;
    }

    /**
     * Set line_flg
     *
     * @param boolean $line_flg
     * @return $this;
     */
    public function setLineFlg($line_flg)
    {
        $this->line_flg = $line_flg;

        return $this;
    }

    /**
     * Get line_flg
     *
     * @return boolean 
     */
    public function getLineFlg()
    {
        return $this->line_flg;
    }

    /**
     * Set shop_name_top_flg
     *
     * @param boolean $shop_name_top_flg
     * @return $this;
     */
    public function setShopNameTopFlg($shop_name_top_flg)
    {
        $this->shop_name_top_flg = $shop_name_top_flg;

        return $this;
    }

    /**
     * Get shop_name_top_flg
     *
     * @return boolean 
     */
    public function getShopNameTopFlg()
    {
        return $this->shop_name_top_flg;
    }

    /**
     * Set global_id_flg
     *
     * @param boolean $global_id_flg
     * @return $this;
     */
    public function setGlobalIdFlg($global_id_flg)
    {
        $this->global_id_flg = $global_id_flg;

        return $this;
    }

    /**
     * Get global_id_flg
     *
     * @return boolean 
     */
    public function getGlobalIdFlg()
    {
        return $this->global_id_flg;
    }

    /**
     * Set global_id_type
     *
     * @param integer $global_id_type
     * @return $this;
     */
    public function setGlobalIdType($global_id_type)
    {
        $this->global_id_type = $global_id_type;

        return $this;
    }

    /**
     * Get global_id_type
     *
     * @return integer 
     */
    public function getGlobalIdType()
    {
        return $this->global_id_type;
    }

    /**
     * Set valid_price_flg
     *
     * @param boolean $valid_price_flg
     * @return $this;
     */
    public function setValidPriceFlg($valid_price_flg)
    {
        $this->valid_price_flg = $valid_price_flg;

        return $this;
    }

    /**
     * Get valid_price_flg
     *
     * @return boolean 
     */
    public function getValidPriceFlg()
    {
        return $this->valid_price_flg;
    }

    /**
     * Set valid_price_month
     *
     * @param integer $valid_price_month
     * @return $this;
     */
    public function setValidPriceMonth($valid_price_month)
    {
        $this->valid_price_month = $valid_price_month;

        return $this;
    }

    /**
     * Get valid_price_month
     *
     * @return integer 
     */
    public function getValidPriceMonth()
    {
        return $this->valid_price_month;
    }
}
