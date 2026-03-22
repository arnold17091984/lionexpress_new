<?php

namespace Plugin\LecastTableList41\Entity;

use Doctrine\ORM\Mapping as ORM;
/**
 * Class RelatedProduct.
 *
 * @ORM\Table(name="plg_table_template")
 * @ORM\Entity(repositoryClass="Plugin\LecastTableList41\Repository\TableTemplateRepository")
 */
class TableTemplate extends \Eccube\Entity\AbstractEntity
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
     * @ORM\Column(name="template_name", type="string", length=255)
     */
    private $template_name;

    /**
     * @var string
     *
     * @ORM\Column(name="template_value", type="text")
     */
    private $template_value;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get template_name.
     *
     * @return string
     */
    public function getTemplateName()
    {
        return $this->template_name;
    }

    /**
     * Set template_name.
     *
     * @param string $template_name
     *
     * @return string
     */
    public function setTemplateName($template_name)
    {
        $this->template_name = $template_name;

        return $this;
    }

    /**
     * Get template_value.
     *
     * @return string
     */
    public function getTemplateValue()
    {
        return $this->template_value;
    }

    /**
     * Set template_value.
     *
     * @param string $template_value
     *
     * @return string
     */
    public function setTemplateValue($template_value)
    {
        $this->template_value = $template_value;

        return $this;
    }

    public function buildHtml($json, $disp = false){
        if(!$json){
            return '';
        }
        $data = json_decode($json);
        $html = '';
        foreach ($data as $tr){
            $html .= '<tr>';
            foreach ($tr as $key){
                $cell = ($key[0] === 'th')? '<td class="itl_caption">': '<td>';
                $close = '</td>';
                if($key[0] === 'th' && $disp){
                    $cell = '<th>';
                    $close = '</th>';
                }
                $val = ($disp)? $key[1]: htmlspecialchars($key[1]);
                $html .= $cell. $val. $close;
            }
            $html .= '</tr>';
        }
        return $html;
    }

}