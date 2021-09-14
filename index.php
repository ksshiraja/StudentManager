<?php    
session_start();
date_default_timezone_set("Asia/India");
 
include 'includes/configs.php';
include 'vendor/autoload.php';
include 'includes/functions.php';

$templateRoot = "templates/default";
$templateURL  = "./" . $templateRoot; 
$version      = (($production)? "1.0": time());
$hash         = "?v=". $version;
$loader       = new   \Twig\Loader\FilesystemLoader($templateRoot);
$twig         = new   \Twig\Environment($loader); 
$libs         = new   \BMgr\Libs("assets/lib");
$libs         = $libs->getLibs(); 
$DB           = new DB(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);

$jsonToArray  = new \Twig\TwigFunction('jsonToArray', function($jsonData) {
    return json_decode($jsonData);
});
$getScores  = new \Twig\TwigFunction('getScores', function($id) {
    global $DB;
});
$explode  = new \Twig\TwigFunction('explode', function($str, $patt) {
    return preg_split($patt, $str);
});
$twig->addFunction($getScores);
$twig->addFunction($jsonToArray);
$twig->addFunction($explode);
$load       = array( 
    "currentDate" => date("d/m/Y"),
    "access"    => true,
    "assets"    => $libs,
    "imgs"      => "assets/imgs/",
    "hash"      => $hash,  
    "tpl"       => $templateURL
);
require 'app/routes.php';
 $currentTheme = "Default";
 // POST method is reserved for AJAX actions
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if (isset($_SESSION) && @$_SESSION['loggedin'] == true) {
    $load['content'] = $content;
    $load['S']  = $_SESSION; 
} else {
    switch (@$_GET['p']) {
        case "Register":
            $load['content'] = "Register";
        break;
        default: 
        $load['content'] = "Login";
    }
}
$template   = $twig->load('index.html')->render($load);
echo $template;
}
include 'app/actions.php';
?> 