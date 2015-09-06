<?php

namespace GS\SteamWeaponsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use GS\SteamWeaponsBundle\Entity\Weapon;
use GS\SteamWeaponsBundle\Entity\Collection;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class DefaultController extends Controller
{

    /**
     * @Route("/api/test")
     * @Template("GSSteamWeaponsBundle:Default:index.html.twig")
     */
    public function apiTestAction()
    {
        $testArray = array(
            'text' => 'does this work?',
            'success' => true
        );

        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        $jsonContent = $serializer->serialize($testArray, 'json');

        echo $jsonContent;
        die();
    }

    /**
     * @Route("/get/weapons")
     * @Template("GSSteamWeaponsBundle:Default:index.html.twig")
     */
    public function getWeaponsAction()
    {

        $data = $this->getDoctrine()
            ->getRepository('GSSteamWeaponsBundle:Collection')
            ->findAll();

        ladybug_dump($data);
        return array();

    }

    /**
     * @Route("/get/weapons/{collectionName}")
     */
    public function getWeaponCollectionAction($collectionName)
    {
        $collection = $this->getDoctrine()
            ->getRepository('GSSteamWeaponsBundle:Collection')
            ->findOneBy(array('name' => $collectionName));

        $weapons = $this->getDoctrine()
            ->getRepository('GSSteamWeaponsBundle:Weapon')
            ->findBy(array('collection' => $collection));

        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        $jsonContent = $serializer->serialize($weapons, 'json');

        echo $jsonContent;


//        echo "Collection : ". $collection->getName() ."<br /><br />";
//        foreach($weapons as $w){
//            echo $w->getName() . "<br />";
//        }
//        return array();

    }


    /**
     * @Route("/get/weaponstest")
     */
    public function getWeaponCollectionAction2()
    {



        //extract data from the post
        extract($_POST);
//        $fields_string = "";

        //set POST variables
//        $url = 'https://steamcommunity.com/market/pricehistory/';
//        $fields = array(
//            'appid'=> "730",
//			'market_hash_name'=>"AWP | Asiimov (Field-Tested)"
//        );

        //url-ify the data for the POST
//        foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
//        rtrim($fields_string, '&');
//
//        ladybug_dump($fields_string);
//        die();

        $url = "http://steamcommunity.com/market/pricehistory/?appid=730&market_hash_name=AWP%20%7C%20Asiimov%20(Field-Tested)";
        //open connection
        $ch = curl_init();

        //set the url, number of POST vars, POST data
//        curl_setopt($ch,CURLOPT_URL, $url);
//        curl_setopt($ch,CURLOPT_POST, count($fields));
//        curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);



        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);



        //execute post
        $result = curl_exec($ch);
        ladybug_dump($result);

        //close connection
        curl_close($ch);

        return array();
    }


}
