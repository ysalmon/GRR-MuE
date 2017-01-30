<?php
/**
 * admin_access_etablissement.php
 * Interface de gestion des accès aux établissements.
 * Ce script fait partie de l'application GRR
 * Dernière modification : $Date: 2011-05-17 15:05:45 $
 * @author    Laurent Delineau <laurent.delineau@ac-poitiers.fr>
 * @copyright Copyright 2003-2008 Laurent Delineau
 * @link      http://www.gnu.org/licenses/licenses.html
 * @package   root
 * @version   $Id: admin_access_area.php,v 1.2 2011-05-17 15:05:45 grr Exp $
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
$grr_script_name = "admin_access_etablissement.php";

$id = isset($_POST["id"]) ? $_POST["id"] : (isset($_GET["id"]) ? $_GET["id"] : NULL);
if (!isset($id)) settype($id,"integer");
$reg_user_login = isset($_POST["reg_user_login"]) ? $_POST["reg_user_login"] : NULL;
$reg_multi_user_login = isset($_POST["reg_multi_user_login"]) ? $_POST["reg_multi_user_login"] : NULL;
$test_user =  isset($_POST["reg_multi_user_login"]) ? "multi" : (isset($_POST["reg_user_login"]) ? "simple" : NULL);
$action = isset($_GET["action"]) ? $_GET["action"] : NULL;
$msg='';

$back = '';
if (isset($_SERVER['HTTP_REFERER'])) $back = htmlspecialchars($_SERVER['HTTP_REFERER']);
$day   = date("d");
$month = date("m");
$year  = date("Y");

if(authGetUserLevel(getUserName(),-1,'area') < 6)
{
    showAccessDenied($day, $month, $year, '',$back);
    exit();
}

# print the page header
//print_header("","","","",$type="with_session", $page="admin");
print_header('', '', '', $type = 'with_session');
// Affichage de la colonne de gauche
include_once "admin_col_gauche.php";

// Si la table j_user_etablissement est vide, il faut modifier la requête
$test_grr_j_user_etablissement = grr_sql_count(grr_sql_query("SELECT * from ".TABLE_PREFIX."_j_user_etablissement"));

if ($test_user == "multi") {

  foreach ($reg_multi_user_login as $valeur){
    // On commence par vérifier que le professeur n'est pas déjà présent dans cette liste.
  	if ($id !=-1) {
  		if(authGetUserLevel(getUserName(),$id,'etablissement') < 6)
  		{
  			showAccessDenied($day, $month, $year, '',$back);
  			exit();
  		}
  		$sql = "SELECT * FROM ".TABLE_PREFIX."_j_user_etablissement WHERE (login = '".$valeur."' and id_etablissement = $id)";
  		$res = grr_sql_query($sql);
  		$test = grr_sql_count($res);
  		if ($test > 0) {
  			$msg = get_vocab("warning_exist");
  		} else {
  			if ($valeur != '') {
  				$sql = "INSERT INTO ".TABLE_PREFIX."_j_user_etablissement SET login= '$valeur', id_etablissement = $id";
  				if (grr_sql_command($sql) < 0) {
  					fatal_error(1, "<p>" . grr_sql_error());
  				}  else {$msg= get_vocab("add_multi_user_succeed");
  				}
  			}
  		}
  	}
  }
}


if ($test_user == "simple") {
   // On commence par vérifier que le professeur n'est pas déjà présent dans cette liste.
    if ($id !=-1) {
        if(authGetUserLevel(getUserName(),$id,'etablissement') < 6)
        {
            showAccessDenied($day, $month, $year, '',$back);
            exit();
        }
        $sql = "SELECT * FROM ".TABLE_PREFIX."_j_user_etablissement WHERE (login = '$reg_user_login' and id_etablissement = '$id')";
        $res = grr_sql_query($sql);
        $test = grr_sql_count($res);
        if ($test > 0) {
            $msg = get_vocab("warning_exist");
        } else {
            if ($reg_user_login != '') {
                $sql = "INSERT INTO ".TABLE_PREFIX."_j_user_etablissement SET login= '$reg_user_login', id_etablissement = '$id'";
                if (grr_sql_command($sql) < 0) {fatal_error(1, "<p>" . grr_sql_error());}  else {$msg= get_vocab("add_user_succeed");}
            }
        }
    }
}

if ($action=='del_user') {
    if(authGetUserLevel(getUserName(),$id,'etablissement') < 6)
    {
        showAccessDenied($day, $month, $year, '',$back);
        exit();
    }
    unset($login_user); $login_user = $_GET["login_user"];
    $sql = "DELETE FROM ".TABLE_PREFIX."_j_user_etablissement WHERE (login='$login_user' and id_etablissement = '$id')";
    if (grr_sql_command($sql) < 0) {fatal_error(1, "<p>" . grr_sql_error());} else {$msg=get_vocab("del_user_succeed");}
}
if (empty($id)) $id = -1;
echo "<h2>".get_vocab('admin_access_etablissement.php')./*grr_help("aide_grr_domaine_restreint").*/"</h2>\n";
affiche_pop_up($msg,"admin");

