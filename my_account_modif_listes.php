<?php
/**
 * my_account_modif_listes.php
 * Page "Ajax" utilisée pour générer les listes de domaines et de ressources, en liaison avec my_account.php
 * Dernière modification : $Date: 2009-04-14 12:59:17 $
 * @author    Laurent Delineau <laurent.delineau@ac-poitiers.fr>
 * @copyright Copyright 2003-2008 Laurent Delineau
 * @link      http://www.gnu.org/licenses/licenses.html
 * @package   root
 * @version   $Id: my_account_modif_listes.php,v 1.4 2009-04-14 12:59:17 grr Exp $
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
//Arguments passés par la méthode GET :
//$use_etab : 'y' (fonctionnalité multietab activée) ou 'n' (fonctionnalité multietab désactivée)
//$use_site : 'y' (fonctionnalité multisite activée) ou 'n' (fonctionnalité multisite désactivée)
//$id_etab : l'identifiant de l'établissemnt
//$id_site : l'identifiant du site
//$default_area : domaine par défaut
//$default_room : ressource par défaut
//$session_login : identifiant
//$type : 'site'-> on change la liste des site -  necessite $id_etab, $default_site, $session_login, $use_etab
//		'ressource'-> on change la liste des ressources
//        'domaine'-> on change la liste des domaines
//$action : 'actualiser'-> on actualise la liste 
//          'vider'-> on vide la liste 

include "include/admin.inc.php";
if ((authGetUserLevel(getUserName(), -1) < 1))
{
	showAccessDenied("");
	exit();
}


	
/*
* Actualiser la liste des établissements
*/
if ($_GET['type']=="etab") {

	if (isset($_GET["default_etab"])) {
		$default_etab = $_GET["default_etab"];
		settype($default_etab,"integer");
	} else die();
	if (isset($_GET["session_login"])) {
		$session_login = $_GET["session_login"];
	} else die();
	if (isset($_GET["session_login"])) {
		$session_login = $_GET["session_login"];
	} else die();
	if (isset($_GET["action"])) {
		$action = $_GET["action"];
	} else die();
	if (isset($_GET["use_site"])) {
		$use_site = $_GET["use_site"];
	} else die();

	$atLeastOneSelected = false;
	if ($action == "actualiser") {
		
		$sql = "SELECT E.id, E.shortname
					           FROM ".TABLE_PREFIX."_etablissement E
					           ORDER BY E.shortname";			
		
		
		$resultat = grr_sql_query($sql);
		$nb = mysqli_num_rows($resultat);
		for ($enr = 0; $enr < $nb ; $enr++)
		{
			$row = grr_sql_row($resultat, $enr);
			if (authUserAccesEtab($session_login, $row[0])!=0)
			{
				if ($default_etab == $row[0]){
					$atLeastOneSelected = true;
					break;
				}
			}
		}
		
	} 
	
	$display_liste = '
        <table border="0"><tr>
          <td>'.get_vocab('default_etablissement').'</td>
          <td> ';
    if ($use_site == 'y'){
        $onchange = 'modifier_liste_sites("actualiser");modifier_liste_domaines("vider");modifier_liste_ressources("vider");';
    } else {
        $onchange = 'modifier_liste_domaines("actualiser");modifier_liste_ressources("vider");';
    }
	$display_liste .= "
            <select class='form-control' id='id_etab' name='id_etab' onchange='$onchange'> ";
	if (! $atLeastOneSelected ){
		$display_liste .= '     <option value="-1" selected="selected">'.get_vocab('choose_an_etab').'</option>'."\n";
	} else {
		$display_liste .= '     <option value="-1">'.get_vocab('choose_an_etab').'</option>'."\n";
	}

	if ($resultat){
		for ($enr = 0; ($row = grr_sql_row($resultat, $enr)); $enr++)
		{
			if (authUserAccesEtab($session_login, $row[0])!=0)
			{
				$display_liste .=  '              <option value="'.$row[0].'"';
				if ($default_etab == $row[0])
					$display_liste .= ' selected="selected" ';
				$display_liste .= '>'.htmlspecialchars($row[1]);
				$display_liste .= '</option>'."\n";
			}
		}
	}
		
	$display_liste .= '            </select>';
	$display_liste .=  '</td>
        </tr></table>'."\n";
}


/*
* Actualiser la liste des sites
*/

