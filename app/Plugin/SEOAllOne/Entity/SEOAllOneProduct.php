<?php

namespace Plugin\SEOAllOne\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SEOAllOneProduct
 * 
 * @ORM\Table(name="plg_seoallone_product")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discriminator_type", type="string", length=255)
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass="Plugin\SEOAllOne\Repository\SEOAllOneProductRepository")
 */
class SEOAllOneProduct extends \Eccube\Entity\AbstractEntity
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
     * @var \Eccube\Entity\Product
     * @ORM\OneToOne(targetEntity="\Eccube\Entity\Product")
     * @ORM\JoinColumns({
     *  @ORM\JoinColumn(name="product_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    private $Product;

    /**
     * @var int
     *
     * @ORM\Column(name="global_id_type", type="integer", nullable=true)
     */
    private $global_id_type;
 
    /**
     * @var string
     * 
     * @ORM\Column(name="global_id", type="string", nullable=true, length=255)
     */
     private $global_id;

    /**
     * @var int
     *
     * @ORM\Column(name="valid_price_month", type="integer", nullable=true)
     */
    private $valid_price_month;

    /**
     * @var string
     * 
     * @ORM\Column(name="title", type="text", nullable=true)
     */
    private $title;

    /**
     * @var string
     * 
     * @ORM\Column(name="author", type="text", nullable=true)
     */
    private $author;

    /**
     * @var string
     * 
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var string
     * 
     * @ORM\Column(name="keyword", type="text", nullable=true)
     */
    private $keyword;

    /**
     * @var string
     * 
     * @ORM\Column(name="canonical", type="text", nullable=true)
     */
    private $canonical;

    /**
     * @var string
     * 
     * @ORM\Column(name="og_title", type="text", nullable=true)
     */
    private $og_title;

     /**
      * @var string
      * 
      * @ORM\Column(name="og_description", type="text", nullable=true)
      */
    private $og_description;
 
    /**
     * @var string
     * 
     * @ORM\Column(name="og_site_name", type="string", nullable=true, length=255)
     */
     private $og_site_name;

     /**
      * @var string
      * 
      * @ORM\Column(name="og_type", type="string", nullable=true, length=255)
      */
     private $og_type;
 
 
     /**
      * @var string
      * 
      * @ORM\Column(name="og_url", type="text", nullable=true, length=65535)
      */
     private $og_url;
 
     /**
      * @var string
      * 
      * @ORM\Column(name="og_image", type="text", nullable=true, length=65535)
      */
     private $og_image;

    /**
     * @var int
     * 
     * @ORM\Column(name="noindex_flg", type="smallint", options={"unsigned":true})
     */
    private $noindex_flg = 0;

    /**
     * @var string
     * 
     * @ORM\Column(name="redirect_url", type="text", nullable=true)
     */
    private $redirect_url;

    /**
     * @var int
     * 
     * @ORM\Column(name="del_flg", type="smallint", options={"unsigned":true})
     */
    private $del_flg;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_date", type="datetimetz")
     */
    private $create_date;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="update_date", type="datetimetz")
     */
    private $update_date;

    /**
     * @var int
     *
     * @ORM\Column(name="updated_flg", type="boolean", options={"default":false})
     */
    private $updated_flg;

    /**
     * Constructor
     */
    public function __construct()
    {
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
     * @return string
     */
    public function getGlobalId()
    {
        return $this->global_id;
    }

    /**
     * @param string $global_id
     * 
     * @return $this;
     */
    public function setGlobalId($global_id)
    {
        $this->global_id = $global_id;

        return $this;
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

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     * 
     * @return $this;
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @param string $author
     * 
     * @return $this;
     */
    public function setAuthor($author)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     * 
     * @return $this;
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getKeyword()
    {
        return $this->keyword;
    }

    /**
     * @param string $keyword
     * 
     * @return $this;
     */
    public function setKeyword($keyword)
    {
        $this->keyword = $keyword;

        return $this;
    }

    /**
     * @return string
     */
    public function getCanonical()
    {
        return $this->canonical;
    }

    /**
     * @param string $canonical
     * 
     * @return $this;
     */
    public function setCanonical($canonical)
    {
        $this->canonical = $canonical;

        return $this;
    }

    /**
     * @return string
     */
     public function getOGTitle()
    {
        return $this->og_title;
    }
 
     /**
      * @param string $og_title
      * 
      * @return $this;
      */
     public function setOGTitle($og_title)
    {
        $this->og_title = $og_title;

        return $this;
    }

    /**
     * @return string
     */
     public function getOGDescription()
    {
        return $this->og_description;
    }
 
     /**
      * @param string $og_description
      * 
      * @return $this;
      */
     public function setOGDescription($og_description)
    {
        $this->og_description = $og_description;

        return $this;
    }

    /**
     * @return string
     */
    public function getOGSiteName()
    {
        return $this->og_site_name;
    }
 
     /**
      * @param string $og_site_name
      * 
      * @return $this;
      */
    public function setOGSiteName($og_site_name)
    {
        $this->og_site_name = $og_site_name;

        return $this;
    }

    /**
     * @return string
     */
    public function getOGType()
    {
        return $this->og_type;
    }
 
     /**
      * @param string $og_type
      * 
      * @return $this;
      */
    public function setOGType($og_type)
    {
        $this->og_type = $og_type;

        return $this;
    }

    /**
     * @return string
     */
    public function getOGUrl()
    {
        return $this->og_url;
    }
 
     /**
      * @param string $og_url
      * 
      * @return $this;
      */
    public function setOGUrl($og_url)
    {
        $this->og_url = $og_url;

        return $this;
    }

    /**
     * @return string
     */
    public function getOGImage()
    {
        return $this->og_image;
    }
 
     /**
      * @param string $og_image
      * 
      * @return $this;
      */
    public function setOGImage($og_image)
    {
        $this->og_image = $og_image;

        return $this;
    }

    /**
     * @param \Eccube\Entity\Product $product
     * 
     * @return $this;
     */
    public function setProduct(\Eccube\Entity\Product $product)
    {
        $this->Product = $product;

        return $this;
    }

    /**
     * @return \Eccube\Entity\Product
     */
    public function getProduct()
    {
        return $this->Product;
    }

    /**
     * Set noindex_flg
     * 
     * @param integer $noindex_flg
     * @return $this;
     */
    public function setNoindexFlg($noindex_flg)
    {
        $this->noindex_flg = $noindex_flg;

        return $this;
    }

    /**
     * Get noindex_flg
     * 
     * @return integer
     */
    public function getNoindexFlg()
    {
        return $this->noindex_flg;
    }

    /**
     * Set redirect_url
     * 
     * @param string $redirect_url
     * @return $this;
     */
    public function setRedirectUrl($redirect_url)
    {
        $this->redirect_url = $redirect_url;

        return $this;
    }

    /**
     * Get redirect_url
     * 
     * @return string
     */
    public function getRedirectUrl()
    {
        return $this->redirect_url;
    }

    /**
     * Set del_flg
     *
     * @param integer $delFlg
     * @return $this;
     */
    public function setDelFlg($delFlg)
    {
        $this->del_flg = $delFlg;

        return $this;
    }

    /**
     * Get del_flg
     *
     * @return integer 
     */
    public function getDelFlg()
    {
        return $this->del_flg;
    }

    /**
     * Set create_date
     *
     * @param \DateTime $createDate
     * @return $this
     */
    public function setCreateDate($createDate)
    {
        $this->create_date = $createDate;

        return $this;
    }

    /**
     * Get create_date
     *
     * @return \DateTime 
     */
    public function getCreateDate()
    {
        return $this->create_date;
    }

    /**
     * Set update_date
     *
     * @param \DateTime $updateDate
     * @return $this
     */
    public function setUpdateDate($updateDate)
    {
        $this->update_date = $updateDate;

        return $this;
    }

    /**
     * Get update_date
     *
     * @return \DateTime 
     */
    public function getUpdateDate()
    {
        return $this->update_date;
    }

    /**
     * Set updated_flg
     *
     * @param boolean $updated_flg
     * @return $this;
     */
    public function setUpdatedFlg($updated_flg)
    {
        $this->updated_flg = $updated_flg;

        return $this;
    }

    /**
     * Get updated_flg
     *
     * @return boolean 
     */
    public function getUpdatedFlg()
    {
        return $this->updated_flg;
    }
}

?>