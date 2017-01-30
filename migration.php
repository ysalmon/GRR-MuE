<?php

include "include/connect.inc.php";
include "include/config.inc.php";
include "include/misc.inc.php";
include "include/functions.inc.php";
include "include/$dbsys.inc.php";

$grr_script_name = "migration.php";

// Settings
require_once("include/settings.class.php");
//Chargement des valeurs de la table settingS
if (!Settings::load())
	die("Erreur chargement settings");

// Session related functions
require_once("include/session.inc.php");
// Paramètres langage
include "include/language.inc.php";

function traite_requete($requete = ""){
	
	mysqli_query($GLOBALS['db_c'], $requete);
	$erreur_no = mysqli_errno($GLOBALS['db_c']);
	if (!$erreur_no)
		$retour = "";
	else{
		
		switch ($erreur_no){
			
			case "1060":
			// le champ existe déjà : pas de problème
			$retour = "";
			break;
			case "1061":
			// La cléf existe déjà : pas de problème
			$retour = "";
			break;
			case "1062":
			// Présence d'un doublon : création de la cléf impossible
			$retour = "<span style=\"color:#FF0000;\">Erreur (<b>non critique</b>) sur la requête : <i>".$requete."</i> (".mysqli_errno($GLOBALS['db_c'])." : ".mysqli_error($GLOBALS['db_c']).")</span><br />\n";
			break;
			case "1068":
			// Des cléfs existent déjà : pas de problème
			$retour = "";
			break;
			case "1091":
			// Déjà supprimé : pas de problème
			$retour = "";
			break;
			default:
			$retour = "<span style=\"color:#FF0000;\">Erreur sur la requête : <i>".$requete."</i> (".mysqli_errno($GLOBALS['db_c'])." : ".mysqli_error($GLOBALS['db_c']).")</span><br />\n";
			break;
		}
	}
	return $retour;
}


$result_inter = '';
$result = "<b>Migration de la version escoGRR vers GRR 3.1</b><br />";

// Ajout nouveaux champs 3.1
$result_inter .= traite_requete("ALTER TABLE grr_entry ADD clef INT(2) NOT NULL DEFAULT '0' AFTER jours;");
$result_inter .= traite_requete("ALTER TABLE grr_entry ADD courrier INT(2) NOT NULL DEFAULT '0' AFTER clef;");
$result_inter .= traite_requete("ALTER TABLE grr_repeat ADD courrier INT(2) NOT NULL DEFAULT '0' AFTER jours;");

// MAJ du numéro de version
$result_inter .= traite_requete("UPDATE grr_setting SET value = '3.1.0' WHERE name = 'version';");

// Nouveaux réglages 3.1
$result_inter .= traite_requete("INSERT INTO grr_setting VALUES('menu_gauche', '1');");
$result_inter .= traite_requete("INSERT INTO grr_setting VALUES('legend', '0');");
$result_inter .= traite_requete("INSERT INTO grr_setting VALUES('file', '1');");
$result_inter .= traite_requete("INSERT INTO grr_setting VALUES('mail_destinataire', 'test@test.fr');");
$result_inter .= traite_requete("INSERT INTO grr_setting VALUES('mail_etat_destinataire', '0');");

// Suppression des préférences "default_style" et "type default_list_type" pour tous les utilisateurs
$result_inter .= traite_requete("UPDATE grr_utilisateurs SET default_style = '';");
$result_inter .= traite_requete("UPDATE grr_utilisateurs SET default_list_type = '';");

// CSS : Affectation du style défault pour la config générale
$result_inter .= traite_requete("UPDATE grr_setting SET value = 'default' WHERE name = 'default_css';");

// type list "select" pour tous les établissements
$result_inter .= traite_requete("UPDATE grr_setting SET value = 'select' WHERE name = 'area_list_format';");
$result_inter .= traite_requete("UPDATE grr_setting_etablissement SET value = 'select' WHERE name = 'area_list_format';");


$result_inter .= traite_requete("UPDATE grr_setting_etablissement SET value = 'tourraine' WHERE name = 'default_css' and code_etab = '0281041E';");

// Suppression de toutes les lignes "default_css" de la table grr_setting_etablissement
$result_inter .= traite_requete("delete from grr_setting_etablissement where name='default_css';");