if ($_GET['type']=="site") {
	
	if (isset($_GET["id_etab"])) {
		$id_etab = $_GET["id_etab"];
		settype($id_site,"integer");
	} else die();
	if (isset($_GET["default_site"])) {
		$default_site= $_GET["default_site"];
		settype($default_site,"integer");
	} else die();
	if (isset($_GET["session_login"])) {
		$session_login = $_GET["session_login"];
	} else die();
	if (isset($_GET["session_login"])) {
		$session_login = $_GET["session_login"];
	} else die();
	if (isset($_GET["action"])) {
		$action = $_GET["action"];
	} else die();
	if (isset($_GET["use_etab"])) {
		$use_etab = $_GET["use_etab"];
	} else die();

	$atLeastOneSelected = false;
	
	$sql = '';
	$resultat = false;
	$default_area = -1;
	
	if ($action == "actualiser") {
		if ($use_etab=='y') {
			if ($id_etab >= 1) {
				$sql = "SELECT S.id, S.sitename
		           FROM ".TABLE_PREFIX."_site S
		           JOIN ".TABLE_PREFIX."_j_etablissement_site AS J ON J.id_site = S.id
		           WHERE J.id_etablissement = $id_etab 
		           ORDER BY S.sitename";
			}
		} else {
			$sql = "SELECT S.id, S.sitename
					           FROM ".TABLE_PREFIX."_site S
					           ORDER BY S.sitename";
		}
		
		if ($sql != ''){
			$resultat = grr_sql_query($sql);
			$nb = mysqli_num_rows($resultat);
			for ($enr = 0; $enr < $nb ; $enr++)
			{
				$row = grr_sql_row($resultat, $enr);
				if ($default_area == $row[0]){
					$atLeastOneSelected = true;
					break;
				}
			}
		}
	} 
	
	$display_liste = '
        <table border="0"><tr>
          <td>'.get_vocab('default_site').'</td>
          <td> ';
	$display_liste .= '<select class="form-control" id="id_site" name="id_site"  onchange="modifier_liste_domaines(\'actualiser\');modifier_liste_ressources(\'vider\');"> ';
	if (! $atLeastOneSelected ){
		$display_liste .= '     <option value="-1" selected="selected">'.get_vocab('choose_a_site').'</option>'."\n";
	} else {
		$display_liste .= '     <option value="-1">'.get_vocab('choose_a_site').'</option>'."\n";
	}

	if ($resultat){
		for ($enr = 0; ($row = grr_sql_row($resultat, $enr)); $enr++)
		{
			$display_liste .=  '              <option value="'.$row[0].'"';
			if ($default_site == $row[0])
			$display_liste .= ' selected="selected" ';
			$display_liste .= '>'.htmlspecialchars($row[1]);
			$display_liste .= '</option>'."\n";
		}
	}

	$display_liste .= '            </select>';
	$display_liste .=  '</td>
        </tr></table>'."\n";
}



/*
 * Actualiser la liste des domaines
 */

if ($_GET['type']=="domaine") {
	// Initialisation
	if (isset($_GET["id_site"])) {
		$id_site = $_GET["id_site"];
		settype($id_site,"integer");
	} else die();
	if (isset($_GET["id_etab"])) {
		$id_etab = $_GET["id_etab"];
		settype($id_site,"integer");
	} else die();
	if (isset($_GET["default_area"])) {
		$default_area = $_GET["default_area"];
		settype($default_area,"integer");
	} else die();
	if (isset($_GET["session_login"])) {
		$session_login = $_GET["session_login"];
	} else die();
	if (isset($_GET["use_site"])) {
		$use_site = $_GET["use_site"];
	} else die();
	if (isset($_GET["use_etab"])) {
		$use_etab = $_GET["use_etab"];
	} else die();
	if (isset($_GET["action"])) {
		$action = $_GET["action"];
	} else die();

	
	$atLeastOneSelected = false;

	$sql = '';
	$resultat = false;
	
	if ($action == "actualiser") {
		
		if ($use_site=='y' && $id_site >= 1) {
			$sql = "SELECT A.id, A.area_name, A.access
			           FROM ".TABLE_PREFIX."_area A
			           JOIN ".TABLE_PREFIX."_j_site_area AS J ON J.id_area = A.id
			           WHERE J.id_site = $id_site 
			           ORDER BY A.order_display, A.area_name";
		} else if ($use_etab =='y' && $id_etab >= 1) {
            $sql = "SELECT A.id, A.area_name, A.access
			           FROM ".TABLE_PREFIX."_area A
			           JOIN ".TABLE_PREFIX."_j_site_area AS J ON J.id_area = A.id
			           JOIN ".TABLE_PREFIX."_j_etablissement_site AS ES ON ES.id_site = J.id_site
			           WHERE ES.id_etablissement = $id_etab 
			           ORDER BY A.order_display, A.area_name";
		} else if ($use_site =='n' && $use_etab =='n' )  {
			$sql = "SELECT id, area_name,access
			           FROM ".TABLE_PREFIX."_area
			           ORDER BY order_display, area_name";
		}
		if ($sql != '' )  {
			$resultat = grr_sql_query($sql);
			$nb = mysqli_num_rows($resultat);
			for ($enr = 0; $enr < $nb ; $enr++)
			{
				$row = grr_sql_row($resultat, $enr);
				if (authUserAccesArea($session_login, $row[0])!=0)
				{
					if ($default_area == $row[0]){
						$atLeastOneSelected = true;
						break;
					}
				}
			}
		}
	}

	$display_liste = '
	        <table border="0"><tr>
	          <td>'.get_vocab('default_area').'</td>
	          <td> ';
	$display_liste .= ' <select class="form-control" id="id_area" name="id_area"  onchange="modifier_liste_ressources(\'actualiser\');"> ';
	if (! $atLeastOneSelected ){
		$display_liste .= '     <option value="-1" selected="selected">'.get_vocab('choose_an_area').'</option>'."\n";
	} else {
		$display_liste .= '     <option value="-1">'.get_vocab('choose_an_area').'</option>'."\n";
	}

	if ($resultat){
		for ($enr = 0; ($row = grr_sql_row($resultat, $enr)); $enr++){
			
			if (authUserAccesArea($session_login, $row[0])!=0){
				
				$display_liste .=  '              <option value="'.$row[0].'"';
				if ($default_area == $row[0])
					$display_liste .= ' selected="selected" ';
				$display_liste .= '>'.htmlspecialchars($row[1]);
				if ($row[2]=='r')
					$display_liste .= ' ('.get_vocab('restricted').')';
				$display_liste .= '</option>'."\n";
			}
		}
	}
	$display_liste .= '            </select>';
	$id_area = 5;
	$display_liste .=  '</td>
</tr></table>'."\n";
}


