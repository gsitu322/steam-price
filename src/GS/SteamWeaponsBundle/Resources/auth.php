<?php
//
//private $settings = array(
//    "apikey" => "FBEF75D8158BC4E4DB22A53CBD1CD047",
//    "domainname" => "localhost",
//    "buttonstyle" => "small",
//    "logoutpage" => "/steam/login",
//    "loginpage" => "/steam/logout",
//);
//
//private function loadLibs(){
//    require_once($this->container->getParameter( 'kernel.root_dir' ) . '/../vendor/lightopenid/lib/lightopenid.php');
//}
//
//
///**
// * @Route("/steam/login/", name="steam_login")
// */
//public function steamloginAction()
//{
//
//
//    try {
//        $this->loadLibs();
//
//        $openid = new \LightOpenID($this->generateUrl('steam_login', array(), true));
//
//        $button['small'] = "small";
//        $button['large_no'] = "large_noborder";
//        $button['large'] = "large_border";
//        $button = $button[$this->settings['buttonstyle']];
//
//        if(!$openid->mode) {
//            if(isset($_GET['login'])) {
//                $openid->identity = 'http://steamcommunity.com/openid';
//                header('Location: ' . $openid->authUrl());
//            }
//
//            echo  "<form action=\"?login\" method=\"post\"> <input type=\"image\" src=\"http://cdn.steamcommunity.com/public/images/signinthroughsteam/sits_".$button.".png\"></form>";
//            die();
//        }
//
//        elseif($openid->mode == 'cancel') {
//            echo 'User has canceled authentication!';
//        } else {
//            if($openid->validate()) {
//                $id = $openid->identity;
//                $ptn = "/^http:\/\/steamcommunity\.com\/openid\/id\/(7[0-9]{15,25}+)$/";
//                preg_match($ptn, $id, $matches);
//
//                $_SESSION['steamid'] = $matches[1];
//
//                return $this->redirect($this->generateUrl('steam_user'));
//
//            } else {
//                echo "User is not logged in.\n";
//                return $this->redirect($this->generateUrl('steam_login'));
//            }
//
//        }
//    } catch(ErrorException $e) {
//        echo $e->getMessage();
//    }
//
//    die();
//}
//
///**
// * @Route("/steam/user/", name="steam_user")
// */
//public function steamUserAction()
//{
//    if (empty($_SESSION['steam_uptodate']) or $_SESSION['steam_uptodate'] == false or empty($_SESSION['steam_personaname'])) {
//        @ $url = file_get_contents("http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=". $this->settings['apikey'] ."&steamids=".$_SESSION['steamid']);
//
//        $content = json_decode($url, true);
//        $_SESSION['steam_steamid'] = $content['response']['players'][0]['steamid'];
//        $_SESSION['steam_communityvisibilitystate'] = $content['response']['players'][0]['communityvisibilitystate'];
//        $_SESSION['steam_profilestate'] = $content['response']['players'][0]['profilestate'];
//        $_SESSION['steam_personaname'] = $content['response']['players'][0]['personaname'];
//        $_SESSION['steam_lastlogoff'] = $content['response']['players'][0]['lastlogoff'];
//        $_SESSION['steam_profileurl'] = $content['response']['players'][0]['profileurl'];
//        $_SESSION['steam_avatar'] = $content['response']['players'][0]['avatar'];
//        $_SESSION['steam_avatarmedium'] = $content['response']['players'][0]['avatarmedium'];
//        $_SESSION['steam_avatarfull'] = $content['response']['players'][0]['avatarfull'];
//        $_SESSION['steam_personastate'] = $content['response']['players'][0]['personastate'];
//        if (isset($content['response']['players'][0]['realname'])) {
//            $_SESSION['steam_realname'] = $content['response']['players'][0]['realname'];
//        } else {
//            $_SESSION['steam_realname'] = "Real name not given";
//        }
//        $_SESSION['steam_primaryclanid'] = $content['response']['players'][0]['primaryclanid'];
//        $_SESSION['steam_timecreated'] = $content['response']['players'][0]['timecreated'];
//        $_SESSION['steam_uptodate'] = true;
//    }
//    $steamprofile['steamid'] = $_SESSION['steam_steamid'];
//    $steamprofile['communityvisibilitystate'] = $_SESSION['steam_communityvisibilitystate'];
//    $steamprofile['profilestate'] = $_SESSION['steam_profilestate'];
//    $steamprofile['personaname'] = $_SESSION['steam_personaname'];
//    $steamprofile['lastlogoff'] = $_SESSION['steam_lastlogoff'];
//    $steamprofile['profileurl'] = $_SESSION['steam_profileurl'];
//    $steamprofile['avatar'] = $_SESSION['steam_avatar'];
//    $steamprofile['avatarmedium'] = $_SESSION['steam_avatarmedium'];
//    $steamprofile['avatarfull'] = $_SESSION['steam_avatarfull'];
//    $steamprofile['personastate'] = $_SESSION['steam_personastate'];
//    $steamprofile['realname'] = $_SESSION['steam_realname'];
//    $steamprofile['primaryclanid'] = $_SESSION['steam_primaryclanid'];
//    $steamprofile['timecreated'] = $_SESSION['steam_timecreated'];
//
//    ladybug_dump_die($steamprofile);
//
//
//}
//
///**
// * @Route("/steam/validate/")
// */
//public function steamValidateAction()
//{
//    unset($_SESSION['steamid']);
//    unset($_SESSION['steam_uptodate']);
//
//    return $this->redirect($this->generateUrl('steam_login'));
//}
//
///**
// * @Route("/steam/logout/")
// */
//public function steamlogoutAction()
//{
//    unset($_SESSION['steamid']);
//    unset($_SESSION['steam_uptodate']);
//
//    return $this->redirect($this->generateUrl('steam_login'));
//}
//
