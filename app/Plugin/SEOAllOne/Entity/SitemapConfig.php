<?php

namespace Plugin\SEOAllOne\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SitemapConfig
 *
 * @ORM\Table(name="plg_seoallone_sitemap_config")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discriminator_type", type="string", length=255)
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass="Plugin\SEOAllOne\Repository\SitemapConfigRepository")
 */
class SitemapConfig extends \Eccube\Entity\AbstractEntity
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
     * @var string
     *
     * @ORM\Column(name="url_name", type="text", nullable=true)
     */
    private $url_name;

    /**
     * @var string
     * 
     * @ORM\Column(name="changefreq", type="text", nullable=true)
     */
    private $changefreq;

    /**
     * @var string
     * 
     * @ORM\Column(name="priority", type="text", nullable=true)
     */
    private $priority;

    /**
     * @var \Eccube\Entity\Page
     * @ORM\OneToOne(targetEntity="\Eccube\Entity\Page")
     * @ORM\JoinColumns({
     *  @ORM\JoinColumn(name="page_id", referencedColumnName="id")
     * })
     */
    private $Page;

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
    public function getUrlName()
    {
        return $this->url_name;
    }

    /**
     * @param string $urlName
     *
     * @return $this;
     */
    public function setUrlName($urlName)
    {
        $this->url_name = $urlName;

        return $this;
    }

    /**
     * @return string
     */
    public function getChangeFreq()
    {
        return $this->changefreq;
    }

    /**
     * @param string $changeFreq
     *
     * @return $this;
     */
    public function setChangeFreq($changeFreq)
    {
        $this->changefreq = $changeFreq;

        return $this;
    }

    /**
     * @return string
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param string $priority
     *
     * @return $this;
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;

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
}
