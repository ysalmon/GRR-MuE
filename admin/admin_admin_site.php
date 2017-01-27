<?php
/**
 * admin_admin_site.php
 * Interface de gestion des
 * administrateurs de sites de l'application GRR
 * Dernière modification : $Date: 2009-04-14 12:59:16 $
 * @author    Laurent Delineau <laurent.delineau@ac-poitiers.fr>
 * @copyright Copyright 2003-2008 Laurent Delineau
 * @link      http://www.gnu.org/licenses/licenses.html
 * @package   admin
 * @version   $Id: admin_admin_site.php,v 1.7 2009-04-14 12:59:16 grr Exp $
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
$grr_script_name = "admin_admin_site.php";
$id_site = isset($_POST["id_site"]) ? $_POST["id_site"] : (isset($_GET["id_site"]) ? $_GET["id_site"] : NULL);

/*if (empty($id_site))
	$id_site = get_default_site();*/

if (!isset($id_site))
	settype($id_site, "integer");
$back = '';

if (isset($_SERVER['HTTP_REFERER']))
	$back = htmlspecialchars($_SERVER['HTTP_REFERER']);

check_access(6, $back);

if (Settings::get("module_multisite") != "Oui"){
	
	showAccessDenied($back);
	exit();
}

# print the page header
print_header("", "", "", $type="with_session");

// Affichage de la colonne de gauche
include "admin_col_gauche.php";

$reg_admin_login = isset($_GET["reg_admin_login"]) ? $_GET["reg_admin_login"] : NULL;
$action = isset($_GET["action"]) ? $_GET["action"] : NULL;
$msg = '';

if ($reg_admin_login)
{
	$res = grr_sql_query1("select login from ".TABLE_PREFIX."_j_useradmin_site where (login = '$reg_admin_login' and id_site = '$id_site')");
	if ($res == -1)
	{
		$sql = "insert into ".TABLE_PREFIX."_j_useradmin_site (login, id_site) values ('$reg_admin_login',$id_site)";
		if (grr_sql_command($sql) < 0)
			fatal_error(1, "<p>" . grr_sql_error());
		else
			$msg = get_vocab("add_user_succeed");
	}
}

if ($action)
{
	if ($action == "del_admin")
	{
		unset($login_admin);
		$login_admin = $_GET["login_admin"];
		$sql = "DELETE FROM ".TABLE_PREFIX."_j_useradmin_site WHERE (login='$login_admin' and id_site = '$id_site')";
		if (grr_sql_command($sql) < 0)
			fatal_error(1, "<p>" . grr_sql_error());
		else
			$msg = get_vocab("del_user_succeed");
	}
}

echo "<h2>".get_vocab('admin_admin_site.php')."</h2>";
echo "<p><i>".get_vocab("admin_admin_site_explain")."</i></p>";

// Affichage d'un pop-up
affiche_pop_up($msg,"admin");

echo "<table><tr>";
$this_site_name = "";

# liste des sites
echo "<td ><p><b>".get_vocab("sites").get_vocab("deux_points")."</b></p>\n";
$out_html = "<form id=\"site\" action=\"admin_admin_site.php\" method=\"post\">\n<div><select name=\"id_site\" onchange=\"site_go()\">\n";
$out_html .= "<option value=\"admin_admin_site.php?id_site=-1\">".get_vocab('select')."</option>";

if (Settings::get("module_multietablissement") == "Oui"){
	$id_etablissement = getIdEtablissementCourant();
	$sql = "select id, sitename from ".TABLE_PREFIX."_site JOIN ".TABLE_PREFIX."_j_etablissement_site ON id_site = id WHERE id_etablissement =  $id_etablissement order by sitename";
} else {
	$sql = "select id, sitename from ".TABLE_PREFIX."_site order by sitename";
}

