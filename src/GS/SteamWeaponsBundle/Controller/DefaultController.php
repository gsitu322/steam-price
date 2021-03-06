<?php

namespace GS\SteamWeaponsBundle\Controller;

use GS\SteamWeaponsBundle\Entity\Price;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use GS\SteamWeaponsBundle\Entity\Weapon;
use GS\SteamWeaponsBundle\Entity\Collection;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Validator\Constraints\DateTime;


class DefaultController extends Controller
{



    private $cookie_string = "steamLogin=76561198122003514%7C%7CEF5C25694485FD406021188629D12A96B7B75676";

    /**
     * @Route("/")
     */
    public function listCollectionsAction($collectionName = null)
    {

        $em = $this->getDoctrine();
        $collection = $em->getRepository('GSSteamWeaponsBundle:Collection')->findAll();

        echo "<br/>";
        foreach($collection as $c){
            echo "<a href='".$this->generateUrl('get_weapons_collection', array('collectionName' => $c->getName() ))."'>".$c->getName()."</a><br />";
        }

        die();
    }

    /**
     * @Route("/get/weapons/{collectionName}", name="get_weapons_collection")
     */
    public function getWeaponCollectionAction($collectionName)
    {

        $em = $this->getDoctrine();

        if($collectionName == null){
            $collection = $em->getRepository('GSSteamWeaponsBundle:Collection')->findAll();
            $collection = $collection[0];
        }else{
            $collection = $em->getRepository('GSSteamWeaponsBundle:Collection')->findOneBy(array('name' => $collectionName));
        }

        $weapons = $this->getDoctrine()
            ->getRepository('GSSteamWeaponsBundle:Weapon')
            ->findBy(array('collection' => $collection));

        foreach($weapons as $w){
            echo "<a href='".$this->generateUrl('get_weapon_price_history', array('weapon' => $w->getName() ))."'>".$w->getName()."</a><br />";
        }

        die();
    }


    /**
     * @Route("/test", name="test")
     */
    public function getTestAction()
    {
        set_time_limit(0);
        echo "start";
        sleep(70);
        echo "stop";

        die('end');
    }


    /**
     * @Route("/get/pricehistory/{weapon}", name="get_weapon_price_history")
     */
    public function getWeaponPriceHistoryAction($weapon)
    {
        set_time_limit(0);

        $em = $this->getDoctrine();
        /** @var \GS\SteamWeaponsBundle\Entity\Weapon $weaponObj */
        $weaponObj = $em->getRepository('GSSteamWeaponsBundle:Weapon')->findOneBy(array('name' => $weapon));



        if(!is_null($weaponObj)){
            $wp = new Weapon();
            $exteriors = $wp->getExteriorList();
            $categories = $wp->getCategory();


            foreach($categories as $category ){

                // Conditions for different weapons
                if(($category == Weapon::CATEGORY_STAR_STATTRACK || $category == Weapon::CATEGORY_STAR) &&  $weaponObj->getCollection()->getName() != 'Knife'){
                    break;
                }

                foreach($exteriors as $exterior){

                    $queryData = array(
                        'appid' => 730,
                        'market_hash_name' => $category . $weaponObj->getName() . " ($exterior)",

                    );

//                    ladybug_dump($queryData['market_hash_name']);

                    $queryString = http_build_query($queryData);
                    $url = "http://steamcommunity.com/market/pricehistory/?" . $queryString;

                    $curl = curl_init();
                    curl_setopt($curl, CURLOPT_URL, $url);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
                    curl_setopt($curl, CURLOPT_COOKIE, $this->cookie_string);
                    $data = curl_exec($curl);
                    curl_close($curl);


                    $data = json_decode($data);

                    if(is_object($data)){
                        ladybug_dump($queryData['market_hash_name']);
                        ladybug_dump($data);
                        self::processData($data->prices, $weaponObj, $exterior, $category);
                    }
                }
            }




        }else{
            die('weapon not found');
        }

        $em = $this->getDoctrine()->getEntityManager();
        $em->flush();


        die();
    }

