<?php

namespace GS\SteamWeaponsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Price
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Price
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @var float
     *
     * @ORM\Column(name="price", type="float")
     */
    private $price;

    /**
     * @var integer
     *
     * @ORM\Column(name="volume", type="integer")
     */
    private $volume;

    /**
     * @ORM\ManyToOne(targetEntity="GS\SteamWeaponsBundle\Entity\Weapon")
     * @ORM\JoinColumn(name="weapon_id", referencedColumnName="id")
     **/
    private $weapon;

    /**
     * @var string
     *
     * @ORM\Column(name="exterior", type="string")
     */
    private $exterior;

    /**
     * @var string
     *
     * @ORM\Column(name="category", type="string")
     */
    private $category;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return Price
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime 
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set price
     *
     * @param float $price
     * @return Price
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return float 
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set volume
     *
     * @param integer $volume
     * @return Price
     */
    public function setVolume($volume)
    {
        $this->volume = $volume;

        return $this;
    }

    /**
     * Get volume
     *
     * @return integer 
     */
    public function getVolume()
    {
        return $this->volume;
    }

    /**
     * Set weapon
     *
     * @param string weapon
     * @return Price
     */
    public function setWeapon($weapon)
    {
        $this->weapon = $weapon;

        return $this;
    }

    /**
     * Get weapon
     *
     * @return string
     */
    public function getWeapon()
    {
        return $this->weapon;
    }

    /**
     * Set exterior
     *
     * @param string exterior
     * @return Price
     */
    public function setExterior($exterior)
    {
        $this->exterior = $exterior;

        return $this;
    }

    /**
     * Get exterior
     *
     * @return string
     */
    public function getExterior()
    {
        return $this->exterior;
    }    
    
    
    /**
     * Set category
     *
     * @param string category
     * @return Price
     */
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }
}
