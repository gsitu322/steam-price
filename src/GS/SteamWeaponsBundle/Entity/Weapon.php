<?php

namespace GS\SteamWeaponsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Weapon
 *
 * @ORM\Table("weapon")
 * @ORM\Entity
 */
class Weapon
{
    const EXTERIOR_FIELD_TESTED     = 'Field-Tested';
    const EXTERIOR_MINIMAL_WEAR     = 'Minimal Wear';
    const EXTERIOR_BATTLE_SCARRED   = 'Battle-Scarred';
    const EXTERIOR_WELL_WORN        = 'Well-Worn';
    const EXTERIOR_FACTORY_NEW      = 'Factory New';

    const CATEGORY_NORMAL           = '';
    const CATEGORY_STATTRACK        = 'StatTrak™ ';
    const CATEGORY_SOUVENIR         = 'Souvenir ';
    const CATEGORY_STAR             = '★ ';
    const CATEGORY_STAR_STATTRACK   = '★ StatTrak™ ';


    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="GS\SteamWeaponsBundle\Entity\Collection")
     * @ORM\JoinColumn(name="collection_id", referencedColumnName="id")
     **/
    private $collection;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;


    public function getExteriorList() {
        return array(
            self::EXTERIOR_FIELD_TESTED,
            self::EXTERIOR_MINIMAL_WEAR,
            self::EXTERIOR_BATTLE_SCARRED,
            self::EXTERIOR_WELL_WORN,
            self::EXTERIOR_FACTORY_NEW,
        );
    }

    public function getCategory(){
        return array(
            self::CATEGORY_NORMAL,
            self::CATEGORY_STATTRACK,
            self::CATEGORY_SOUVENIR,
            self::CATEGORY_STAR,
            self::CATEGORY_STAR_STATTRACK,
        );
    }

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
     * Set collection
     *
     * @param string $collection
     * @return Weapon
     */
    public function setCollection($collection)
    {
        $this->collection = $collection;

        return $this;
    }

    /**
     * Get collection
     *
     * @return string 
     */
    public function getCollection()
    {
        return $this->collection;
    }


    /**
     * Set name
     *
     * @param string $name
     * @return Weapon
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

}