// Insertation d'une ligne "default_css" pour tous les établissements
$result_inter .= traite_requete("insert into grr_setting_etablissement
									select 'default_css', '', code from grr_etablissement;");


// Affectation du style Agricole pour les établissements concernés
$result_inter .= traite_requete("update grr_setting_etablissement set value = 'agricole'
where name = 'default_css' and code_etab in (
'0370781Y','0180585N','0360017Y','0410018X','0450094H','0451535Z','0370878D','0450027K','0370794M','0410626H','0410629L'
);");

// Affectation du style Net'O Centre pour les établissements concernés
$result_inter .= traite_requete("update grr_setting_etablissement set value = 'netocentre'
where name = 'default_css' and code_etab in (
'0281041E', '0360766M', '0451418X', '0371436K', '0371587Z', '0371588A', '0371513U', '0180847Y', '0180924G', '0410955R', '0411065K', '0411045N', 
'0451463W', '0410860M', '0410593X', '0455007Y', '0455006X', '0280657M', '0370066W', '0280706R', '0360050J', '0280659P', '0451104F', '0180982V', 
'0281098Q', '0450888C', '18450311800020', '0180809Q', '0180912U', '0371197Q', '0451147Q', '0360824A', '0371347Q', '0410820Q', '0281011Q', 
'0411020L', '0180913V', '0453509W', '0452509W', '0371558T', '0371347N', '0410820U', '0180808F', '0281011X', '0360689D', '0281010W', '0281009V', 
'0360737F', '0281010Q', '0451146B', '0180809G', '0451358G', '0371197A', '0281156E', '0370074E', '0180005H', '0370035M', '0360008N', '0280007F', 
'0360002G', '0370038R', '0370016S', '0370036N', '0450029M', '0410959V', '0451526P', '0370037P', '0410017W', '0450062Y', '0450042B', '0280015P', 
'0410002E', '0451484U', '0281047L', '0360024F', '0180007K', '0371418R', '0451462V', '0371417P', '0180024D', '0370001A', '0180006J', '0370039S', 
'0360009P', '0450049J', '0410030K', '0280019U', '0450782F', '0370040T', '0371211R', '0370888P', '0280009H', '0280864M', '0371258S', '0370032J', 
'0451304Y', '0450064A', '0280925D', '0370053G', '0371099U', '0360026H', '0180009M', '0450750W', '0280700J', '0180025E', '0451067R', '0180010N', 
'0371100V', '0360011S', '0450066C', '0451037H', '0370054H', '0280022X', '0450786K', '0410832G', '0281021H', '0410718H', '0180823X', '0370771M', 
'0410031L', '0451442Y', '0360003H', '0410036S', '0180042Y', '0371123V', '0180026F', '0450043C', '0280021W', '0450822X', '0180036S', '0410899E', 
'0280044W', '0180008L', '0280036M', '0281077U', '0410001D', '0450051L', '0360043B', '0180035R', '0370009J', '0360019A', '0450050K', '0451483T', 
'0360005K', '0450040Z', '0180860Q', '0281098S' 
);");

// Affectation du style Touraine e-school pour les établissements concernés
$result_inter .= traite_requete("update grr_setting_etablissement set value = 'tourraine'
where name = 'default_css' and code_etab in (
'0370793L', '0370010K', '0370044X', '0370007G', '0370013N', '0371248F', '0370791J', '0371210P', '0371098T', '0370034L', '0371101W', '0371122U', 
'0370051E', '0370022Y', '0280957N', '0377777U', '0370045Y', '0370026C', '0371189S', '0370768J', '0371204H', '0370994E', '0370792K', '0370023Z', 
'0370766G', '0370886M', '0371397T', '0371159J', '0370006F', '0370764E', '0370887N', '0371126Y', '0371158H', '0370071B', '0370769K', '0370885L', 
'0371191U', '0371124W', '0370765F', '0370011L', '0370763D', '0371378X', '0370015R', '0370041U', '0370884K', '0370767H', '0370024A', '0371304S', 
'0371209N', '0370991B', '0370995F', '0370033K', '0370799T', '0371403Z', '0371192V', '0371391L', '0370993D', '0371316E'
);");

// Etablissements à supprimer 0180777X - 0450790P - 0360658V
$result_inter .= traite_requete("DELETE FROM grr_etablissement WHERE code in ('0180777X', '0450790P', '0360658V');");
$result_inter .= traite_requete("DELETE FROM grr_setting_etablissement WHERE code_etab in ('0180777X', '0450790P', '0360658V');");

/////////////////////////////////////////////
// DEV ATOS - requete pour developpement en local (condition pour eviter l'execution en production)
if(file_exists('../test_atos.php')){
	$result_inter .= traite_requete("UPDATE grr_utilisateurs SET password = 'ab4f63f9ac65152575886860dde480a1' WHERE login = 'ADMINISTRATEUR';");
	$result_inter .= traite_requete("UPDATE grr_setting SET value = '' WHERE name = 'sso_statut';");
	$result_inter .= traite_requete("UPDATE grr_setting SET value = '' WHERE name = 'Url_cacher_page_login';");
}
/////////////////////////////////////////////

if ($result_inter == ''){
	$result .= "<span style=\"color:green;\">Ok !</span><br />";
	$result .= "<a href='index.php'>Se connecter à GRR</a>";
}
else
	$result .= $result_inter;

echo $result;
?>