    private function processData($data, $weaponObj, $exterior, $category){

        $em = $this->getDoctrine()->getEntityManager();
        /** @var \GS\SteamWeaponsBundle\Entity\Price $priceObj */
        $priceObj = $em->getRepository('GSSteamWeaponsBundle:Price')
            ->findOneBy(
                array('weapon' => $weaponObj, 'exterior' => $exterior, 'category' => $category),
                array('date' => 'DESC'),
                1
            );

        while(count($data) > 0){
            $da = array_pop($data);
            $dateObject = \DateTime::createFromFormat("M d Y H: O", $da[0]);


            if(!is_null($priceObj) && $priceObj->getDate() >= $dateObject){
                break;
            }else{

                /** @var \GS\SteamWeaponsBundle\Entity\Price $price */
                $price = new Price();
                $price->setDate($dateObject);
                $price->setPrice($da[1]);
                $price->setVolume($da[2]);
                $price->setExterior($exterior);
                $price->setCategory($category);
                $price->setWeapon($weaponObj);

                $em->persist($price);
            }
        }
    }


























//    /**
//     * @Route("/update/weapons")
//     */
//    public function getUpdateWeaponsAction($collectionName = null)
//    {
//        set_time_limit(0);
//
//        $data = array(
//            "Assault"   => array(
//                "SG 553 | Tornado",
//                "UMP-45 | Caramel",
//                "Five-SeveN | Candy Apple",
//                "AUG | Hot Rod",
//                "Negev | Anodized Navy",
//                "MP9 | Bulldozer",
//                "Glock-18 | Fade"
//            ),
//
//            "Aztec"     => array(
//                "Nova | Forest Leaves",
//                "Five-SeveN | Jungle",
//                "SSG 08 | Lichen Dashed",
//                "AK-47 | Jungle Spray",
//                "M4A4 | Jungle Tiger",
//                "Tec-9 | Ossified"
//            ),
//
//            "Baggage"   => array(
//                "G3SG1 | Contractor",
//                "MP7 | Olive Plaid",
//                "CZ75-Auto | Green Plaid",
//                "MP9 | Green Plaid",
//                "SSG 08 | Sand Dune",
//                "SG 553 | Traveler",
//                "P90 | Leather",
//                "MAC-10 | Commuter",
//                "P2000 | Coach Class",
//                "Sawed-Off | First Class",
//                "USP-S | Business Class",
//                "XM1014 | Red Leather",
//                "AK-47 | First Class",
//                "Desert Eagle | Pilot",
//                "AK-47 | Jet Set"
//            ),
//
//            "Bank"   => array(
//                "Tec-9 | Urban DDPAT",
//                "SG 553 | Army Sheen",
//                "Sawed-Off | Forest DDPAT",
//                "Negev | Army Sheen",
//                "MP7 | Forest DDPAT",
//                "UMP-45 | Carbon Fiber",
//                "Nova | Caged Steel",
//                "MAC-10 | Silver",
//                "Glock-18 | Death Rattle",
//                "G3SG1 | Green Apple",
//                "Galil AR | Tuxedo",
//                "Desert Eagle | Meteorite",
//                "CZ75-Auto | Tuxedo",
//                "AK-47 | Emerald Pinstripe",
//                "P250 | Franklin"
//            ),
//
//            "Cache"  => array(
//                "Negev | Nuclear Waste",
//                "P250 | Contamination",
//                "AUG | Radiation Hazard",
//                "PP-Bizon | Chemical Green",
//                "SG 553 | Fallout Warning",
//                "Five-SeveN | Hot Shot",
//                "Glock-18 | Reactor",
//                "MP9 | Setting Sun",
//                "XM1014 | Bone Machine",
//                "MAC-10 | Nuclear Garden",
//                "Tec-9 | Toxic",
//                "FAMAS | Styx",
//                "Galil AR | Cerberus"
//            ),
//
//            "Cobblestone"  => array(
//                "P90 | Storm",
//                "UMP-45 | Indigo",
//                "MAC-10 | Indigo",
//                "SCAR-20 | Storm",
//                "Dual Berettas | Briar",
//                "USP-S | Royal Blue",
//                "Nova | Green Apple",
//                "MAG-7 | Silver",
//                "Sawed-Off | Rust Coat",
//                "P2000 | Chainmail",
//                "MP9 | Dark Age",
//                "Desert Eagle | Hand Cannon",
//                "CZ75-Auto | Chalice",
//                "M4A1-S | Knight",
//                "AWP | Dragon Lore"
//            ),
//
//            "Dust"  => array(
//                "M4A4 | Desert Storm",
//                "SCAR-20 | Palm",
//                "AK-47 | Predator",
//                "AWP | Snake Camo",
//                "AUG | Copperhead",
//                "Sawed-Off | Copper",
//                "Desert Eagle | Blaze",
//                "P2000 | Scorpion",
//                "Glock-18 | Brass"
//            ),
//
//            "Dust 2"  => array(
//                "Nova | Predator",
//                "MP9 | Sand Dashed",
//                "P90 | Sand Spray",
//                "SCAR-20 | Sand Mesh",
//                "P250 | Sand Dune",
//                "G3SG1 | Desert Storm",
//                "Tec-9 | VariCamo",
//                "MAC-10 | Palm",
//                "Five-SeveN | Orange Peel",
//                "AK-47 | Safari Mesh",
//                "Sawed-Off | Snake Camo",
//                "M4A1-S | VariCamo",
//                "PP-Bizon | Brass",
//                "SG 553 | Damascus Steel",
//                "P2000 | Amber Fade"
//            ),
//
//            "Inferno"  => array(
//                "MAG-7 | Sand Dune",
//                "Nova | Walnut",
//                "P250 | Gunsmoke",
//                "M4A4 | Tornado",
//                "Dual Berettas | Anodized Navy",
//                "Tec-9 | Brass"
//            ),
//
//            "Italy"  => array(
//                "PP-Bizon | Sand Dashed",
//                "Nova | Sand Dune",
//                "FAMAS | Colony",
//                "AUG | Contractor",
//                "Tec-9 | Groundwater",
//                "Nova | Candy Apple",
//                "Dual Berettas | Stained",
//                "P2000 | Granite Marbleized",
//                "UMP-45 | Gunsmoke",
//                "M4A1-S | Boreal Forest",
//                "XM1014 | CaliCamo",
//                "MP7 | Anodized Navy",
//                "Glock-18 | Candy Apple",
//                "Sawed-Off | Full Stop",
//                "AWP | Pit Viper"
//            ),
//
//            "Lake"  => array(
//                "G3SG1 | Jungle Dashed",
//                "SG 553 | Waves Perforated",
//                "Galil AR | Sage Spray",
//                "AUG | Storm",
//                "XM1014 | Blue Spruce",
//                "P250 | Boreal Forest",
//                "XM1014 | Blue Steel",
//                "FAMAS | Cyanospatter",
//                "PP-Bizon | Night Ops",
//                "AWP | Safari Mesh",
//                "Desert Eagle | Mudder",
//                "SG 553 | Anodized Navy",
//                "P90 | Teardown",
//                "USP-S | Night Ops",
//                "Dual Berettas | Cobalt Quartz"
//            ),
//
//            "Militia"  => array(
//                "XM1014 | Grassland",
//                "MAC-10 | Tornado",
//                "PP-Bizon | Forest Leaves",
//                "P2000 | Grassland Leaves",
//                "Nova | Modern Hunter",
//                "Nova | Blaze Orange",
//                "P250 | Modern Hunter",
//                "XM1014 | Blaze Orange",
//                "PP-Bizon | Modern Hunter",
//                "M4A4 | Modern Hunter",
//                "SCAR-20 | Splash Jam"
//            ),
//
//            "Mirage"  => array(
//                "Galil AR | Hunting Blind",
//                "P90 | Scorched",
//                "G3SG1 | Safari Mesh",
//                "AUG | Colony",
//                "Five-SeveN | Contractor",
//                "P250 | Bone Mask",
//                "MP7 | Orange Peel",
//                "Glock-18 | Groundwater",
//                "SG 553 | Gator Mesh",
//                "SSG 08 | Tropical Storm",
//                "Negev | CaliCamo",
//                "MP9 | Hot Rod",
//                "UMP-45 | Blaze",
//                "MAC-10 | Amber Fade",
//                "MAG-7 | Bulldozer"
//            ),
//
//            "Nuke"  => array(
//                "MAG-7 | Irradiated Alert",
//                "Sawed-Off | Irradiated Alert",
//                "PP-Bizon | Irradiated Alert",
//                "P90 | Fallout Warning",
//                "UMP-45 | Fallout Warning",
//                "XM1014 | Fallout Warning",
//                "M4A4 | Radiation Hazard",
//                "P250 | Nuclear Threat",
//                "Tec-9 | Nuclear Threat"
//            ),
//
//            "Office"  => array(
//                "FAMAS | Contrast Spray",
//                "Galil AR | Winter Forest",
//                "G3SG1 | Arctic Camo",
//                "M249 | Blizzard Marbleized",
//                "P2000 | Silver",
//                "MP7 | Whiteout"
//            ),
//
//            "Overpass"  => array(
//                "Sawed-Off | Sage Spray",
//                "UMP-45 | Scorched",
//                "M249 | Contrast Spray",
//                "MAG-7 | Storm",
//                "MP9 | Storm",
//                "Desert Eagle | Urban DDPAT",
//                "MP7 | Gunsmoke",
//                "Glock-18 | Night",
//                "P2000 | Grassland",
//                "CZ75-Auto | Nitro",
//                "SSG 08 | Detour",
//                "XM1014 | VariCamo Blue",
//                "AWP | Pink DDPAT",
//                "USP-S | Road Rash",
//                "M4A1-S | Master Piece",
//            ),
//
//            "Safehouse"  => array(
//                "Dual Berettas | Contractor",
//                "MP7 | Army Recon",
//                "Tec-9 | Army Mesh",
//                "SSG 08 | Blue Spruce",
//                "SCAR-20 | Contractor",
//                "MP9 | Orange Peel",
//                "AUG | Condemned",
//                "USP-S | Forest Leaves",
//                "Galil AR | VariCamo",
//                "M249 | Gator Mesh",
//                "G3SG1 | VariCamo",
//                "FAMAS | Teardown",
//                "Five-SeveN | Silver Quartz",
//                "SSG 08 | Acid Fade",
//                "M4A1-S | Nitro"
//            ),
//
//            "Train"  => array(
//                "PP-Bizon | Urban Dashed",
//                "Nova | Polar Mesh",
//                "Five-SeveN | Forest Night",
//                "G3SG1 | Polar Camo",
//                "Dual Berettas | Colony",
//                "UMP-45 | Urban DDPAT",
//                "M4A4 | Urban DDPAT",
//                "MAC-10 | Candy Apple",
//                "P90 | Ash Wood",
//                "SCAR-20 | Carbon Fiber",
//                "MAG-7 | Metallic DDPAT",
//                "P250 | Metallic DDPAT",
//                "Sawed-Off | Amber Fade",
//                "Desert Eagle | Urban Rubble",
//                "Tec-9 | Red Quartz"
//            ),
//
//            "Vertigo"  => array(
//                "XM1014 | Urban Perforated",
//                "MAC-10 | Urban DDPAT",
//                "PP-Bizon | Carbon Fiber",
//                "P90 | Glacier Mesh",
//                "AK-47 | Black Laminate",
//                "Dual Berettas | Demolition"
//            ),
//
//            "Chop Shop"  => array(
//                "SCAR-20 | Army Sheen",
//                "CZ75-Auto | Army Sheen",
//                "M249 | Impact Drill",
//                "MAG-7 | Seabird",
//                "Desert Eagle | Night",
//                "Galil AR | Urban Rubble",
//                "USP-S | Para Green",
//                "MAC-10 | Fade",
//                "P250 | Whiteout",
//                "MP7 | Full Stop",
//                "Five-SeveN | Nitro",
//                "CZ75-Auto | Emerald",
//                "SG 553 | Bulldozer",
//                "Dual Berettas | Duelist",
//                "Glock-18 | Twilight Galaxy",
//                "M4A1-S | Hot Rod"
//            ),
//
//            "Gods and Monsters"  => array(
//                "MP7 | Asterion",
//                "AUG | Daedalus",
//                "Dual Berettas | Moon in Libra",
//                "Nova | Moon in Libra",
//                "Tec-9 | Hades",
//                "P2000 | Pathfinder",
//                "AWP | Sun in Leo",
//                "M249 | Shipping Forecast",
//                "UMP-45 | Minotaur's Labyrinth",
//                "MP9 | Pandora's Box",
//                "G3SG1 | Chronos",
//                "M4A1-S | Icarus Fell",
//                "M4A4 | Poseidon",
//                "AWP | Medusa"
//            ),
//
//            "Rising Sun"  => array(
//                "PP-Bizon | Bamboo Print",
//                "Sawed-Off | Bamboo Shadow",
//                "Tec-9 | Bamboo Forest",
//                "G3SG1 | Orange Kimono",
//                "P250 | Mint Kimono",
//                "P250 | Crimson Kimono",
//                "Desert Eagle | Midnight Storm",
//                "Galil AR | Aqua Terrace",
//                "MAG-7 | Counter Terrace",
//                "Tec-9 | Terrace",
//                "Five-SeveN | Neon Kimono",
//                "Desert Eagle | Sunset Storm ?",
//                "Desert Eagle | Sunset Storm ?",
//                "M4A4 | Daybreak",
//                "AK-47 | Hydroponic",
//                "AUG | Akihabara Accept"
//            ),
//
//            "Alpha"  => array(
//                "M249 | Jungle DDPAT",
//                "Tec-9 | Tornado",
//                "MP9 | Dry Season",
//                "Five-SeveN | Anodized Gunmetal",
//                "XM1014 | Jungle",
//                "MP7 | Groundwater",
//                "Glock-18 | Sand Dune",
//                "SSG 08 | Mayan Dreams",
//                "Negev | Palm",
//                "Sawed-Off | Mosaico",
//                "P250 | Facets",
//                "AUG | Anodized Navy",
//                "MAG-7 | Hazard",
//                "PP-Bizon | Rust Coat",
//                "FAMAS | Spitfire",
//                "SCAR-20 | Emerald"
//            ),
//
//            "Arms Deal"  => array(
//                "MP7 | Skulls",
//                "SG 553 | Ultraviolet",
//                "AUG | Wings",
//                "M4A1-S | Dark Water",
//                "USP-S | Dark Water",
//                "Glock-18 | Dragon Tattoo",
//                "Desert Eagle | Hypnotic",
//                "AK-47 | Case Hardened",
//                "AWP | Lightning Strike"
//            ),
//
//            "eSports 2013"  => array(
//                "M4A4 | Faded Zebra",
//                "FAMAS | Doomkitty",
//                "MAG-7 | Memento",
//                "Sawed-Off | Orange DDPAT",
//                "P250 | Splash",
//                "Galil AR | Orange DDPAT",
//                "AK-47 | Red Laminate",
//                "AWP | BOOM",
//                "P90 | Death by Kitty"
//            ),
//
//            "Bravo"  => array(
//                "Nova | Tempest",
//                "Dual Berettas | Black Limba",
//                "UMP-45 | Bone Pile",
//                "SG 553 | Wave Spray",
//                "Galil AR | Shattered",
//                "G3SG1 | Demeter",
//                "M4A1-S | Bright Water",
//                "M4A4 | Zirka",
//                "MAC-10 | Graven",
//                "USP-S | Overgrowth",
//                "P90 | Emerald Dragon",
//                "P2000 | Ocean Foam",
//                "AWP | Graphite",
//                "Desert Eagle | Golden Koi",
//                "AK-47 | Fire Serpent"
//            ),
//
//            "Arms Deal Two"  => array(
//                "Tec-9 | Blue Titanium",
//                "M4A1-S | Blood Tiger",
//                "FAMAS | Hexane",
//                "P250 | Hive",
//                "SCAR-20 | Crimson Web",
//                "Five-SeveN | Case Hardened",
//                "MP9 | Hypnotic",
//                "Nova | Graphite",
//                "Dual Berettas | Hemoglobin",
//                "P90 | Cold Blooded",
//                "USP-S | Serum",
//                "SSG 08 | Blood in the Water"
//            ),
//
//            "Winter Offensive"  => array(
//                "PP-Bizon | Cobalt Halftone",
//                "M249 | Magma",
//                "Five-SeveN | Kami",
//                "Galil AR | Sandstorm",
//                "Nova | Rising Skull",
//                "MP9 | Rose Iron",
//                "Dual Berettas | Marina",
//                "FAMAS | Pulse",
//                "AWP | Redline",
//                "P250 | Mehndi",
//                "M4A1-S | Guardian",
//                "Sawed-Off | The Kraken",
//                "M4A4 | Asiimov",
//            ),
//
//            "eSports 2013 Winter"  => array(
//                "Galil AR | Blue Titanium",
//                "Five-SeveN | Nightshade",
//                "PP-Bizon | Water Sigil",
//                "Nova | Ghost Camo",
//                "G3SG1 | Azure Zebra",
//                "P250 | Steel Disruption",
//                "AK-47 | Blue Laminate",
//                "P90 | Blind Spot",
//                "FAMAS | Afterimage",
//                "AWP | Electric Hive",
//                "Desert Eagle | Cobalt Disruption",
//                "M4A4 | X-Ray"
//            ),
//
//            "Arms Deal 3"  => array(
//                "CZ75-Auto | Crimson Web",
//                "P2000 | Red FragCam",
//                "Dual Berettas | Panther",
//                "USP-S | Stainless",
//                "Glock-18 | Blue Fissure",
//                "CZ75-Auto | Tread Plate",
//                "Tec-9 | Titanium Bit",
//                "Desert Eagle | Heirloom",
//                "Five-SeveN | Copper Galaxy",
//                "CZ75-Auto | The Fuschia Is Now",
//                "P250 | Undertow",
//                "CZ75-Auto | Victoria"
//            ),
//
//            "Phoenix"  => array(
//                "UMP-45 | Corporal",
//                "Negev | Terrain",
//                "Tec-9 | Sandstorm",
//                "MAG-7 | Heaven Guard",
//                "MAC-10 | Heat",
//                "SG 553 | Pulse",
//                "FAMAS | Sergeant",
//                "USP-S | Guardian",
//                "AK-47 | Redline",
//                "P90 | Trigon",
//                "Nova | Antique",
//                "AWP | Asiimov",
//                "AUG | Chameleon"
//            ),
//
//            "Huntsman"  => array(
//                "Tec-9 | Isaac",
//                "SSG 08 | Slashed",
//                "Dual Berettas | Retribution",
//                "Galil AR | Kami",
//                "P90 | Desert Warfare",
//                "CZ75-Auto | Poison Dart",
//                "CZ75-Auto | Twist",
//                "P90 | Module",
//                "P2000 | Pulse",
//                "AUG | Torque",
//                "PP-Bizon | Antique",
//                "XM1014 | Heaven Guard",
//                "MAC-10 | Curse",
//                "MAC-10 | Tatter",
//                "M4A1-S | Atomic Alloy",
//                "SCAR-20 | Cyrex",
//                "USP-S | Orion",
//                "USP-S | Caiman",
//                "AK-47 | Vulcan",
//                "M4A4 | Desert-Strike",
//                "M4A4 | Howl"
//            ),
//
//            "Breakout"  => array(
//                "MP7 | Urban Hazard",
//                "Negev | Desert-Strike",
//                "P2000 | Ivory",
//                "SSG 08 | Abyss",
//                "UMP-45 | Labyrinth",
//                "PP-Bizon | Osiris",
//                "CZ75-Auto | Tigris",
//                "Nova | Koi",
//                "P250 | Supernova",
//                "Desert Eagle | Conspiracy",
//                "Five-SeveN | Fowl Play",
//                "Glock-18 | Water Elemental",
//                "P90 | Asiimov",
//                "M4A1-S | Cyrex"
//            ),
//
//            "eSports 2014 Summer" => array(
//                "SSG 08 | Dark Water",
//                "MAC-10 | Ultraviolet",
//                "USP-S | Blood Tiger",
//                "CZ75-Auto | Hexane",
//                "Negev | Bratatat",
//                "XM1014 | Red Python",
//                "PP-Bizon | Blue Streak",
//                "P90 | Virus",
//                "MP7 | Ocean Foam",
//                "Glock-18 | Steel Disruption",
//                "Desert Eagle | Crimson Web",
//                "AUG | Bengal Tiger",
//                "Nova | Bloomstick",
//                "AWP | Corticera",
//                "P2000 | Corticera",
//                "M4A4 | Bullet Rain",
//                "AK-47 | Jaguar"
//            ),
//
//            "Vanguard" => array(
//                "G3SG1 | Murky",
//                "MAG-7 | Firestarter",
//                "MP9 | Dart",
//                "Five-SeveN | Urban Hazard",
//                "UMP-45 | Delusion",
//                "Glock-18 | Grinder",
//                "M4A1-S | Basilisk",
//                "M4A4 | Griffin",
//                "Sawed-Off | Highwayman",
//                "P250 | Cartel",
//                "SCAR-20 | Cardiac",
//                "XM1014 | Tranquility",
//                "AK-47 | Wasteland Rebel",
//                "P2000 | Fire Elemental"
//            ),
//
//            "Chroma" => array(
//                "Glock-18 | Catacombs",
//                "M249 | System Lock",
//                "MP9 | Deadly Poison",
//                "SCAR-20 | Grotto",
//                "XM1014 | Quicksilver",
//                "Dual Berettas | Urban Shock",
//                "Desert Eagle | Naga",
//                "MAC-10 | Malachite",
//                "Sawed-Off | Serenity",
//                "AK-47 | Cartel",
//                "M4A4 | ?? (Dragon King)",
//                "P250 | Muertos",
//                "AWP | Man-o'-war",
//                "Galil AR | Chatterbox"
//            ),
//
//            "Chroma 2" => array(
//                "AK-47 | Elite Build",
//                "MP7 | Armor Core",
//                "Desert Eagle | Bronze Deco",
//                "P250 | Valence",
//                "Negev | Man-o'-war",
//                "Sawed-Off | Origami",
//                "AWP | Worm God",
//                "MAG-7 | Heat",
//                "CZ75-Auto | Pole Position",
//                "UMP-45 | Grand Prix",
//                "Five-SeveN | Monkey Business",
//                "Galil AR | Eco",
//                "FAMAS | Djinn",
//                "M4A1-S | Hyper Beast",
//                "MAC-10 | Neon Rider"
//            ),
//
//            "Falchion Case" => array(
//                "Galil AR | Rocket Pop",
//                "Glock-18 | Bunsen Burner",
//                "Nova | Ranger",
//                "P90 | Elite Build",
//                "UMP-45 | Riot",
//                "USP-S | Torque",
//                "FAMAS | Neural Net",
//                "M4A4 | Evil Daimyo",
//                "MP9 | Ruby Poison Dart",
//                "Negev | Loudmouth",
//                "P2000 | Handgun",
//                "CZ75-Auto | Yellow Jacket",
//                "MP7 | Nemesis",
//                "SG 553 | Cyrex",
//                "AK-47 | Aquamarine Revenge",
//                "AWP | Hyper Beast"
//            ),
//
//            "Knife" => array(
//                "Bayonet",
//                "Bayonet | Blue Steel",
//                "Bayonet | Boreal Forest",
//                "Bayonet | Case Hardened",
//                "Bayonet | Crimson Web",
//                "Bayonet | Damascus Steel",
//                "Bayonet | Doppler",
//                "Bayonet | Fade",
//                "Bayonet | Forest DDPAT",
//                "Bayonet | Marble Fade",
//                "Bayonet | Night",
//                "Bayonet | Rust Coat",
//                "Bayonet | Safari Mesh",
//                "Bayonet | Scorched",
//                "Bayonet | Slaughter",
//                "Bayonet | Stained",
//                "Bayonet | Tiger Tooth",
//                "Bayonet | Ultraviolet",
//                "Bayonet | Urban Masked",
//                "Butterfly Knife",
//                "Butterfly Knife | Blue Steel",
//                "Butterfly Knife | Boreal Forest",
//                "Butterfly Knife | Case Hardened",
//                "Butterfly Knife | Crimson Web",
//                "Butterfly Knife | Fade",
//                "Butterfly Knife | Forest DDPAT",
//                "Butterfly Knife | Night",
//                "Butterfly Knife | Safari Mesh",
//                "Butterfly Knife | Scorched",
//                "Butterfly Knife | Slaughter",
//                "Butterfly Knife | Stained",
//                "Butterfly Knife | Urban Masked",
//                "Falchion Knife",
//                "Falchion Knife | Blue Steel",
//                "Falchion Knife | Boreal Forest",
//                "Falchion Knife | Case Hardened",
//                "Falchion Knife | Crimson Web",
//                "Falchion Knife | Fade",
//                "Falchion Knife | Forest DDPAT",
//                "Falchion Knife | Night",
//                "Falchion Knife | Safari Mesh",
//                "Falchion Knife | Scorched",
//                "Falchion Knife | Slaughter",
//                "Falchion Knife | Stained",
//                "Falchion Knife | Urban Masked",
//                "Flip Knife",
//                "Flip Knife | Blue Steel",
//                "Flip Knife | Boreal Forest",
//                "Flip Knife | Case Hardened",
//                "Flip Knife | Crimson Web",
//                "Flip Knife | Damascus Steel",
//                "Flip Knife | Doppler",
//                "Flip Knife | Fade",
//                "Flip Knife | Forest DDPAT",
//                "Flip Knife | Marble Fade",
//                "Flip Knife | Night",
//                "Flip Knife | Rust Coat",
//                "Flip Knife | Safari Mesh",
//                "Flip Knife | Scorched",
//                "Flip Knife | Slaughter",
//                "Flip Knife | Stained",
//                "Flip Knife | Tiger Tooth",
//                "Flip Knife | Ultraviolet",
//                "Flip Knife | Urban Masked",
//                "Gut Knife",
//                "Gut Knife | Blue Steel",
//                "Gut Knife | Boreal Forest",
//                "Gut Knife | Case Hardened",
//                "Gut Knife | Crimson Web",
//                "Gut Knife | Damascus Steel",
//                "Gut Knife | Doppler",
//                "Gut Knife | Fade",
//                "Gut Knife | Forest DDPAT",
//                "Gut Knife | Marble Fade",
//                "Gut Knife | Night",
//                "Gut Knife | Rust Coat",
//                "Gut Knife | Safari Mesh",
//                "Gut Knife | Scorched",
//                "Gut Knife | Slaughter",
//                "Gut Knife | Stained",
//                "Gut Knife | Tiger Tooth",
//                "Gut Knife | Ultraviolet",
//                "Gut Knife | Urban Masked",
//                "Huntsman Knife",
//                "Huntsman Knife | Blue Steel",
//                "Huntsman Knife | Boreal Forest",
//                "Huntsman Knife | Case Hardened",
//                "Huntsman Knife | Crimson Web",
//                "Huntsman Knife | Fade",
//                "Huntsman Knife | Forest DDPAT",
//                "Huntsman Knife | Night",
//                "Huntsman Knife | Safari Mesh",
//                "Huntsman Knife | Scorched",
//                "Huntsman Knife | Slaughter",
//                "Huntsman Knife | Stained",
//                "Huntsman Knife | Urban Masked",
//                "Karambit",
//                "Karambit | Blue Steel",
//                "Karambit | Boreal Forest",
//                "Karambit | Case Hardened",
//                "Karambit | Crimson Web",
//                "Karambit | Damascus Steel",
//                "Karambit | Doppler",
//                "Karambit | Fade",
//                "Karambit | Forest DDPAT",
//                "Karambit | Marble Fade",
//                "Karambit | Night",
//                "Karambit | Rust Coat",
//                "Karambit | Safari Mesh",
//                "Karambit | Scorched",
//                "Karambit | Slaughter",
//                "Karambit | Stained",
//                "Karambit | Tiger Tooth",
//                "Karambit | Ultraviolet",
//                "Karambit | Urban Masked"
//            )
//
//        );
//
//        $em = $this->getDoctrine()->getEntityManager();
//
//        foreach($data as $collectionName => $collectionWeapons){
//
//            $collection = $this->getDoctrine()
//                ->getRepository('GSSteamWeaponsBundle:Collection')
//                ->findOneBy(array('name' => $collectionName));
//
//            /** Add new collection to table */
//            if(count($collection) > 0){
//                echo $collection->getName() . "<br/>";
//            }else{
//                $collection = new Collection();
//                $collection->setName($collectionName);
//                $em->persist($collection);
//                $em->flush();
//            }
//
//            /** Add weapons to the weapons table */
//            foreach($collectionWeapons as $weapon){
//                $w = new Weapon();
//                $w->setName($weapon);
//                $w->setCollection($collection);
//                $em->persist($w);
//            }
//            $em->flush();
//
//        }
//
//        die('done');
//    }
}
