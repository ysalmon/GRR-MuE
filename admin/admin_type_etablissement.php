<?php
/**
 * admin_type_etablissement.php
 * interface de gestion des types de réservations pour un domaine
 * Ce script fait partie de l'application GRR
 * Dernière modification : $Date: 2010-05-07 21:26:44 $
 * @author    Laurent Delineau <laurent.delineau@ac-poitiers.fr>
 * @copyright Copyright 2003-2008 Laurent Delineau
 * @link      http://www.gnu.org/licenses/licenses.html
 * @package   root
 * @version   $Id: admin_type_area.php,v 1.1 2010-05-07 21:26:44 grr Exp $
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

include_once "../include/admin.inc.php";
$grr_script_name = "admin_type_etablissement.php";

// Initialisation
$id_etablissement = getIdEtablissementCourant();

if(authGetUserLevel(getUserName(),$id_etablissement,'etab') < 6)
{
    $back = '';
    if (isset($_SERVER['HTTP_REFERER'])) $back = htmlspecialchars($_SERVER['HTTP_REFERER']);
    $day   = date("d");
    $month = date("m");
    $year  = date("Y");
    showAccessDenied($day, $month, $year, '',$back);
    exit();
}

$back = "";
if (isset($_SERVER['HTTP_REFERER'])) $back = htmlspecialchars($_SERVER['HTTP_REFERER']);


if ((isset($_GET['msg'])) and isset($_SESSION['displ_msg'])  and ($_SESSION['displ_msg']=='yes') )  {
   $msg = $_GET['msg'];
}
else
   $msg = '';

# print the page header
//print_header("","","","",$type="with_session", $page="admin");
print_header('', '', '', $type = 'with_session');
// Affichage de la colonne de gauche
include_once('admin_col_gauche.php');



//
// Suppression d'un type de réservation
//
if ((isset($_GET['action_del'])) and ($_GET['js_confirmed'] ==1) and ($_GET['action_del']='yes')) {
    // faire le test si il existe une réservation en cours avec ce type de réservation
    $type_id = grr_sql_query1("select type_letter from ".TABLE_PREFIX."_type_area where id = '".$_GET['type_del']."'");
    $test1 = grr_sql_query1("select count(id) from ".TABLE_PREFIX."_entry where type= '".$type_id."'");
    $test2 = grr_sql_query1("select count(id) from ".TABLE_PREFIX."_repeat where type= '".$type_id."'");
    if (($test1 != 0) or ($test2 != 0)) {
        $msg =  "Suppression impossible : des réservations ont été enregistrées avec ce type.";
    } else {
    	
		$sql = "DELETE FROM ".TABLE_PREFIX."_j_etablissement_type_area WHERE id_type_area='".$_GET['type_del']."'";
    	if (grr_sql_command($sql) < 0) {
    		fatal_error(1, "<p>" . grr_sql_error());
		}
    	
		$sql = "DELETE FROM ".TABLE_PREFIX."_type_area WHERE id='".$_GET['type_del']."'";
		if (grr_sql_command($sql) < 0) {
        	fatal_error(1, "<p>" . grr_sql_error());
		}
		
        $sql = "DELETE FROM ".TABLE_PREFIX."_j_type_area WHERE id_type='".$_GET['type_del']."'";
        if (grr_sql_command($sql) < 0) {
        	fatal_error(1, "<p>" . grr_sql_error());
		}
    }
}

affiche_pop_up($msg,"admin");

$etablissement_name = grr_sql_query1("select shortname from ".TABLE_PREFIX."_etablissement where id='".$id_etablissement."'");
echo "<div>";
echo "<h2>".get_vocab('admin_type_etablissement.php')."</h2>";
echo "<h2>".get_vocab("match_etablissement").get_vocab('deux_points')." ".$etablissement_name."</h2>";
echo get_vocab('admin_type_explications');

$sql = "SELECT A.id, type_name, order_display, couleur, type_letter FROM ".TABLE_PREFIX."_type_area AS A
JOIN ".TABLE_PREFIX."_j_etablissement_type_area AS J ON J.id_type_area = A.id 
WHERE J.id_etablissement = $id_etablissement
ORDER BY order_display, type_letter";




if(authGetUserLevel(getUserName(),-1) >= 6)
	echo "<div><a href=\"admin_type_etablissement_modify.php?id=0\">".get_vocab("display_add_type")."</a></div>";

$res = grr_sql_query($sql);
$nb_lignes = grr_sql_count($res);
if ($nb_lignes == 0) {
	echo "</body></html>";
	die();
}


// Affichage du tableau
//echo "<table border=\"1\" cellpadding=\"3\"><tr>\n";
echo '<table class="table table-hover table-bordered"><thead><tr>';
// echo "<tr><th><b>".get_vocab("type_num")."</a></b></th>\n";
//echo "<th><b>".get_vocab("type_num")."</b></th>\n";
echo "<th><b>".get_vocab("type_name")."</b></th>\n";
echo "<th><b>".get_vocab("type_color")."</b></th>\n";
echo "<th><b>".get_vocab("type_order")."</b></th>\n";
echo "<th><b>".get_vocab("delete")."</b></th>";
echo "</tr></thead><tbody>";
if ($res) {
    for ($i = 0; ($row = grr_sql_row($res, $i)); $i++)
    {
    $id_type        = $row[0];
    $type_name      = $row[1];
    $order_display     = $row[2];
    $couleur = $row[3];
    $type_letter = $row[4];
    // Affichage des numéros et descriptions
    $col[$i][1] = $type_letter;
    $col[$i][2] = $id_type;
    $col[$i][3] = $type_name;
    // Affichage de l'ordre
    $col[$i][4]= $order_display;
    $col[$i][5]= $couleur;

   echo "<tr>\n";
   // echo "<td>{$col[$i][1]}</td>\n";
    echo "<td><a href='admin_type_etablissement_modify.php?id_type={$col[$i][2]}'>{$col[$i][3]}</a></td>\n";
    echo "<td style=\"background-color:".$tab_couleur[$col[$i][5]]."\"></td>\n";
    echo "<td>{$col[$i][4]}</td>\n";
	$themessage = get_vocab("confirm_del");
    echo "<td><a href='admin_type_etablissement.php?&amp;type_del={$col[$i][2]}&amp;action_del=yes' onclick='return confirmlink(this, \"{$col[$i][2]}\", \"$themessage\")'>".get_vocab("delete")."</a></td>";
    // Fin de la ligne courante
    echo "</tr>";

    }

 
}
echo "</tbody></table>";


?>
</body>
</html>