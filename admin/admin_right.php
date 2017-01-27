<?php
/**
 * admin_right.php
 * Interface de gestion des droits de gestion des utilisateurs
 * Dernière modification : $Date: 2009-04-14 12:59:17 $
 * @author    Laurent Delineau <laurent.delineau@ac-poitiers.fr>
 * @copyright Copyright 2003-2008 Laurent Delineau
 * @link      http://www.gnu.org/licenses/licenses.html
 * @package   admin
 * @version   $Id: admin_right.php,v 1.10 2009-04-14 12:59:17 grr Exp $
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
include "../include/admin.inc.php";
$grr_script_name = "admin_right.php";

$back = '';
if (isset($_SERVER['HTTP_REFERER']))
	$back = htmlspecialchars($_SERVER['HTTP_REFERER']);

$id_area = isset($_POST["id_area"]) ? $_POST["id_area"] : (isset($_GET["id_area"]) ? $_GET["id_area"] : NULL);
$id_room = isset($_POST["id_room"]) ? $_POST["id_room"] : (isset($_GET["id_room"]) ? $_GET["id_room"] : NULL);

if (isset($id_room))
	settype($id_room,"integer");
if (!isset($id_area))
	settype($id_area,"integer");

check_access(4, $back);

//print the page header
print_header("", "", "", $type="with_session");

// Affichage de la colonne de gauche
include "admin_col_gauche.php";

// tableau des ressources auxquelles l'utilisateur n'a pas accès
$tab_rooms_noaccess = verif_acces_ressource(getUserName(), 'all');
$reg_admin_login = isset($_POST["reg_admin_login"]) ? $_POST["reg_admin_login"] : NULL;
$reg_multi_admin_login = isset($_POST["reg_multi_admin_login"]) ? $_POST["reg_multi_admin_login"] : NULL;
$test_user =  isset($_POST["reg_multi_admin_login"]) ? "multi" : (isset($_POST["reg_admin_login"]) ? "simple" : NULL);
$action = isset($_GET["action"]) ? $_GET["action"] : NULL;
$msg = '';

if ($test_user == "multi")
{
	foreach ($reg_multi_admin_login as $valeur)
	{
	// On commence par vérifier que le professeur n'est pas déjà présent dans cette liste.
	// ajout pour une ressource d'un domaine
		if ($id_room != -1)
		{
		// Ressource
		// On vérifie que la ressource $id_room existe
			$test = grr_sql_query1("SELECT id FROM ".TABLE_PREFIX."_room WHERE id='".$id_room."'");
			if ($test == -1)
			{
				showAccessDenied($back);
				exit();
			}
			if (in_array($id_room,$tab_rooms_noaccess))
			{
				showAccessDenied($back);
				exit();
			}
		// La ressource existe : on vérifie les privilèges de l'utilisateur
			if (authGetUserLevel(getUserName(),$id_room) < 4)
			{
				showAccessDenied($back);
				exit();
			}
			$sql = "SELECT * FROM ".TABLE_PREFIX."_j_user_room WHERE (login = '$valeur' and id_room = '$id_room')";
			$res = grr_sql_query($sql);
			$test = grr_sql_count($res);
			if ($test != "0")
			{
				$msg = get_vocab("warning_exist");
			}
			else
			{
				if ($valeur != '')
				{
					$sql = "INSERT INTO ".TABLE_PREFIX."_j_user_room SET login= '$valeur', id_room = '$id_room'";
					if (grr_sql_command($sql) < 0)
						fatal_error(0, "<p>" . grr_sql_error());
					else
						$msg = get_vocab("add_multi_user_succeed");
				}
			}
		}
		else
		{
		//ajout pour toutes les ressources du domaine
		// Domaine
		// On vérifie que le domaine $id_area existe
			$test = grr_sql_query1("SELECT id FROM ".TABLE_PREFIX."_area WHERE id='".$id_area."'");
			if ($test == -1)
			{
				showAccessDenied($back);
				exit();
			}
		// Le domaine existe : on vérifie les privilèges de l'utilisateur
			if (authGetUserLevel(getUserName(),$id_area,'area') < 4)
			{
				showAccessDenied($back);
				exit();
			}
			$sql = "SELECT id FROM ".TABLE_PREFIX."_room WHERE area_id=$id_area";
		// on ne cherche pas parmi les ressources invisibles pour l'utilisateur
			foreach ($tab_rooms_noaccess as $key)
				$sql .= " and id != $key ";
			$res = grr_sql_query($sql);
			if ($res)
			{
				for ($i = 0; ($row = grr_sql_row($res, $i)); $i++)
				{
					$sql2 = "SELECT login FROM ".TABLE_PREFIX."_j_user_room WHERE (login = '$valeur' and id_room = '$row[0]')";
					$res2 = grr_sql_query($sql2);
					$nb = grr_sql_count($res2);
					if ($nb == 0)
					{
						$sql3 = "INSERT INTO ".TABLE_PREFIX."_j_user_room (login, id_room) VALUES ('$valeur','$row[0]')";
						if (grr_sql_command($sql3) < 0)
							fatal_error(0, "<p>" . grr_sql_error());
						else
							$msg = get_vocab("add_multi_user_succeed");
					}
				}
			}
		}
	}
}
if ($test_user == "simple")
{
	if ($reg_admin_login)
	{
	// On commence par vérifier que le professeur n'est pas déjà présent dans cette liste.
	// ajout pour une ressource d'un domaine
		if ($id_room != -1)
		{
		// Ressource
		// On vérifie que la ressource $id_room existe
			$test = grr_sql_query1("SELECT id FROM ".TABLE_PREFIX."_room WHERE id='".$id_room."'");
			if ($test == -1)
			{
				showAccessDenied($back);
				exit();
			}
			if (in_array($id_room,$tab_rooms_noaccess))
			{
				showAccessDenied($back);
				exit();
			}
		// La ressource existe : on vérifie les privilèges de l'utilisateur
			if (authGetUserLevel(getUserName(),$id_room) < 4)
			{
				showAccessDenied($back);
				exit();
			}
			$sql = "SELECT * FROM ".TABLE_PREFIX."_j_user_room WHERE (login = '$reg_admin_login' and id_room = '$id_room')";
			$res = grr_sql_query($sql);
			$test = grr_sql_count($res);
			if ($test != "0")
				$msg = get_vocab("warning_exist");
			else
			{
				if ($reg_admin_login != '')
				{
					$sql = "INSERT INTO ".TABLE_PREFIX."_j_user_room SET login= '$reg_admin_login', id_room = '$id_room'";
					if (grr_sql_command($sql) < 0)
						fatal_error(0, "<p>" . grr_sql_error());
					else
						$msg = get_vocab("add_user_succeed");
				}
			}
		}
		else
		{
			//ajout pour toutes les ressources du domaine
			// Domaine
			// On vérifie que le domaine $id_area existe
			$test = grr_sql_query1("SELECT id FROM ".TABLE_PREFIX."_area WHERE id='".$id_area."'");
			if ($test == -1)
			{
				showAccessDenied($back);
				exit();
			}
			// Le domaine existe : on vérifie les privilèges de l'utilisateur
			if (authGetUserLevel(getUserName(),$id_area,'area') < 4)
			{
				showAccessDenied($back);
				exit();
			}
			$sql = "SELECT id FROM ".TABLE_PREFIX."_room WHERE area_id=$id_area";
			// on ne cherche pas parmi les ressources invisibles pour l'utilisateur
			foreach ($tab_rooms_noaccess as $key)
				$sql .= " and id != $key ";
			$res = grr_sql_query($sql);
			if ($res)
			{
				for ($i = 0; ($row = grr_sql_row($res, $i)); $i++)
				{
					$sql2 = "SELECT login FROM ".TABLE_PREFIX."_j_user_room WHERE (login = '$reg_admin_login' and id_room = '$row[0]')";
					$res2 = grr_sql_query($sql2);
					$nb = grr_sql_count($res2);
					if ($nb==0)
					{
						$sql3 = "INSERT INTO ".TABLE_PREFIX."_j_user_room (login, id_room) values ('$reg_admin_login','$row[0]')";
						if (grr_sql_command($sql3) < 0)
							fatal_error(0, "<p>" . grr_sql_error());
						else
							$msg = get_vocab("add_user_succeed");
					}
				}
			}
		}
	}
}
if ($action)
{
	if ($action == "del_admin")
	{
		if (authGetUserLevel(getUserName(),$id_room) < 4)
		{
			showAccessDenied($back);
			exit();
		}
		unset($login_admin); $login_admin = $_GET["login_admin"];
		$sql = "DELETE FROM ".TABLE_PREFIX."_j_user_room WHERE (login='$login_admin' and id_room = '$id_room')";
		if (grr_sql_command($sql) < 0)
			fatal_error(0, "<p>" . grr_sql_error());
		else
			$msg = get_vocab("del_user_succeed");
	}
	if ($action == "del_admin_all")
	{
		if (authGetUserLevel(getUserName(),$id_area,'area') < 4)
		{
			showAccessDenied($back);
			exit();
		}
		$sql = "SELECT id FROM ".TABLE_PREFIX."_room WHERE area_id=$id_area ";
		// on ne cherche pas parmi les ressources invisibles pour l'utilisateur
		foreach ($tab_rooms_noaccess as $key)
			$sql .= " and id != $key ";
		$sql .= " order by room_name";
		$res = grr_sql_query($sql);
		if ($res)
		{
			for ($i = 0; ($row = grr_sql_row($res, $i)); $i++)
			{
				$sql2 = "DELETE FROM ".TABLE_PREFIX."_j_user_room WHERE (login='".$_GET['login_admin']."' and id_room = '$row[0]')";
				if (grr_sql_command($sql2) < 0)
					fatal_error(0, "<p>" . grr_sql_error());
				else
					$msg = get_vocab("del_user_succeed");
			}
		}
	}
}
if ((empty($id_area)) && (isset($row[0])))
{
	if (authGetUserLevel(getUserName(),$row[0],'area') >= 6)
		$id_area = get_default_area();
	else
	{
		//Retourne le domaine par défaut; Utilisé si aucun domaine n'a été défini.
		// On cherche le premier domaine à accès non restreint
		$id_area = grr_sql_query1("SELECT a.id FROM ".TABLE_PREFIX."_area a, ".TABLE_PREFIX."_j_useradmin_area j
			WHERE a.id=j.id_area and j.login='".getUserName()."'
			ORDER BY a.access, a.order_display, a.area_name
			LIMIT 1");
	}
}

if (empty($id_room))
	$id_room = -1;

echo "<h2>".get_vocab('admin_right.php')."</h2>\n";
echo "<p><i>".get_vocab("admin_right_explain")."</i></p>\n";

// Affichage d'un pop-up
affiche_pop_up($msg,"admin");

//Table with areas, rooms.
echo "<table><tr>\n";
$this_area_name = "";
$this_room_name = "";

//Show all areas
echo "<td ><p><b>".get_vocab("areas")."</b></p>\n";
$out_html = "<form id=\"area\" action=\"admin_right.php\" method=\"post\">\n<div><SELECT name=\"area\" onchange=\"area_go()\">\n";
$out_html .= "<option value=\"admin_right.php?id_area=-1\">".get_vocab('select')."</option>\n";

if (Settings::get("module_multietablissement") == "Oui") {
	$idEtablissement = getIdEtablissementCourant();
	$sql = "select A.id, A.area_name from ".TABLE_PREFIX."_area AS A 
			JOIN ".TABLE_PREFIX."_j_site_area AS SA ON SA.id_area = A.id
			JOIN ".TABLE_PREFIX."_j_etablissement_site AS ES ON ES.id_SITE = SA.id_site
			WHERE ES.id_etablissement = $idEtablissement
			order by A.order_display";
} else {
	$sql = "select id, area_name from ".TABLE_PREFIX."_area order by order_display";
}

$res = grr_sql_query($sql);
if ($res)
{
	for ($i = 0; ($row = grr_sql_row($res, $i)); $i++)
	{
		$selected = ($row[0] == $id_area) ? "selected=\"selected\"" : "";
		$link = "admin_right.php?id_area=$row[0]";
		// On affiche uniquement les domaines administrés par l'utilisateur
		if (authGetUserLevel(getUserName(),$row[0],'area') >= 4)
			$out_html .= "<option $selected value=\"$link\">" . htmlspecialchars($row[1])."</option>\n";
	}
}
$out_html .= "</select></div>\n
<script type=\"text/javascript\" >
	<!--
	function area_go()
	{
		box = document.getElementById(\"area\").area;
		destination = box.options[box.selectedIndex].value;
		if (destination) location.href = destination;
	}
	// -->
</script>
<noscript>
	<div><input type=\"submit\" value=\"Change\" /></div>
</noscript>
</form>";
echo $out_html;

$this_area_name = grr_sql_query1("SELECT area_name FROM ".TABLE_PREFIX."_area WHERE id=$id_area");
$this_room_name = grr_sql_query1("SELECT room_name FROM ".TABLE_PREFIX."_room WHERE id=$id_room");
$this_room_name_des = grr_sql_query1("SELECT description FROM ".TABLE_PREFIX."_room WHERE id=$id_room");
echo "</td>\n";

//Show all rooms in the current area
echo "<td><p><b>".get_vocab('rooms')."</b></p>";

//should we show a drop-down for the room list, or not?
$out_html = "<form id=\"room\" action=\"admin_right.php\" method=\"post\">\n<div><SELECT name=\"room\" onchange=\"room_go()\">\n";
$out_html .= "<option value=\"admin_right.php?id_area=$id_area&amp;id_room=-1\">".get_vocab('select_all')."</option>\n";
$sql = "SELECT id, room_name, description FROM ".TABLE_PREFIX."_room WHERE area_id=$id_area ";
foreach ($tab_rooms_noaccess as $key)
	$sql .= " and id != $key ";
$sql .= " order by order_display,room_name";
$res = grr_sql_query($sql);
if ($res)
{
	for ($i = 0; ($row = grr_sql_row($res, $i)); $i++)
	{
		if ($row[2])
			$temp = " (".htmlspecialchars($row[2]).")";
		else
			$temp = "";
		$selected = ($row[0] == $id_room) ? "selected=\"selected\"" : "";
		$link = "admin_right.php?id_area=$id_area&amp;id_room=$row[0]";
		$out_html .= "<option $selected value=\"$link\">" . htmlspecialchars($row[1].$temp)."</option>\n";
	}
}
$out_html .= "</select></div>\n
<script type=\"text/javascript\" >
	<!--
	function room_go()
	{
		box = document.getElementById(\"room\").room;
		destination = box.options[box.selectedIndex].value;
		if (destination) location.href = destination;
	}
		// -->
</script>
<noscript>
	<div><input type=\"submit\" value=\"Change\" /></div>
</noscript>
</form>";
echo $out_html;
echo "</td>\n";
echo "</tr></table>\n";
//Don't continue if this area has no rooms:
if ($id_area <= 0)
{
	echo "<h1>".get_vocab("no_area")."</h1>";
	// fin de l'affichage de la colonne de droite
	echo "</td></tr></table></body></html>";
	exit;
}
//Show area and room:
if ($this_room_name_des != '-1')
	$this_room_name_des = " (".$this_room_name_des.")";
else
	$this_room_name_des = '';
echo "<table border=\"1\" cellpadding=\"5\"><tr><td class='paddingLR5'>";
if ($id_room != -1)
{
	$sql = "SELECT u.login, u.nom, u.prenom FROM ".TABLE_PREFIX."_utilisateurs u, ".TABLE_PREFIX."_j_user_room j WHERE (j.id_room='$id_room' and u.login=j.login)  order by u.nom, u.prenom";
	$res = grr_sql_query($sql);
	$nombre = grr_sql_count($res);
	if ($nombre != 0)
		echo "<h3>".get_vocab("user_list")."</h3>";
	if ($res)
	{
		for ($i = 0; ($row = grr_sql_row($res, $i)); $i++)
		{
			$login_admin = $row[0];
			$nom_admin = htmlspecialchars($row[1]);
			$prenom_admin = htmlspecialchars($row[2]);
			echo "<b>";
			echo "$nom_admin $prenom_admin</b> | <a href='admin_right.php?action=del_admin&amp;login_admin=".urlencode($login_admin)."&amp;id_room=$id_room&amp;id_area=$id_area'>".get_vocab("delete")."</a><br />";
		}
	}
	if ($nombre == 0)
		echo "<h3><span class=\"avertissement\">".get_vocab("no_admin")."</span></h3>";
}
else
{
	$adminAllRoom = array();
	
	$exist_admin='no';
	
	if (Settings::get("module_multietablissement") == "Oui") {
    	$idEtablissement = getIdEtablissementCourant();
    	$sql = "select U.login, U.nom, U.prenom 
    			FROM ".TABLE_PREFIX."_utilisateurs AS U
    			JOIN ".TABLE_PREFIX."_j_user_etablissement AS UE ON UE.login = U.login
    	 		WHERE (U.statut='utilisateur' or U.statut='gestionnaire_utilisateur')
    			AND UE.id_etablissement = $idEtablissement";
    } else {
    	$sql = "select login, nom, prenom from ".TABLE_PREFIX."_utilisateurs where (statut='utilisateur' or statut='gestionnaire_utilisateur')";
    }
	
	$res = grr_sql_query($sql);
	if ($res)
	{
		for ($i = 0; ($row = grr_sql_row($res, $i)); $i++)
		{
			$is_admin = 'yes';
			$sql2 = "SELECT id, room_name, description FROM ".TABLE_PREFIX."_room WHERE area_id=$id_area ";
			foreach ($tab_rooms_noaccess as $key)
				$sql2 .= " and id != $key ";
			$sql2 .= " order by order_display,room_name";
			$res2 = grr_sql_query($sql2);
			if ($res2)
			{
				$test = grr_sql_count($res2);
				if ($test != 0)
				{
					for ($j = 0; ($row2 = grr_sql_row($res2, $j)); $j++)
					{
						$sql3 = "SELECT login FROM ".TABLE_PREFIX."_j_user_room WHERE (id_room='".$row2[0]."' and login='".$row[0]."')";
						$res3 = grr_sql_query($sql3);
						$nombre = grr_sql_count($res3);
						if ($nombre == 0)
							$is_admin = 'no';
					}
				}
				else
					$is_admin = 'no';
			}
			if ($is_admin == 'yes')
			{
				if ($exist_admin == 'no')
				{
					echo "<h3>".get_vocab("user_list")."</h3>";
					$exist_admin = 'yes';
				}
				echo "<b>";
				echo htmlspecialchars($row[1])." ".htmlspecialchars($row[2])."</b> | <a href='admin_right.php?action=del_admin_all&amp;login_admin=".urlencode($row[0])."&amp;id_area=$id_area'>".get_vocab("delete")."</a><br />";
				$adminAllRoom[] = $row[0];
			}
		}
	}
	if ($exist_admin=='no')
		echo "<h3><span class=\"avertissement\">".get_vocab("no_admin_all")."</span></h3>";
}

if ($id_room > 0) {
	if (Settings::get("module_multietablissement") == "Oui") {
		$idEtablissement = getIdEtablissementCourant();
		$sqlListUserToAdd = "
						SELECT U.login, U.nom, U.prenom FROM ".TABLE_PREFIX."_utilisateurs AS U
						JOIN ".TABLE_PREFIX."_j_user_etablissement AS UE ON U.login = UE.login
						WHERE  
							(U.etat!='inactif' and (U.statut='utilisateur' or U.statut='gestionnaire_utilisateur'))
							AND UE.id_etablissement = $idEtablissement
							AND U.login not in (SELECT login FROM ".TABLE_PREFIX."_j_user_room WHERE id_room = $id_room ) 
						ORDER BY U.nom, U.prenom";
	} else {
		$sqlListUserToAdd = "SELECT U.login, U.nom, U.prenom FROM ".TABLE_PREFIX."_utilisateurs AS U
							WHERE 
								(U.etat!='inactif' and (U.statut='utilisateur' or U.statut='gestionnaire_utilisateur'))
								AND U.login not in (SELECT loggin FROM ".TABLE_PREFIX."_j_user_room WHERE id_room = $id_room )
							ORDER BY U.nom, U.prenom";
	}
} else {
	if (Settings::get("module_multietablissement") == "Oui") {
		$idEtablissement = getIdEtablissementCourant();
		$sqlListUserToAdd = "
							SELECT U.login, U.nom, U.prenom FROM ".TABLE_PREFIX."_utilisateurs AS U
							LEFT JOIN  ".TABLE_PREFIX."_j_user_room AS UR ON UR.login = U.login 
							JOIN ".TABLE_PREFIX."_j_user_etablissement AS UE ON U.login = UE.login
							LEFT JOIN ".TABLE_PREFIX."_j_useradmin_etablissement AS UAE ON UAE.login = U.login
							LEFT JOIN ".TABLE_PREFIX."_j_useradmin_site AS UAS ON UAS.login = U.login
							LEFT JOIN ".TABLE_PREFIX."_j_user_area AS UAA ON UAA.login = U.login
							, ".TABLE_PREFIX."_area AS A
							WHERE  
								(etat!='inactif' and (statut='utilisateur' or statut='gestionnaire_utilisateur'))
								AND UE.id_etablissement = $idEtablissement
								AND (UAE.login IS NOT NULL OR UAS.login IS NOT NULL OR A.access != 'r' OR UAA.login IS NOT NULL )
								AND A.id = $id_area ";
	} else {
		$sqlListUserToAdd = "SELECT U.login, U.nom, U.prenom FROM ".TABLE_PREFIX."_utilisateurs AS U
								JOIN  ".TABLE_PREFIX."_j_user_room AS UR ON UR.login = U.login
								WHERE  (U.etat!='inactif' and (U.statut='utilisateur' or U.statut='gestionnaire_utilisateur')) ";
	}
	
	foreach ($adminAllRoom as $login){
		$sqlListUserToAdd .= " AND U.login != '$login' ";
	}
	
	$sqlListUserToAdd .= "order by nom, prenom";
}


$listeUser[] = null;
$res = grr_sql_query($sqlListUserToAdd);
$nb_users = grr_sql_count($res);
if ($res) for ($i = 0; ($row = grr_sql_row($res, $i)); $i++) {
	if (authUserAccesArea($row[0],$id_area) == 1) {
		$listeUser[] = $row;
	}
}


?>
<h3><?php echo get_vocab("add_user_to_list");?></h3>
<form  action="admin_right.php" method='post'>
	<div><SELECT size="1" name="reg_admin_login">
		<option value=''><?php echo get_vocab("nobody"); ?></option>
		<?php
		if ($listeUser) {
		   $listeUser = array_map("unserialize", array_unique(array_map("serialize", $listeUser)));
		}
		if ($listeUser) foreach ($listeUser as $row ) {
			echo "<option value=\"$row[0]\">".htmlspecialchars($row[1])." ".htmlspecialchars($row[2])." </option>";
		}
		?>
	</select>
	<input type="hidden" name="id_area" value="<?php echo $id_area;?>" />
	<input type="hidden" name="id_room" value="<?php echo $id_room;?>" />
	<input class="btn btn-primary btn-xs" type="submit" value="Enregistrer" />
</div></form>
</td></tr>


<!-- selection pour ajout de masse !-->
<?php
 if ($nb_users > 0) {
	?>
	<tr><td class="paddingLR5">
		<h3><?php echo get_vocab("add_multiple_user_to_list").get_vocab("deux_points");?></h3>
		<form action="admin_right.php" method='post'>
			<div><select name="agent" size="8" style="width:200px;" multiple="multiple" ondblclick="Deplacer(this.form.agent,this.form.elements['reg_multi_admin_login[]'])">
				<?php
				 if ($listeUser) foreach ($listeUser as $row ) {
						echo "<option value=\"$row[0]\">".htmlspecialchars($row[1])." ".htmlspecialchars($row[2])." </option>";
				}
				?>
			</select>
			<input class="btn btn-default btn-xs" type="button" value="&lt;&lt;" onclick="Deplacer(this.form.elements['reg_multi_admin_login[]'],this.form.agent)"/>
			<input class="btn btn-default btn-xs" type="button" value="&gt;&gt;" onclick="Deplacer(this.form.agent,this.form.elements['reg_multi_admin_login[]'])"/>
			<select name="reg_multi_admin_login[]" id="reg_multi_admin_login" size="8" style="width:200px;" multiple="multiple" ondblclick="Deplacer(this.form.elements['reg_multi_admin_login[]'],this.form.agent)">
				<option> </option>
			</select>
			<input type="hidden" name="id_area" value="<?php echo $id_area;?>" />
			<input type="hidden" name="room" value="<?php echo $id_room ;?>" />
			<input class="btn btn-primary btn-xs" type="submit" value="Enregistrer"  onclick="selectionner_liste(this.form.reg_multi_admin_login);"/></div>
			<script type="text/javascript">
				vider_liste(document.getElementById('reg_multi_admin_login'));
			</script> </form>
			<?php
			echo "</td></tr>";
		}
		echo "</table>";
// fin de l'affichage de la colonne de droite
		echo "</td></tr></table>";
		?>
	</body>
	</html>