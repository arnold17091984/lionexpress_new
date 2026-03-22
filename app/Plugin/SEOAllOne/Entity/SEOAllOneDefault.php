<?php

namespace Plugin\SEOAllOne\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SEOAllOneDefault
 * 
 * @ORM\Table(name="plg_seoallone_default")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discriminator_type", type="string", length=255)
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass="Plugin\SEOAllOne\Repository\SEOAllOneDefaultRepository")
 */
class SEOAllOneDefault extends \Eccube\Entity\AbstractEntity
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
     * @var \Eccube\Entity\Page
     * @ORM\OneToOne(targetEntity="\Eccube\Entity\Page")
     * @ORM\JoinColumns({
     *  @ORM\JoinColumn(name="page_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    private $Page;

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
     * @param \Eccube\Entity\Page $page
     * 
     * @return $this;
     */
    public function setPage(\Eccube\Entity\Page $page)
    {
        $this->Page = $page;

        return $this;
    }

    /**
     * @return \Eccube\Entity\Page
     */
    public function getPage()
    {
        return $this->Page;
    }

    /**
     * Set del_flg
     *
     * @param integer $delFlg
     * @return Product
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
}

?>