<?php
/**
 * moderation
 * Interface de moderation des reservations lié aux domaines et ressources
 * Ce script fait partie de l'application GRR
 * Dernière modification : $Date: 2009-02-27 13:28:19 $
 * @author    Théo Beaudenon <theo.beaudenon@gfi.fr>
 * @copyright Copyright 2003-2008 Laurent Delineau
 * @link      http://www.gnu.org/licenses/licenses.html
 * @package   root
 * @version   $Id: moderation.php,v 1.4 20017-08-24 13:28:19 grr Exp $
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
include "include/connect.inc.php";
include "include/config.inc.php";
include "include/misc.inc.php";
include "include/functions.inc.php";
include "include/$dbsys.inc.php";
include "include/mincals.inc.php";
include "include/mrbs_sql.inc.php";
$grr_script_name = "moderation.php";
require_once("./include/settings.class.php");
$settings = new Settings();
if (!$settings)
    die("Erreur chargement settings");
require_once("./include/session.inc.php");
include "include/resume_session.php";
include "include/language.inc.php";
include "include/setdate.php";

$back = '';
if (isset($_SERVER['HTTP_REFERER']))
	$back = htmlspecialchars($_SERVER['HTTP_REFERER']);


print_header("", "", "", $type="with_session");
//include "admin/admin_col_gauche.php";
echo '<div class="col-md-12 col-md-12 col-xs-12 coldroite_admin blocBlanc colPadding">';
$out = demandeModeration($user);
if(count($out) == 0){
    echo '<h2>Aucune demande de moderation</h2>';
}
else{
    foreach ($out as $rowSite){
        echo "<form action=\"view_entry.php\" method=\"get\">\n";
        echo "<input type=\"hidden\" name=\"action_moderate\" value=\"y\" />\n";
        echo "<input type=\"hidden\" name=\"id\" value=\"".$rowSite[4]."\" />\n";
        echo "<input type=\"hidden\" name=\"page\" value=\"moderation\" />\n";
        echo "<fieldset><legend style=\"font-weight:bold\">".get_vocab("moderate_entry")." : <a title=\"Vor le detail de la réservation\" class=\"lienModal\" onclick=\"requestModal(". $rowSite[4].",".date('j,m,Y').",'day',readDataModal);\" data-toggle=\"modal\" data-target=\"#myModal\" style=\"height: 60px; padding-top: 0px;\">".$rowSite[1]."</a></legend>\n";
        echo "<p>";
        echo "<input type=\"radio\" name=\"moderate\" value=\"1\" checked=\"checked\" />".get_vocab("accepter_resa");
        echo "<br /><input type=\"radio\" name=\"moderate\" value=\"0\" />".get_vocab("refuser_resa");
        if ($rowSite[5])
        {
            echo "<br /><input type=\"radio\" name=\"moderate\" value=\"S1\" />".get_vocab("accepter_resa_serie");
            echo "<br /><input type=\"radio\" name=\"moderate\" value=\"S0\" />".get_vocab("refuser_resa_serie");
        }
        echo "</p><p>";
        echo "<label for=\"description\">".get_vocab("justifier_decision_moderation").get_vocab("deux_points")."</label>\n";
        echo "<textarea class=\"form-control\" name=\"description\" id=\"description\" cols=\"40\" rows=\"3\"></textarea>";
        echo "</p>";
        echo "<div style=\"text-align:center;\"><input class=\"btn btn-primary\" type=\"submit\" name=\"commit\" value=\"".get_vocab("save")."\" /></div>\n";
        echo "</fieldset></form>\n";
     }
}

?>
</div>
</div>
<?php
echo '<div id="popup_name" class="popup_block col-xs-12" ></div>'.PHP_EOL;

//modal bootstrap
echo '
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div id="modalBody" class="modal-body">
				<!-- insertion de la page view-entry.php via la fonction requestModal du fichier js/popup.js -->
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Fermer</button>
			</div>
		</div>
	</div>
</div>
';
?>
</body>
</html>
