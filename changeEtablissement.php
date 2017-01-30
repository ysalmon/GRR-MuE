<?php

include_once "/include/connect.inc.php";
include_once "/include/config.inc.php";
include_once "/include/misc.inc.php";
include_once "/include/mrbs_sql.inc.php";
include_once "/include/functions.inc.php";
include_once "/include/$dbsys.inc.php";
include_once "/include/mincals.inc.php";
$grr_script_name = "day.php";
#Paramètres de connection
require_once("/include/settings.class.php");

#Chargement des valeurs de la table settings
$settings = new Settings();
if (!$settings)
	die("Erreur chargement settings");


#Fonction relative à la session
include_once "/include/session.inc.php";

// Resume session
if (!grr_resumeSession()) {
    if ((Settings::get("authentification_obli")==1) or ((Settings::get("authentification_obli")==0) and (isset($_SESSION['login'])))) {
       header("Location: ./logout.php?auto=1&url=$url");
       die();
    }
};

if (isset($_POST['etablissement']) ) {
	$tabSplit = split("=", $_POST['etablissement']);
	$_SESSION['current_etablisement']= $tabSplit[1];

}

if (isset($_GET['code_etablissement']) ) {
	$_SESSION['current_etablisement']=  $_GET['code_etablissement'];
}
header("Location: ".htmlspecialchars_decode(page_accueil())."");


?>