echo "<table><tr>\n";
$this_etablissement_name = "";
# Show all areas
$existe_etablissement = 'no';
echo "<td ><p><b>".get_vocab('etablissements')."</b></p>\n";
$out_html = "\n<form id=\"etablissement\" action=\"admin_access_etablissement.php\" method=\"post\">\n<div><select name=\"etablissement\" onchange=\"etablissement_go()\">";
$out_html .= "\n<option value=\"admin_access_etablissement.php?id=-1\">".get_vocab('select')."</option>";
    $sql = "select id, shortname from ".TABLE_PREFIX."_etablissement order by shortname";
    $res = grr_sql_query($sql);
    $nb = grr_sql_count($res);
    if ($res) for ($i = 0; ($row = grr_sql_row($res, $i)); $i++)
    {
        $selected = ($row[0] == $id) ? "selected = \"selected\"" : "";
        $link = "admin_access_etablissement.php?id=$row[0]";
        // on affiche que les domaines que l'utilisateur connecté a le droit d'administrer
        if(authGetUserLevel(getUserName(),$row[0],'etab') >= 6) {
            $out_html .= "\n<option $selected value=\"$link\">" . htmlspecialchars($row[1])."</option>";
            $existe_etablissement = 'yes';
        }
    }
    $out_html .= "</select></div>
    <script  type=\"text/javascript\" >
    <!--
    function etablissement_go()
    {
    box = document.getElementById('etablissement').etablissement;
    destination = box.options[box.selectedIndex].value;
    if (destination) location.href = destination;
    }
    // -->
    </script>
    <noscript>
    <div><input type=\"submit\" value=\"Change\" /></div>
    </noscript>
    </form>";

if ($existe_etablissement == 'yes') echo $out_html;

$this_etablissement_name = grr_sql_query1("select shortname from ".TABLE_PREFIX."_etablissement where id=$id");
echo "</td>\n";
echo "</tr></table>\n";