$res = grr_sql_query($sql);
if ($res)
	for ($i = 0; ($row = grr_sql_row($res, $i)); $i++)
	{
		$selected = ($row[0] == $id_site) ? "selected=\"selected\"" : "";
		$link = "admin_admin_site.php?id_site=$row[0]";
		$out_html .= "<option $selected value=\"$link\">" . htmlspecialchars($row[1])."</option>";
	}
	$out_html .= "</select>
	<script type=\"text/javascript\" >
		<!--
		function site_go()
		{
			box = document.getElementById(\"site\").id_site;
			destination = box.options[box.selectedIndex].value;
			if (destination) location.href = destination;
		}
	-->
</script>
</div>
<noscript>
	<div><input type=\"submit\" value=\"Change\" /></div>
</noscript>
</form>";
echo $out_html;
$this_site_name = grr_sql_query1("select sitename from ".TABLE_PREFIX."_site where id=$id_site");
echo "</td>\n";
echo "</tr></table>\n";
# Ne pas continuer si aucun site n'est défini
if ($id_site <= 0)
{
	echo "<h1>".get_vocab("no_site")."</h1>";
	// fin de l'affichage de la colonne de droite
	echo "</td></tr></table></body></html>";
	exit;
}

echo "<table border=\"1\" cellpadding=\"5\"><tr><td>";
$is_admin = 'yes';
echo "<h3>".get_vocab("administration_site").get_vocab("deux_points")."</h3>";
echo "<b>".$this_site_name."</b>";

?>
</td>
<td  class="paddingLR5">
	<?php
	$exist_admin = 'no';
	
	if (Settings::get("module_multietablissement") == "Oui"  ){
		$idEtablissement = getIdEtablissementCourant();
		$sql = "select U.login, U.nom, U.prenom 
				from ".TABLE_PREFIX."_utilisateurs AS U 
				JOIN  ".TABLE_PREFIX."_j_user_etablissement AS UE ON U.login = UE.login
				where (statut='utilisateur' or statut='gestionnaire_utilisateur')
				AND UE.id_etablissement = $idEtablissement";
	} else {
		$sql = "select login, nom, prenom from ".TABLE_PREFIX."_utilisateurs where (statut='utilisateur' or statut='gestionnaire_utilisateur')";
	}

	$res = grr_sql_query($sql);
	if ($res)
		for ($i = 0; ($row = grr_sql_row($res, $i)); $i++)
		{
			$is_admin = 'yes';
			$sql3 = "SELECT login FROM ".TABLE_PREFIX."_j_useradmin_site WHERE (id_site='".$id_site."' and login='".$row[0]."')";
			$res3 = grr_sql_query($sql3);
			$nombre = grr_sql_count($res3);
			if ($nombre == 0)
				$is_admin = 'no';
			if ($is_admin == 'yes')
			{
				if ($exist_admin == 'no')
				{
					echo "<h3>".get_vocab("user_admin_site_list").get_vocab("deux_points")."</h3>";
					$exist_admin='yes';
				}
				echo "<b>";
				echo "$row[1] $row[2]</b> | <a href='admin_admin_site.php?action=del_admin&amp;login_admin=".urlencode($row[0])."&amp;id_site=$id_site'>".get_vocab("delete")."</a><br />";
			}
		}
		if ($exist_admin=='no')
			echo "<h3><span class=\"avertissement\">".get_vocab("no_admin_this_site")."</span></h3>";
		
		
		//Recherche de la liste des utilisateurs qu'il est possible d'ajouter
		$listeUser[] = null;
		if (Settings::get("module_multietablissement") == "Oui"  ){
			$id_etablissement = getIdEtablissementCourant();
			$sql = "SELECT U.login, U.nom, U.prenom FROM ".TABLE_PREFIX."_utilisateurs AS U ".
					"JOIN ".TABLE_PREFIX."_j_user_etablissement AS UE ON UE.login = U.login ".
					"WHERE UE.id_etablissement = $id_etablissement AND (U.etat!='inactif' and (U.statut='utilisateur' or U.statut='gestionnaire_utilisateur')) ".
					" AND U.login NOT IN (SELECT login FROM ".TABLE_PREFIX."_j_useradmin_site WHERE id_site = '$id_site') ".
					"order by U.nom, U.prenom";
		} else {
			$sql = "SELECT login, nom, prenom FROM ".TABLE_PREFIX."_utilisateurs WHERE  (etat!='inactif' and (statut='utilisateur' or statut='gestionnaire_utilisateur')) AND login NOT IN (SELECT login FROM ".TABLE_PREFIX."_j_useradmin_site WHERE id_site = '$id_site') order by nom, prenom";
		}
		$res = grr_sql_query($sql);
		$nb_users = grr_sql_count($res);
		if ($res) for ($i = 0; ($row = grr_sql_row($res, $i)); $i++) {
			$listeUser[] = $row;
		}

		?>
		<h3>
			<?php echo get_vocab("add_user_to_list"); ?>
		</h3>
		<form action="admin_admin_site.php" method='get'>
			<div><select size="1" name="reg_admin_login">
				<option value=''><?php echo get_vocab("nobody"); ?></option>
				<?php
					if ($listeUser) foreach ($listeUser as $row ) {
						echo "<option value='$row[0]'>".htmlspecialchars($row[1])." ".htmlspecialchars($row[2])."</option>";
					}
				?>
				</select>
				<input type="hidden" name="id_site" value="<?php echo $id_site; ?>" />
				<input class="btn btn-primary btn-xs" type="submit" value="Enregistrer" />
			</div></form>
		</td></tr>
		
	<!-- selection pour ajout de masse !-->
	<?php	
		if ($nb_users > 0) {
	?>
    <tr><td></td><td class="paddingLR5">
   	<h3><?php echo get_vocab("add_multiple_user_to_list").get_vocab("deux_points");?></h3>

    <form action="admin_admin_site.php" method='post'>
	  <div><select name="agent" size="8" style="width:200px;" multiple="multiple" ondblclick="Deplacer(this.form.agent,this.form.elements['reg_multi_admin_login[]'])">

    <?php
		if ($listeUser) foreach ($listeUser as $row ) {
			echo "<option value=\"$row[0]\">".htmlspecialchars($row[1])." ".htmlspecialchars($row[2])." </option>\n";
		}
    ?>

	</select>
	<input class="btn btn-default btn-xs" type="button" value="&lt;&lt;" onclick="Deplacer(this.form.elements['reg_multi_admin_login[]'],this.form.agent)"/>
	<input class="btn btn-default btn-xs" type="button" value="&gt;&gt;" onclick="Deplacer(this.form.agent,this.form.elements['reg_multi_admin_login[]'])"/>
	<select name="reg_multi_admin_login[]" id="reg_multi_admin_login" size="8" style="width:200px;" multiple="multiple" ondblclick="Deplacer(this.form.elements['reg_multi_admin_login[]'],this.form.agent)">
  <option>&nbsp;</option>
  </select>
	<input type="hidden" name="id_site" value="<?php echo $id_site;?>" />
    <input class="btn btn-primary btn-xs" type="submit" value="Enregistrer"  onclick="selectionner_liste(this.form.reg_multi_admin_login);"/></div>

    <script type="text/javascript">
    vider_liste(document.getElementById('reg_multi_admin_login'));
    </script> </form>
    <?php
    echo "</td></tr>";
    }
    echo "</table>";
?>
</td></tr>
		
		</table>

	</body>
	</html>