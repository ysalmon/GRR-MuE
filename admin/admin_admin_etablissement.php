<?php
/**
 * admin_admin_etablissement.php
 * Interface de gestion des
 * administrateurs des etablissements de l'application GRR
 * Dernière modification : $Date: 2011-06-16 13:10:24 $
 * @author    Laurent Delineau <laurent.delineau@ac-poitiers.fr>
 * @copyright Copyright 2003-2008 Laurent Delineau
 * @link      http://www.gnu.org/licenses/licenses.html
 * @package   admin
 * @version   $Id: admin_admin_site.php,v 1.2 2011-06-16 13:10:24 grr Exp $
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
$grr_script_name = "admin_admin_etablissement.php";
$id = isset($_POST["id"]) ? $_POST["id"] : (isset($_GET["id"]) ? $_GET["id"] : NULL);
if (empty($id)) $id = get_default_etablissement();
if (!isset($id)) settype($id,"integer");


$back = '';
if (isset($_SERVER['HTTP_REFERER'])) $back = htmlspecialchars($_SERVER['HTTP_REFERER']);
if(authGetUserLevel(getUserName(),-1) < 6)
{
    $day   = date("d");
    $month = date("m");
    $year  = date("Y");
    showAccessDenied($day, $month, $year, '',$back);
    exit();
}
if ($settings->get("module_multietablissement") != "Oui")
{
    $day   = date("d");
    $month = date("m");
    $year  = date("Y");
    showAccessDenied($day, $month, $year, '',$back);
    exit();
}

# print the page header
//print_header("","","","",$type="with_session", $page="admin");
print_header('', '', '', $type = 'with_session');
// Affichage de la colonne de gauche
include_once "admin_col_gauche.php";

$reg_admin_login = isset($_POST["reg_admin_login"]) ? $_POST["reg_admin_login"] : NULL;
$reg_multi_admin_login = isset($_POST["reg_multi_admin_login"]) ? $_POST["reg_multi_admin_login"] : NULL;
$test_user =  isset($_POST["reg_multi_admin_login"]) ? "multi" : (isset($_POST["reg_admin_login"]) ? "simple" : NULL);

$action = isset($_GET["action"]) ? $_GET["action"] : NULL;
$msg='';


if ($test_user == "simple") {
    $res = grr_sql_query1("select login from ".TABLE_PREFIX."_j_useradmin_etablissement where (login = '$reg_admin_login' and id = '$id')");
    if ($res == -1) {
        $sql = "insert into ".TABLE_PREFIX."_j_useradmin_etablissement (login, id_etablissement) values ('$reg_admin_login',$id)";
        if (grr_sql_command($sql) < 0) {fatal_error(1, "<p>" . grr_sql_error());}  else {$msg=get_vocab("add_user_succeed");}
    }
}

if ($test_user == "multi") {
  foreach ($reg_multi_admin_login as $valeur){
    $res = grr_sql_query1("select login from ".TABLE_PREFIX."_j_useradmin_etablissement where (login = '$valeur' and id_etablissement = '$id')");
    if ($res == -1) {
        $sql = "insert into ".TABLE_PREFIX."_j_useradmin_etablissement (login, id_etablissement) values ('$valeur',$id)";
        if (grr_sql_command($sql) < 0) {fatal_error(1, "<p>" . grr_sql_error());}  else {$msg=get_vocab("add_multi_user_succeed");}
    }
  }
}

if ($action) {
    if ($action == "del_admin") {
        unset($login_admin); $login_admin = $_GET["login_admin"];
        $sql = "DELETE FROM ".TABLE_PREFIX."_j_useradmin_etablissement WHERE (login='$login_admin' and id_etablissement = '$id')";
        if (grr_sql_command($sql) < 0) {fatal_error(1, "<p>" . grr_sql_error());} else {$msg=get_vocab("del_user_succeed");}
    }
}


echo "<h2>".get_vocab('admin_admin_etablissement.php')."</h2>";
echo "<p><i>".get_vocab("admin_admin_etablissement_explain")."</i></p>";

// Affichage d'un pop-up
affiche_pop_up($msg,"admin");

echo "<table><tr>";
$this_etab_name = "";
# liste des établissements
echo "<td ><p><b>".get_vocab("etablissements").get_vocab("deux_points")."</b></p>\n";
$out_html = "<form id=\"etablissement\" action=\"admin_admin_etablissement.php\" method=\"post\">\n<div><select name=\"id\" onchange=\"go_to_etablissement()\">\n";
$out_html .= "<option value=\"admin_admin_etablissement.php?id=-1\">".get_vocab('select')."</option>";

$sql = "select id, shortname from ".TABLE_PREFIX."_etablissement order by shortname";
$res = grr_sql_query($sql);
if ($res) for ($i = 0; ($row = grr_sql_row($res, $i)); $i++)
{
    $selected = ($row[0] == $id) ? "selected=\"selected\"" : "";
    $link = "admin_admin_etablissement.php?id=$row[0]";
    $out_html .= "<option $selected value=\"$link\">" . htmlspecialchars($row[1])."</option>";
}
$out_html .= "</select>

<script type=\"text/javascript\" >
<!--
function go_to_etablissement()
{
box = document.getElementById(\"etablissement\").id;
destination = box.options[box.selectedIndex].value;
if (destination) location.href = destination;
}
// -->
</script>
</div>
<noscript>
<div><input type=\"submit\" value=\"Change\" /></div>
</noscript>
</form>";
echo $out_html;
$this_etab_name = grr_sql_query1("select name from ".TABLE_PREFIX."_etablissement where id=$id");
echo "</td>\n";
echo "</tr></table>\n";

# Ne pas continuer si aucun établissement n'est défini
if ($id <= 0)
{
    echo "<h1>".get_vocab("no_etab")."</h1>";
    // fin de l'affichage de la colonne de droite
    echo "</td></tr></table></body></html>";
    exit;
}

echo "<table border=\"1\" cellpadding=\"5\"><tr><td class='paddingLR5'>";
$is_admin='yes';
$exist_admin='no';


$sql = "select login, nom, prenom from ".TABLE_PREFIX."_utilisateurs where (statut='utilisateur' or statut='gestionnaire_utilisateur')";
$res = grr_sql_query($sql);
if ($res) for ($i = 0; ($row = grr_sql_row($res, $i)); $i++)
{
    $is_admin='yes';
    $sql3 = "SELECT login FROM ".TABLE_PREFIX."_j_useradmin_etablissement WHERE (id_etablissement='".$id."' and login='".$row[0]."')";
    $res3 = grr_sql_query($sql3);
    $nombre = grr_sql_count($res3);
    if ($nombre==0) $is_admin='no';

    if ($is_admin=='yes') {
        if ($exist_admin=='no') {
            echo "<h3>".get_vocab("user_admin_etablissement_list").get_vocab("deux_points")."</h3>";
            $exist_admin='yes';
        }
        echo "<b>";
        echo htmlspecialchars($row[1])." ".htmlspecialchars($row[2])."</b> | <a href='admin_admin_etablissement.php?action=del_admin&amp;login_admin=".urlencode($row[0])."&amp;id=$id'>".get_vocab("delete")."</a><br />";
    }
}
if ($exist_admin=='no') {
    echo "<h3><span class=\"avertissement\">".get_vocab("no_admin_this_etablissement")."</span></h3>";
}

//Recherche de la liste des utilisateurs qu'il est possible d'ajouter
$sql = "SELECT U.login, U.nom, U.prenom FROM ".TABLE_PREFIX."_utilisateurs AS U
			INNER JOIN ".TABLE_PREFIX."_j_user_etablissement AS UE ON UE.login = U.login  WHERE  
			UE.id_etablissement = $id AND
			(U.etat!='inactif' and (U.statut='utilisateur' or U.statut='gestionnaire_utilisateur')) 
			AND U.login NOT IN (SELECT login FROM ".TABLE_PREFIX."_j_useradmin_etablissement WHERE id_etablissement = '$id') 
			order by U.nom, U.prenom";
$res = grr_sql_query($sql);
$nb_users = grr_sql_count($res);
$listeUser[] = null;
if ($res) for ($i = 0; ($row = grr_sql_row($res, $i)); $i++) {
	$listeUser[] = $row;
}


?>
<h3><?php echo get_vocab("add_user_to_list");?></h3>
<form action="admin_admin_etablissement.php" method='post'>
<div><select size="1" name="reg_admin_login">
<option value=''><?php echo get_vocab("nobody"); ?></option>
<?php
if ($listeUser) foreach ($listeUser as $row ) {
	echo "<option value='$row[0]'>".htmlspecialchars($row[1])." ".htmlspecialchars($row[2])."</option>";
}
?>
</select>
<input type="hidden" name="id" value="<?php echo $id;?>" />
<input class="btn btn-primary btn-xs" type="submit" value="Enregistrer" />
</div></form>
</td></tr>

<!-- selection pour ajout de masse !-->
  <?php	
  	if ($nb_users > 0) {
    ?>
    <tr><td class="paddingLR5">
   	<h3><?php echo get_vocab("add_multiple_user_to_list").get_vocab("deux_points");?></h3>

    <form action="admin_admin_etablissement.php" method='post'>
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
	<input type="hidden" name="id" value="<?php echo $id;?>" />
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