# Show établissement :
if ($id != -1) {
    echo "<table border=\"1\" cellpadding=\"5\"><tr><td class='paddingLR5'>";
    $sql = "SELECT u.login, u.nom, u.prenom FROM ".TABLE_PREFIX."_utilisateurs u, ".TABLE_PREFIX."_j_user_etablissement j WHERE (j.id_etablissement='$id' and u.login=j.login)  order by u.nom, u.prenom";
    $res = grr_sql_query($sql);
    $nombre = grr_sql_count($res);
    if ($nombre!=0) echo "<h3>".get_vocab("user_etablissement_list")."</h3>\n";
    if ($res) for ($i = 0; ($row = grr_sql_row($res, $i)); $i++)
        {
        $login_user = $row[0];
        $nom_admin = htmlspecialchars($row[1]);
        $prenom_admin = htmlspecialchars($row[2]);
        echo "<b>";
        echo "$nom_admin $prenom_admin</b> | <a href='admin_access_etablissement.php?action=del_user&amp;login_user=".urlencode($login_user)."&amp;id=$id'>".get_vocab("delete")."</a><br />\n";
    }
    if ($nombre == 0) {
        echo "<h3 class='avertissement'>".get_vocab("no_user_etablissement")."</h3>\n";
    }
    ?>
    <h3><?php echo get_vocab("add_user_to_list");?></h3>
    <form action="admin_access_etablissement.php" method='post'>
    <div><select size="1" name="reg_user_login">
    <option value=''><?php echo get_vocab("nobody"); ?></option>
    <?php
    
    $sqlListUserToAdd = "SELECT U.login, U.nom, U.prenom FROM ".TABLE_PREFIX."_utilisateurs AS U ";
    $sqlListUserToAdd .= "WHERE ( U.etat!='inactif' and (U.statut='utilisateur' or U.statut='visiteur' or U.statut='gestionnaire_utilisateur')) ";
    $sqlListUserToAdd .= "AND U.login NOT IN (SELECT login FROM ".TABLE_PREFIX."_j_user_etablissement WHERE id_etablissement = '$id') ";
    $sqlListUserToAdd .= "order by U.nom, U.prenom"; 
   
    
    $res = grr_sql_query($sqlListUserToAdd);
    if ($res) for ($i = 0; ($row = grr_sql_row($res, $i)); $i++) {
        echo "<option value=\"$row[0]\">".htmlspecialchars($row[1])." ".htmlspecialchars($row[2])." </option>\n";
    }
    ?>
    </select>
    
    <input type="hidden" name="id" value="<?php echo $id;?>" />
    <input class="btn btn-primary btn-xs" type="submit" value="Enregistrer" /></div>
    </form>
    </td></tr>
    <!-- selection pour ajout de masse !-->
    <?php
    $res = grr_sql_query($sqlListUserToAdd);
    $nb_users = grr_sql_count($res);
    if ($nb_users > 0) {
    ?>
    <tr><td class="paddingLR5">
   	<h3><?php echo get_vocab("add_multiple_user_to_list").get_vocab("deux_points");?></h3>

    <form action="admin_access_etablissement.php" method='post'>
	  <div><select name="agent" size="8" style="width:200px;" multiple="multiple" ondblclick="Deplacer(this.form.agent,this.form.elements['reg_multi_user_login[]'])">

    <?php
    if ($res) for ($i = 0; ($row = grr_sql_row($res, $i)); $i++) {
        echo "<option value=\"$row[0]\">".htmlspecialchars($row[1])." ".htmlspecialchars($row[2])." </option>\n";
    }
    ?>

	</select>
	<input class="btn btn-default btn-xs" type="button" value="&lt;&lt;" onclick="Deplacer(this.form.elements['reg_multi_user_login[]'],this.form.agent)"/>
	<input class="btn btn-default btn-xs" type="button" value="&gt;&gt;" onclick="Deplacer(this.form.agent,this.form.elements['reg_multi_user_login[]'])"/>
	<select name="reg_multi_user_login[]" id="reg_multi_user_login" size="8" style="width:200px;" multiple="multiple" ondblclick="Deplacer(this.form.elements['reg_multi_user_login[]'],this.form.agent)">
  <option>&nbsp;</option>
  </select>
    <input type="hidden" name="id" value="<?php echo $id;?>" />
    <input class="btn btn-primary btn-xs" type="submit" value="Enregistrer"  onclick="selectionner_liste(this.form.reg_multi_user_login);"/></div>

    <script type="text/javascript">
    vider_liste(document.getElementById('reg_multi_user_login'));
    </script> </form>


    <?php
    echo "</td></tr>";
    }
    echo "</table>";

} else {
    if (($nb =0) or ($existe_etablissement != 'yes')) {
        echo "<h3>".get_vocab("no_accessible_etablissement")."</h3>";
    } else {
        echo "<h3>".get_vocab("no_etablissement")."</h3>";
    }
}

echo "</td></tr>";
echo "</table>";
?>
</body>
</html>
