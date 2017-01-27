
<?php
/**
 * admin_confirm_change_date_bookings.php
 * interface de confirmation des changements de date de début et de fin de réservation
 * Ce script fait partie de l'application GRR
 * Dernière modification : $Date: 2009-06-04 15:30:17 $
 * @author    Laurent Delineau <laurent.delineau@ac-poitiers.fr>
 * @copyright Copyright 2003-2008 Laurent Delineau
 * @link      http://www.gnu.org/licenses/licenses.html
 * @package   root
 * @version   $Id: admin_confirm_change_date_bookings.php,v 1.8 2009-06-04 15:30:17 grr Exp $
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
$grr_script_name = "admin_confirm_change_date_bookings.php";

$back = '';
if (isset($_SERVER['HTTP_REFERER']))
	$back = htmlspecialchars($_SERVER['HTTP_REFERER']);
unset($display);
$display = isset($_GET["display"]) ? $_GET["display"] : NULL;
$day   = date("d");
$month = date("m");
$year  = date("Y");
check_access(6, $back);

if (isset($_GET['valid']) && ($_GET['valid'] == "yes")){
	
	if ($_GET['id_etablissement']) {
		if (!Settings::setEtab("begin_bookings", $_GET['begin_bookings'])){
			echo "Erreur lors de l'enregistrement de begin_bookings !<br />";
		}
		else
		{
			$sqlSelectEntry = "SELECT id FROM ".TABLE_PREFIX."_entry AS E ";
			$sqlSelectRepeat = "SELECT id FROM ".TABLE_PREFIX."_repeat AS E ";
			$sqlSelectEntryModerate = "SELECT id FROM ".TABLE_PREFIX."_entry_moderate AS E ";
			$sqlJoin = "JOIN ".TABLE_PREFIX."_room AS R on R.id = E.room_id
				JOIN ".TABLE_PREFIX."_j_site_area AS SA ON R.area_id = SA.id_area
				JOIN ".TABLE_PREFIX."_j_etablissement_site AS ES ON ES.id_site = SA.id_site 
				WHERE ES.id_etablissement = " . $_GET['id_etablissement'];
				
			$del = grr_sql_query("DELETE FROM ".TABLE_PREFIX."_entry WHERE (end_time < ".Settings::get('begin_bookings').") AND id IN (".$sqlSelectEntry.$sqlJoin.")");
			$del = grr_sql_query("DELETE FROM ".TABLE_PREFIX."_repeat WHERE end_date < ".Settings::get("begin_bookings").") AND id IN (".$sqlSelectRepeat.$sqlJoin.")");
			$del = grr_sql_query("DELETE FROM ".TABLE_PREFIX."_entry_moderate WHERE (end_time < ".Settings::get('begin_bookings').") AND id IN (".$sqlSelectEntryModerate.$sqlJoin.")");
			$del = grr_sql_query("DELETE FROM ".TABLE_PREFIX."_j_etablissement_calendar WHERE DAY < ".Settings::get("begin_bookings")."AND id_etablissement = " . $_GET['id_etablissement']);
		}
	}else{
		
		if (!Settings::set("end_bookings", $_GET['end_bookings']))
			echo "Erreur lors de l'enregistrement de end_bookings !<br />";
		else
		{
			$del = grr_sql_query("DELETE FROM ".TABLE_PREFIX."_entry WHERE start_time > ".Settings::get("end_bookings"));
			$del = grr_sql_query("DELETE FROM ".TABLE_PREFIX."_repeat WHERE start_time > ".Settings::get("end_bookings"));
			$del = grr_sql_query("DELETE FROM ".TABLE_PREFIX."_entry_moderate WHERE (start_time > ".Settings::get('end_bookings').")");
			$del = grr_sql_query("DELETE FROM ".TABLE_PREFIX."_calendar WHERE DAY > ".Settings::get("end_bookings"));
		}
	}
	
	if ($_GET['id_etablissement']) {
		if (!Settings::setEtab("end_bookings", $_GET['end_bookings'])) {
			echo "Erreur lors de l'enregistrement de end_bookings !<br />";
		} else {
			$del = grr_sql_query("DELETE FROM ".TABLE_PREFIX."_entry WHERE (start_time > ".Settings::get('end_bookings').") AND id IN (".$sqlSelectEntry.$sqlJoin.")");
			$del = grr_sql_query("DELETE FROM ".TABLE_PREFIX."_repeat WHERE ( start_time > ".Settings::get("end_bookings").") AND id IN (".$sqlSelectRepeat.$sqlJoin.")");
			$del = grr_sql_query("DELETE FROM ".TABLE_PREFIX."_entry_moderate WHERE (start_time > ".Settings::get('end_bookings').") AND id IN (".$sqlSelectEntryModerate.$sqlJoin.")");
			$del = grr_sql_query("DELETE FROM ".TABLE_PREFIX."_j_etablissement_calendar WHERE DAY > ".Settings::get("end_bookings") ."AND id_etablissement = " . $_GET['id_etablissement']);

		}
	} else {
		if (!Settings::set("end_bookings", $_GET['end_bookings'])) {
			echo "Erreur lors de l'enregistrement de end_bookings !<br />";
		} else {
			$del = grr_sql_query("DELETE FROM ".TABLE_PREFIX."_entry WHERE start_time > ".Settings::get("end_bookings",true));
			$del = grr_sql_query("DELETE FROM ".TABLE_PREFIX."_repeat WHERE start_time > ".Settings::get("end_bookings",true));
			$del = grr_sql_query("DELETE FROM ".TABLE_PREFIX."_entry_moderate WHERE (start_time > ".Settings::get('end_bookings',true).")");
			$del = grr_sql_query("DELETE FROM ".TABLE_PREFIX."_calendar WHERE DAY > ".Settings::get("end_bookings",true));
		}
	}
	
	header("Location: ./admin_config_etablissement.php");

}
else if (isset($_GET['valid']) && ($_GET['valid'] == "no")){
	header("Location: ./admin_config_etablissement.php");
}


if (isset($_GET['id_etablissement'])) {
	$idEtablissement = $_GET['id_etablissement'];
} else {
	$idEtablissement = 0;
}

# print the page header
print_header("", "", "", $type="with_session");

echo "<h2>".get_vocab('admin_confirm_change_date_bookings.php')."</h2>";
echo "<p>".get_vocab("msg_del_bookings")."</p>";
?>

<form action="admin_confirm_change_date_bookings.php" method='get'>
	<div>
		<input class="btn btn-primary" type="submit" value="<?php echo get_vocab("save");?>" />
		<input type="hidden" name="valid" value="yes" />
		<input type="hidden" name="id_etablissement" value=" <?php echo $idEtablissement; ?>" />
		<input type="hidden" name="begin_bookings" value=" <?php echo $_GET['begin_bookings']; ?>" />
		<input type="hidden" name="end_bookings" value=" <?php echo $_GET['end_bookings']; ?>" />
	</div>
</form>

<form action="admin_confirm_change_date_bookings.php" method='get'>
	<div>
		<input class="btn btn-primary" type="submit" value="<?php echo get_vocab("cancel");?>" />
		<input type="hidden" name="valid" value="no" />
	</div>
</form>
</body>
</html>