/*
 * Actualiser la liste des ressources
 */

if ($_GET['type']=="ressource") {
  
	// Initialisation
	if (isset($_GET["id_area"])) {
		$id_area = $_GET["id_area"];
		settype($id_area,"integer");
	} else die();
	if (isset($_GET["default_room"])) {
		$default_room = $_GET["default_room"];
		settype($default_area,"integer");
	} else die();
	if (isset($_GET["session_login"])) {
		$session_login = $_GET["session_login"];
	} else die();
	if (isset($_GET["action"])) {
		$action = $_GET["action"];
	} else die();
	
	$atLeastOneSelected = false;
	
	$sql = '';
	$resultat = false;
	
	if ($action == "actualiser") {
		if ($id_area >= 1) {
			$sql = "SELECT id, room_name
           		FROM ".TABLE_PREFIX."_room
           		WHERE area_id='".$id_area."'";
			$tab_rooms_noaccess = verif_acces_ressource(getUserName(), 'all');
			foreach($tab_rooms_noaccess as $key){
				$sql .= " and id != $key ";
			}
			$sql .= " ORDER BY order_display,room_name";
		} 

		if ($sql != '' ){
			$resultat = grr_sql_query($sql);
			$nb = mysqli_num_rows($resultat);
			for ($enr = 0; $enr < $nb ; $enr++)
			{
				$row = grr_sql_row($resultat, $enr);
				if ($default_area == $row[0]){
					$atLeastOneSelected = true;
					break;
				}
			}
		}
	}

	$display_liste = '
		        <table border="0"><tr>
		          <td>'.get_vocab('default_room').'</td>
		          <td> ';
	$display_liste .= ' <select class="form-control" id="id_room" name="id_room"> ';
	if (! $atLeastOneSelected || $default_room == -1 ){
		$display_liste .= '     <option value="-1" selected="selected">'.get_vocab('default_room_all').'</option>'."\n";
	} else {
		$display_liste .= '     <option value="-1">'.get_vocab('default_room_all').'</option>'."\n";
	}
	$display_liste .= '<option value="-2"';
	if ($default_room == -2)
		$display_liste .= ' selected="selected" ';
	$display_liste .= ' >'.get_vocab('default_room_week_all').'</option>'."\n".
	              '<option value="-3"';
	if ($default_room == -3)
		$display_liste .= ' selected="selected" ';
	$display_liste .= ' >'.get_vocab('default_room_month_all').'</option>'."\n".
	              '<option value="-4"';
	if ($default_room == -4)
		$display_liste .= ' selected="selected" ';
	$display_liste .= ' >'.get_vocab('default_room_month_all_bis').'</option>'."\n";
	
	if ($resultat){
		for ($enr = 0; ($row = grr_sql_row($resultat, $enr)); $enr++)
		{
			$display_liste .=  '              <option value="'.$row[0].'"';
			if ($default_room == $row[0])
			$display_liste .= ' selected="selected" ';
			$display_liste .= '>'.htmlspecialchars($row[1]).' '.get_vocab('display_week');
			$display_liste .= '</option>'."\n";
		}
	}
	
    $display_liste .= '            </select>
          </td>
        </tr></table>'."\n";
  
}


if ($unicode_encoding)
	header("Content-Type: text/html;charset=utf-8");
else
	header("Content-Type: text/html;charset=".$charset_html);
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
echo $display_liste;
?>
