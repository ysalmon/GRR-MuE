<?php
/**
 * view_rights_etablissement.php
 * Liste des privilèges d'un établissement
 * Ce script fait partie de l'application GRR
 * Dernière modification : $Date: 2010-05-07 21:26:44 $
 * @author    Laurent Delineau <laurent.delineau@ac-poitiers.fr>
 * @copyright Copyright 2003-2008 Laurent Delineau
 * @link      http://www.gnu.org/licenses/licenses.html
 * @package   root
 * @version   $Id: view_rights_area.php,v 1.1 2010-05-07 21:26:44 grr Exp $
 * @filesource
 *
 * This file is part of GRR.
 *
 * GRR is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GRR is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GRR; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */
/**
 * $Log: view_rights_area.php,v $
 * Revision 1.1  2010-05-07 21:26:44  grr
 * *** empty log message ***
 *
 * Revision 1.8  2009-12-02 20:11:08  grr
 * *** empty log message ***
 *
 * Revision 1.7  2009-06-04 15:30:17  grr
 * *** empty log message ***
 *
 * Revision 1.6  2009-04-14 12:59:17  grr
 * *** empty log message ***
 *
 * Revision 1.5  2009-02-27 13:28:19  grr
 * *** empty log message ***
 *
 * Revision 1.4  2009-01-20 07:19:17  grr
 * *** empty log message ***
 *
 * Revision 1.3  2008-11-16 22:00:59  grr
 * *** empty log message ***
 *
 * Revision 1.2  2008-11-11 22:01:14  grr
 * *** empty log message ***
 *
 *
 */
include_once "../include/connect.inc.php";
include_once "../include/config.inc.php";
include_once "../include/functions.inc.php";
include_once "../include/$dbsys.inc.php";
include_once "../include/misc.inc.php";
include_once "../include/mrbs_sql.inc.php";
$grr_script_name = "view_rights_etablissement.php";

// Settings
require_once("../include/settings.class.php");
#Chargement des valeurs de la table settings
$settings = new Settings();
if (!$settings)
	die("Erreur chargement settings");

// Session related functions
include_once "../include/session.inc.php";
// Resume session
if (!grr_resumeSession()) {
    if ((Settings::get("authentification_obli")==1) or ((Settings::get("authentification_obli")==0) and (isset($_SESSION['login'])))) {
       header("Location: ./logout.php?auto=1&url=$url");
       die();
    }
};

// Paramètres langage
include_once "../include/language.inc.php";

if ((Settings::get("authentification_obli")==0) and (getUserName()=='')) {
    $type_session = "no_session";
} else {
    $type_session = "with_session";
}

$id = isset($_GET["id"]) ? $_GET["id"] : NULL;
if (isset($id)) settype($area_id,"integer");

if (authGetUserLevel(getUserName(),$id,"etab") < 4)
{
    $day   = date("d");
    $month = date("m");
    $year  = date("Y");
    showAccessDenied($day, $month, $year, '',$back);
    exit();
}

echo begin_page(Settings::get("company").get_vocab("deux_points").get_vocab("mrbs"));

$res = grr_sql_query("SELECT * FROM ".TABLE_PREFIX."_etablissement WHERE id='".$id."'");
if (! $res) fatal_error(0, get_vocab('error_etab') . $id . get_vocab('not_found'));

$row = grr_sql_row_keyed($res, 0);
grr_sql_free($res);

?>

<h3 style="text-align:center;"><?php echo get_vocab("match_etab").get_vocab("deux_points")."&nbsp;".htmlspecialchars($row["shortname"]);?></h3>

<?php 
	// On affiche pour les administrateurs les utilisateurs ayant des privilèges sur cette établissement
    echo "\n<h2>".get_vocab('utilisateurs ayant privileges sur etablissement')."</h2>";
    $a_privileges = 'n';
    // on teste si des utilateurs administrent l'établissement
    $req_admin = "select u.login, u.nom, u.prenom, u.etat from ".TABLE_PREFIX."_utilisateurs u
    left join ".TABLE_PREFIX."_j_useradmin_etablissement j on u.login=j.login
    where j.id_etablissement = '".$id."' order by u.nom, u.prenom";
    $res_admin = grr_sql_query($req_admin);
    $is_admin = '';
    if ($res_admin) {
        for ($j = 0; ($row_admin = grr_sql_row($res_admin, $j)); $j++) {
            $is_admin .= $row_admin[1]." ".$row_admin[2]." (".$row_admin[0].")";
            if ($row_admin[3] == 'inactif') $is_admin .= "<b> -> ".get_vocab("no_activ_user")."</b>";
            $is_admin .= "<br />";
        }
    }
    if ($is_admin != '') {
        $a_privileges = 'y';
        echo "\n<h3><b>".get_vocab("utilisateurs administrateurs etablissement")."</b></h3>";
        echo $is_admin;
    }
    if ($a_privileges == 'n') {
    	echo "<p>".get_vocab("aucun autilisateur").".</p>";
    }

    $req_restreint = "select u.login, u.nom, u.prenom, u.etat  from ".TABLE_PREFIX."_utilisateurs u
        left join ".TABLE_PREFIX."_j_user_etablissement j on u.login=j.login
        where j.id_etablissement = '".$id."' order by u.nom, u.prenom";
    $res_restreint = grr_sql_query($req_restreint);
    $is_restreint = '';
    if ($res_restreint) {
    	for ($j = 0; ($row_restreint = grr_sql_row($res_restreint, $j)); $j++) {
    		$is_restreint .= $row_restreint[1]." ".$row_restreint[2]." (".$row_restreint[0].")";
    		if ($row_restreint[3] == 'inactif') $is_restreint .= "<b> -> ".get_vocab("no_activ_user")."</b>";
    		$is_restreint .= "<br />";
    	}
    }
    if ($is_restreint != '') {
    	$a_privileges = 'y';
    	echo "\n<h3>".get_vocab("utilisateurs acces restreint etablissement")."</h3>\n";
    	echo "<p>".$is_restreint."</p>";
    }
        
   
include_once "../include/trailer.inc.php";