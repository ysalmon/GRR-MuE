<?php
/**
 * admin_site.php
 * Interface d'accueil de Gestion des sites de l'application GRR
 * Dernière modification : $Date: 2009-06-04 15:30:17 $
 * @author    Laurent Delineau <laurent.delineau@ac-poitiers.fr>
 * @author    Marc-Henri PAMISEUX <marcori@users.sourceforge.net>
 * @copyright Copyright 2003-2008 Laurent Delineau
 * @copyright Copyright 2008 Marc-Henri PAMISEUX
 * @link      http://www.gnu.org/licenses/licenses.html
 * @package   admin
 * @version   $Id: admin_site.php,v 1.10 2009-06-04 15:30:17 grr Exp $
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
 * Compte le nombre de sites définis
 *
 * @return integer number of rows
 */
function count_sites()
{
	$sql = "SELECT COUNT(*)
	FROM ".TABLE_PREFIX."_site";
	$res = grr_sql_query($sql);
	if ($res)
	{
		$sites = grr_sql_row($res,0);
		if (is_array($sites))
			return $sites[0];
		else
		{
			echo '      <p>Une erreur est survenue pendant le comptage des sites.</p>';
			// fin de l'affichage de la colonne de droite
			echo "</td></tr></table>\n</body>\n</html>\n";
			die();
		}
	}
	else
	{
		echo '      <p>Une erreur est survenue pendant la préparation de la requète de comptage des sites.</p>';
		// fin de l'affichage de la colonne de droite
		echo "</td></tr></table>\n</body>\n</html>\n";
		die();
	}
}
function create_site($id_site)
{
	if ((isset($_POST['back']) || isset($_GET['back'])))
	{
		// On affiche le tableau des sites
		read_sites();
		exit();
	}
	// Initialisation des variables du formulaire
	if (!isset($id_site))
		$id_site = isset($_POST['id']) ? $_POST['id'] :  NULL;
	if (!isset($sitecode))
		$sitecode = isset($_POST['sitecode']) ? $_POST['sitecode'] : NULL;
	if (!isset($sitename))
		$sitename = isset($_POST['sitename']) ? $_POST['sitename'] :  NULL;
	if (!isset($adresse_ligne1))
		$adresse_ligne1 = isset($_POST['adresse_ligne1']) ? $_POST['adresse_ligne1'] :  NULL;
	if (!isset($adresse_ligne2))
		$adresse_ligne2 = isset($_POST['adresse_ligne2']) ? $_POST['adresse_ligne2'] :  NULL;
	if (!isset($adresse_ligne3))
		$adresse_ligne3 = isset($_POST['adresse_ligne3']) ? $_POST['adresse_ligne3'] :  NULL;
	if (!isset($cp))
		$cp = isset($_POST['cp']) ? $_POST['cp'] :  NULL;
	if (!isset($ville))
		$ville = isset($_POST['ville']) ? $_POST['ville'] :  NULL;
	if (!isset($pays))
		$pays = isset($_POST['pays']) ? $_POST['pays'] :  NULL;
	if (!isset($tel))
		$tel = isset($_POST['tel']) ? $_POST['tel'] :  NULL;
	if (!isset($fax))
		$fax = isset($_POST['fax']) ? $_POST['fax'] :  NULL;
	// On affiche le formulaire de saisie quand l'appel de la fonction ne provient pas de la validation de ce même formulaire
	if ((! (isset($_POST['save']) || isset($_GET['save']))) && ($id_site==0))
	{
		// Affichage des titres de la page
		echo '      <h2>'.get_vocab('addsite').'</h2>';
		echo '
		<form action="admin_site.php?action=create" method="post">
			<table>
				<tr><td>'.get_vocab('site_code').' *</td><td><input type="text" name="sitecode" value="'.$sitecode.'" size="10" title="'.get_vocab('site_code').'" /></td></tr>
				<tr><td>'.get_vocab('site_name').' *</td><td><input type="text" name="sitename" value="'.$sitename.'" size="50" title="'.get_vocab('site_name').'" /></td></tr>
				<tr><td>'.get_vocab('site_adresse_ligne1').'</td><td><input type="text" name="adresse_ligne1" value="'.$adresse_ligne1.'" size="38" title="'.get_vocab('site_adresse_ligne1').'" /></td></tr>
				<tr><td>'.get_vocab('site_adresse_ligne2').'</td><td><input type="text" name="adresse_ligne2" value="'.$adresse_ligne2.'" size="38" title="'.get_vocab('site_adresse_ligne2').'" /></td></tr>
				<tr><td>'.get_vocab('site_adresse_ligne3').'</td><td><input type="text" name="adresse_ligne3" value="'.$adresse_ligne3.'" size="38" title="'.get_vocab('site_adresse_ligne3').'" /></td></tr>
				<tr><td>'.get_vocab('site_cp').'</td><td><input type="text" name="cp" value="'.$cp.'" size="5" title="'.get_vocab('site_cp').'" /></td></tr>
				<tr><td>'.get_vocab('site_ville').'</td><td><input type="text" name="ville" value="'.$ville.'" size="50" title="'.get_vocab('site_ville').'" /></td></tr>
				<tr><td>'.get_vocab('site_pays').'</td><td><input type="text" name="pays" value="'.$pays.'" size="50" title="'.get_vocab('site_pays').'" /></td></tr>
				<tr><td>'.get_vocab('site_tel').'</td><td><input type="text" name="tel" value="'.$tel.'" size="25" title="'.get_vocab('site_tel').'" /></td></tr>
				<tr><td>'.get_vocab('site_fax').'</td><td><input type="text" name="fax" value="'.$fax.'" size="25" title="'.get_vocab('site_fax').'" /></td></tr>
			</table>
			<div>
				<input type="hidden" name="valid" value="yes" />
				<input type="submit" name="save" value="'.get_vocab('save').'" />
				<input type="submit" name="back" value="'.get_vocab('back').'" />
			</div>
		</form>';
		echo get_vocab("required");
		// Sinon, il faut valider le formulaire
	}
	else{
		
		// On vérifie que le code et le nom du site ont été renseignés
		if ($sitecode == '' || $sitecode == NULL || $sitename == '' || $sitename == NULL)
		{
			$_POST['save'] = 'no';
			$_GET['save'] = 'no';
			echo '<span class="avertissement">'.get_vocab('required').'</span>';
		}
		
		// Sauvegarde du record
		if ((isset($_POST['save']) && ($_POST['save'] != 'no')) || ((isset($_GET['save'])) && ($_GET['save'] != 'no'))){
			
			$sql="INSERT INTO ".TABLE_PREFIX."_site
			SET sitecode='".strtoupper(protect_data_sql($sitecode))."',
			sitename='".protect_data_sql($sitename)."',
			adresse_ligne1='".protect_data_sql($adresse_ligne1)."',
			adresse_ligne2='".protect_data_sql($adresse_ligne2)."',
			adresse_ligne3='".protect_data_sql($adresse_ligne3)."',
			cp='".protect_data_sql($cp)."',
			ville='".strtoupper(protect_data_sql($ville))."',
			pays='".strtoupper(protect_data_sql($pays))."',
			tel='".protect_data_sql($tel)."',
			fax='".protect_data_sql($fax)."'";
			if (grr_sql_command($sql) < 0)
				fatal_error(0,'<p>'.grr_sql_error().'</p>');
			
			$site = grr_sql_insert_id();
			
			
			if (Settings::get("module_multietablissement") == "Oui"){
				$id_etablissement = getIdEtablissementCourant();
				$sql="INSERT INTO ".TABLE_PREFIX."_j_etablissement_site 
				  SET id_etablissement = ".protect_data_sql($id_etablissement).", 
				  id_site = ".protect_data_sql($site) ;
				if (grr_sql_command($sql) < 0) {
					fatal_error(0,'<p>'.grr_sql_error().'</p>');
				}
			}

		}
		// On affiche le tableau des sites
		read_sites();
	}
}
function read_sites()
{
	// Affichage des titres de la page
	echo '      <h2>'.get_vocab('admin_site.php').'</h2>';
	echo '      <p>'.get_vocab('admin_site_explications').'</p>
	| <a href="admin_site.php?action=create&amp;id=0">'.get_vocab('display_add_site').'</a> | <a href="admin_site.php?action=shared">'.get_vocab('share_site').'</a> |';
	
	if (count_sites() > 0){
		
		//Prise en compte du multi-etablissement
		if (Settings::get("module_multietablissement") == "Oui"){
			$id_etablissement = getIdEtablissementCourant();
			$sql = "SELECT S.id,S.sitecode,S.sitename,S.cp,S.ville
					FROM ".TABLE_PREFIX."_site AS S JOIN ".TABLE_PREFIX."_j_etablissement_site AS J ON J.id_site = S.id
					WHERE J.id_etablissement = $id_etablissement  
					ORDER BY S.sitename,S.ville,S.id";
			
		} else {
			$sql = "SELECT id,sitecode,sitename,cp,ville
					FROM ".TABLE_PREFIX."_site
					ORDER BY sitename,ville,id";
		}
	
		$res = grr_sql_query($sql);
		if ($res){
			
			// Affichage de l'entête du tableau
			echo '<table class="table table-hover table-bordered">
			<thead>
				<tr>
					<th>'.get_vocab('action').get_vocab('deux_points').'</th>
					<th>'.get_vocab('site_code').'</th>
					<th>'.get_vocab('site_name').'</th>
					<th>'.get_vocab('site_cp').'</th>
					<th>'.get_vocab('site_ville').'</th>
				</tr>
			</thead>
			<tbody>';
			for ($i = 0; ($row=grr_sql_row($res,$i));$i++)
			{
				echo '        <tr>
				<td>
					<a href="admin_site.php?action=update&amp;id='.$row[0].'"><img class="image" title="'.get_vocab('change').'" alt="'.get_vocab('change').'" src="../img_grr/edit_s.png" /></a>
					<a href="admin_site.php?action=delete&amp;id='.$row[0].'"><img class="image" title="'.get_vocab('delete').'" alt="'.get_vocab('delete').'" src="../img_grr/delete_s.png" /></a>';
					//echo '            <a href="admin_site.php?action=right&amp;id='.$row[0].'"><img class=\"image\" title="'.get_vocab('privileges').'" alt="'.get_vocab('privileges').'" src="../img_grr/rights.png" /></a>';
					echo '          </td>
					<td>'.$row[1].'</td>
					<td>'.$row[2].'</td>
					<td>'.$row[3].'</td>
					<td>'.$row[4].'</td>
				</tr>';
			}
			echo '      </tbody></table>';
		}
		else
		{
			echo '      <p>Une erreur est survenue pendant la préparation de la requète de lecture des sites.</p>';
			// fin de l'affichage de la colonne de droite
			echo "</td></tr></table>\n</body>\n</html>\n";
			die();
		}
		// fin de l'affichage de la colonne de droite
		echo "</td></tr></table>\n</body>\n</html>\n";
		die();
	}
}


///////////////////////////////////////////////////
// GIP RECIA - Ajout de 3 fonctions pour permettre le partage d'un site entre plusieurs établissments :
// => shared_sites, share_site, delete_share
///////////////////////////////////////////////////

// Fonction shared_sites : permet l'affichage des partages de sites réalisés
 function shared_sites()
 {
   // Affichage des titres de la page
   echo '      <h2>'.get_vocab('shared_sites').'</h2>';
   echo '      <p>'.get_vocab('msg_shared_sites').'</p>
      | <a href="admin_site.php?action=share">'.get_vocab('share_a_site').'</a> |';


   	//Prise en compte du multi-etablissement
   	if (Settings::get("module_multietablissement") == "Oui"){
   		$id_etablissement = getIdEtablissementCourant();
   		$sql = "SELECT S.id,S.sitecode,S.sitename,S.cp,S.ville
   				FROM ".TABLE_PREFIX."_site AS S JOIN ".TABLE_PREFIX."_j_etablissement_site AS J ON J.id_site = S.id
   				WHERE J.id_etablissement = $id_etablissement  
   		 		ORDER BY S.sitename,S.ville,S.id";
   		
   	} else {
   		echo get_vocab('be_in_multi_etab');
		die();
   	}

   $res = grr_sql_query($sql);
   if ($res && grr_sql_count($res)> 0)
   {
     // Affichage de l'entête du tableau
     echo '      <table border="1" cellpadding="3">
        <tr>
          <th>'.get_vocab('action').get_vocab('deux_points').'</th>
          <th>'.get_vocab('site_code_shared').'</th>
          <th>'.get_vocab('site_name_shared').'</th>
          <th>'.get_vocab('short_name_etab').'</th>
          <th>'.get_vocab('long_name_etab').'</th>
        </tr>';
     // Affichage des sites déjà partagés
     for ($i = 0; ($row=grr_sql_row($res,$i));$i++)
     {
       $sql_shared = "SELECT S.id,S.sitecode,S.sitename,E.id,E.shortname,E.name
			FROM ".TABLE_PREFIX."_site S,".TABLE_PREFIX."_j_etablissement_site ES,".TABLE_PREFIX."_etablissement E
			WHERE ES.id_site = $row[0] AND ES.id_etablissement <> $id_etablissement AND ES.id_site = S.id AND ES.id_etablissement = E.id
			ORDER BY S.sitename,E.name";
	$res_shared=grr_sql_query($sql_shared);
	if ($res_shared && grr_sql_count($res_shared)> 0)
	{
	for ($y = 0; ($row_shared=grr_sql_row($res_shared,$y));$y++)
        {
       echo '        <tr>
          <td>';
// Non encore mis en place, il faut une méthode "update"
//       echo '     <a href="admin_site.php?action=share&amp;id='.$row_shared[0].'&amp;id_etab='.$row_shared[3].'"><img class="image" title="==Modifier le partage" alt="==Modifier le partage" src="../img_grr/edit_s.png" /></a>';
       echo '     <a href="admin_site.php?action=delshare&amp;id='.$row_shared[0].'&amp;id_etab='.$row_shared[3].'"><img class="image" title="'.get_vocab('delete_share').'" alt="'.get_vocab('delete_share').'" src="../img_grr/delete_s.png" /></a>
          </td>
          <td>'.$row_shared[1].'</td>
          <td>'.$row_shared[2].'</td>
          <td>'.$row_shared[4].'</td>
          <td>'.$row_shared[5].'</td>
        </tr>';
	}
	}
     }
     echo '      </table>';
   } 
   echo '<form action="admin_site.php?action=read" method="post">
         <input type="submit" name="back" value="'.get_vocab('back').'" />
         </form>';
    // fin de l'affichage de la colonne de droite
    echo "</td></tr></table>\n</body>\n</html>\n";
//   	die();
   
}


// Fonction share_site : permet de partager un site avec un nouvel établissement
// id_site : identifiant du site à partager
// id_etab : identifiant de l'établissement avec lequel partager le site
function share_site($id_site,$id_etab){
	
	if ((authGetUserLevel(getUserName(),-1,'area') >= 6) and (Settings::get("module_multietablissement") == "Oui") and (Settings::get("module_multisite") == "Oui")) {
		
		if ((isset($_POST['back']) or isset($_GET['back']))) {
			// On affiche le tableau des partages de sites
			shared_sites();
			exit();
		}
		
		// Initialisation des variables du formulaire
		if (!isset($id_site)) $id_site = isset($_POST['id_site']) ? $_POST['id_site'] :  NULL;
		if (!isset($id_etab)) $id_etab = isset($_POST['id_etab']) ? $_POST['id_etab'] :  NULL;

		$id_etablissement = getIdEtablissementCourant();
		
		// On affiche le formulaire de saisie quand l'appel de la fonction ne provient pas de la validation de ce même formulaire
		if (! (isset($_POST['save']) or isset($_GET['save']))) {
			// Affiche un comboBox avec la liste des sites de l'établissement courant;
			$sql = "SELECT S.id,S.sitename
			FROM ".TABLE_PREFIX."_site AS S JOIN ".TABLE_PREFIX."_j_etablissement_site AS J ON J.id_site = S.id
			WHERE J.id_etablissement = $id_etablissement  
			ORDER BY S.sitename,S.ville,S.id";
			$res = grr_sql_query($sql);
			$nb_site = grr_sql_count($res);
			//echo '<form action="admin_site.php?action=share&amp;id_site='.$id_site.'&amp;id_etab='.$id_etab.'" method="post">';
			echo '<form action="admin_site.php?action=share" method="post">';
			
			if ($nb_site > 1) {
				//En-tête et mise en garde
				echo '      <h2>'.get_vocab('share_with_etab').'</h2>';
				echo '      <p><span class="avertissement">'.get_vocab('msg_share_with_etab').'</span></p>';

				for ($i = 0; ($row = grr_sql_row($res, $i)); $i++){
					$listeSite[] = $row;
				}
				echo '<table border="1" width="100%" cellpadding="8" cellspacing="1">
						<tr>
							<th style="text-align:center;">
								<b>'.get_vocab('share_with_etab').'</b>
							</th>
						</tr>';
				echo '<tr><td>'.get_vocab('site_to_share').'&nbsp;*&nbsp;:&nbsp;<select name="id_site" size="1">
					<option value="-1"';
				if ($id_site == NULL){ echo " selected=\"selected\"" ; }
				echo ">".get_vocab('choose_a_site').'</option>';
				foreach($listeSite as $site){
					if ($id_site == $site[0]){
						echo "<option selected=\"selected\" value ='$site[0]'>$site[1]</option>";
					} else {
						echo "<option value ='$site[0]'>$site[1]</option>";
					}
				}
				echo '</select></td></tr>';

			} else {
				echo '<span class="avertissement">'.get_vocab('min_2_sites').'</span>';
			}  

			// choix de l'établissement
			$sql = "SELECT E.id, E.name FROM ".TABLE_PREFIX."_etablissement E WHERE E.id <> '".$id_etablissement."' ORDER BY E.name";
			$res = grr_sql_query($sql);
			if ($res) {
				for ($i = 0; ($row = grr_sql_row($res, $i)); $i++){
					$listeEtab[] = $row;
				}
			}

			echo '<tr><td>'.get_vocab('etab_to_share').'&nbsp;*&nbsp;:&nbsp;<select name="id_etab" size="1">
			<option value ="-1"';
			if ($id_etab == NULL){ echo " selected=\"selected\"" ; }
			echo ">".get_vocab('choose_etab')."</option>";
			foreach($listeEtab as $etab){
				if ($id_etab == $etab[0]){
					echo "<option selected=\"selected\" value ='$etab[0]'>$etab[1]</option>";
				} else {
					echo "<option value ='$etab[0]'>$etab[1]</option>";
				}
			}
			echo "</select></td></tr></table>";

			echo '<div><input type="submit" name="save" value="'.get_vocab('save').'" />
			<input type="submit" name="back" value="'.get_vocab('back').'" />
			</div></form>';
			// Sinon on valide le formulaire
		} else {
			$msg ='';
			if ($id_site=='' or $id_site=='-1' or $id_etab=='' or $id_etab=='-1'){
				$_POST['save'] = 'no';
				$_GET['save'] = 'no';
				echo '<span class="avertissement">'.get_vocab('required').'</span>';
			} 
			if ((isset($_POST['save']) and ($_POST['save']!='no')) or ((isset($_GET['save'])) and ($_GET['save']!='no'))){
				//Vérification de na non existance du partage
				$sql_verif = "SELECT id_site FROM ".TABLE_PREFIX."_j_etablissement_site WHERE id_site = '".$id_site."' AND id_etablissement = '".$id_etab."'";
				$res_verif = grr_sql_query($sql_verif);
				$nb_site_verif = grr_sql_count($res_verif);
				if ($nb_site_verif <> 0){
					echo '<span class="avertissement">'.get_vocab('share_exist').'</span>';
				} else {
					$sql = "INSERT INTO ".TABLE_PREFIX."_j_etablissement_site
					SET id_site = '".protect_data_sql($id_site)."',
					id_etablissement = '".protect_data_sql($id_etab)."'";
					if (grr_sql_command($sql) < 0) {fatal_error(0,'<p>'.grr_sql_error().'</p>');}
				}
			}
			shared_sites();
		} 
		// fin de l'affichage de la colonne de droite
		echo "</td></tr></table>\n";
		echo "</body>";
		echo "</html>";
	}
}
 

// Fonction delete_share : permet de supprimer le partage d'un site avec un établissement
// id_site : identifiant du site à ne plus partager
// id_etab : identifiant de l'établissement dont on veut détacher le site
function delete_share($id_site,$id_etab){
	
	if ((authGetUserLevel(getUserName(),-1,'area') >= 6) and (Settings::get("module_multietablissement") == "Oui") and (Settings::get("module_multisite") == "Oui")) {
		if (!(isset($_GET['confirm']))) {
			echo '<h2>'.get_vocab('delete_site_share').'</h2>';
			echo '<h2 style="text-align:center;">' .  get_vocab('sure') . '</h2>';
			echo '<h2 style="text-align:center;"><a href="admin_site.php?action=delshare&amp;id='.$id_site.'&amp;id_etab='.$id_etab.'&amp;confirm=yes">' . get_vocab('YES') . '!</a> &nbsp;&nbsp;&nbsp; <a href="admin_site.php?action=delshare&amp;id='.$id_site.'&amp;id_etab='.$id_etab.'&amp;confirm=no">' . get_vocab('NO') . '!</a></h2>';
		} else {
			if ($_GET['confirm']=='yes') {
				//if ($id_site=='' or $id_site=='-1' or $id_etab=='' or $id_etab=='-1')
				$sql = "DELETE FROM ".TABLE_PREFIX."_j_etablissement_site WHERE id_site = '".$id_site."' AND id_etablissement = '".$id_etab."'";
				if (grr_sql_command($sql) < 0) {fatal_error(0,'<p>'.grr_sql_error().'</p>');}
			}
			// On affiche le tableau des sites
			shared_sites();
		}
	}
}

///////////////////////////////////////////////////
// GIP RECIA | FIN
///////////////////////////////////////////////////


function update_site($id)
{
	if ((isset($_POST['back']) || isset($_GET['back'])))
	{
		 // On affiche le tableau des sites
		read_sites();
		exit();
	}
	 // On affiche le formulaire de saisie quand l'appel de la fonction ne provient pas de la validation de ce même formulaire
	if (!(isset($_POST['save']) || isset($_GET['save'])))
	{
		 // Initialisation
		$res = grr_sql_query("SELECT * FROM ".TABLE_PREFIX."_site WHERE id='".$id."'");
		if (!$res)
			fatal_error(0,'<p>'.grr_sql_error().'</p>');
		$row = grr_sql_row_keyed($res, 0);
		grr_sql_free($res);
		$sitecode = $row['sitecode'];
		$sitename = $row['sitename'];
		$adresse_ligne1 = $row['adresse_ligne1'];
		$adresse_ligne2 = $row['adresse_ligne2'];
		$adresse_ligne3 = $row['adresse_ligne3'];
		$cp = $row['cp'];
		$ville = $row['ville'];
		$pays = $row['pays'];
		$tel = $row['tel'];
		$fax = $row['fax'];
		// Affichage des titres de la page
		echo '      <h2>'.get_vocab('modifier site').'</h2>';
		echo '
		<form action="admin_site.php?action=update" method="post">
			<table>
				<tr><td>'.get_vocab('site_code').' *</td><td><input type="text" name="sitecode" value="'.$sitecode.'" size="10" title="'.get_vocab('site_code').'" /></td></tr>
				<tr><td>'.get_vocab('site_name').' *</td><td><input type="text" name="sitename" value="'.$sitename.'" size="50" title="'.get_vocab('site_name').'" /></td></tr>
				<tr><td>'.get_vocab('site_adresse_ligne1').'</td><td><input type="text" name="adresse_ligne1" value="'.$adresse_ligne1.'" size="38" title="'.get_vocab('site_adresse_ligne1').'" /></td></tr>
				<tr><td>'.get_vocab('site_adresse_ligne2').'</td><td><input type="text" name="adresse_ligne2" value="'.$adresse_ligne2.'" size="38" title="'.get_vocab('site_adresse_ligne2').'" /></td></tr>
				<tr><td>'.get_vocab('site_adresse_ligne3').'</td><td><input type="text" name="adresse_ligne3" value="'.$adresse_ligne3.'" size="38" title="'.get_vocab('site_adresse_ligne3').'" /></td></tr>
				<tr><td>'.get_vocab('site_cp').'</td><td><input type="text" name="cp" value="'.$cp.'" size="5" title="'.get_vocab('site_cp').'" /></td></tr>
				<tr><td>'.get_vocab('site_ville').'</td><td><input type="text" name="ville" value="'.$ville.'" size="50" title="'.get_vocab('site_ville').'" /></td></tr>
				<tr><td>'.get_vocab('site_pays').'</td><td><input type="text" name="pays" value="'.$pays.'" size="50" title="'.get_vocab('site_pays').'" /></td></tr>
				<tr><td>'.get_vocab('site_tel').'</td><td><input type="text" name="tel" value="'.$tel.'" size="25" title="'.get_vocab('site_tel').'" /></td></tr>
				<tr><td>'.get_vocab('site_fax').'</td><td><input type="text" name="fax" value="'.$fax.'" size="25" title="'.get_vocab('site_fax').'" /></td></tr>
			</table>
			<div><br/>
				<input type="hidden" name="valid" value="yes" />
				<input type="hidden" name="id" value="'.$id.'" />
				<input class="btn btn-primary" type="submit" name="save" value="'.get_vocab('save').'" />
				<input class="btn btn-primary" type="submit" name="back" value="'.get_vocab('back').'" /></div>
			</form>';
			echo get_vocab("required");
			// Sinon, il faut valider le formulaire
		}
		else
		{
			if (!isset($id))
				$id = isset($_POST['id']) ? $_POST['id'] :  NULL;
			if (!isset($sitecode))
				$sitecode = isset($_POST['sitecode']) ? $_POST['sitecode'] : NULL;
			if (!isset($sitename))
				$sitename = isset($_POST['sitename']) ? $_POST['sitename'] :  NULL;
			if (!isset($adresse_ligne1))
				$adresse_ligne1 = isset($_POST['adresse_ligne1']) ? $_POST['adresse_ligne1'] :  NULL;
			if (!isset($adresse_ligne2))
				$adresse_ligne2 = isset($_POST['adresse_ligne2']) ? $_POST['adresse_ligne2'] :  NULL;
			if (!isset($adresse_ligne3))
				$adresse_ligne3 = isset($_POST['adresse_ligne3']) ? $_POST['adresse_ligne3'] :  NULL;
			if (!isset($cp))
				$cp = isset($_POST['cp']) ? $_POST['cp'] :  NULL;
			if (!isset($ville))
				$ville = isset($_POST['ville']) ? $_POST['ville'] :  NULL;
			if (!isset($pays))
				$pays = isset($_POST['pays']) ? $_POST['pays'] :  NULL;
			if (!isset($tel))
				$tel = isset($_POST['tel']) ? $_POST['tel'] :  NULL;
			if (!isset($fax))
				$fax = isset($_POST['fax']) ? $_POST['fax'] :  NULL;
		 	// On vérifie que le code et le nom du site ont été renseignés
			if ($sitecode == '' || $sitecode == NULL || $sitename == '' || $sitename==NULL)
			{
				$_POST['save'] = 'no';
				$_GET['save'] = 'no';
				echo '<span class="avertissement">'.get_vocab('required').'</span>';
			}
			// Sauvegarde du record
			if ((isset($_POST['save']) && ($_POST['save']!='no')) || ((isset($_GET['save'])) && ($_GET['save']!='no')))
			{
				$sql = "UPDATE ".TABLE_PREFIX."_site
				SET sitecode='".strtoupper(protect_data_sql($sitecode))."',
				sitename='".protect_data_sql($sitename)."',
				adresse_ligne1='".protect_data_sql($adresse_ligne1)."',
				adresse_ligne2='".protect_data_sql($adresse_ligne2)."',
				adresse_ligne3='".protect_data_sql($adresse_ligne3)."',
				cp='".protect_data_sql($cp)."',
				ville='".strtoupper(protect_data_sql($ville))."',
				pays='".strtoupper(protect_data_sql($pays))."',
				tel='".protect_data_sql($tel)."',
				fax='".protect_data_sql($fax)."'
				WHERE id='".$id."'";
				if (grr_sql_command($sql) < 0)
					fatal_error(0,'<p>'.grr_sql_error().'</p>');
				mysqli_insert_id($GLOBALS['db_c']);
			}
			// On affiche le tableau des sites
			read_sites();
		}
	}
	function delete_site($id)
	{
		if (!(isset($_GET['confirm']))){
			
			if (Settings::get("module_multietablissement") == "Oui"){
				$id_etablissement = getIdEtablissementCourant();
			
				$sql = "SELECT * FROM ".TABLE_PREFIX."_j_etablissement_site WHERE id_etablissement = $id_etablissement";
				//echo $sql;
				$res = grr_sql_query($sql);
				if ( grr_sql_count($res) <= 1){
					echo '<h2>'.get_vocab('supprimer site').'</h2>';
					echo '<div style="text-align:center;">' .  get_vocab('impossible_supprimer_dernier_site') . '</div>';
					echo '<h2 style="text-align:center;"><a href="admin_site.php?action=delete&amp;id='.$id.'&amp;confirm=no">' . get_vocab('continuer') . '</a></h2>';
					return;
				}
			}
			
			echo '<h2>'.get_vocab('supprimer site').'</h2>';
			echo '<h2 style="text-align:center;">' .  get_vocab('sure') . '</h2>';
			echo '<h2 style="text-align:center;"><a href="admin_site.php?action=delete&amp;id='.$id.'&amp;confirm=yes">' . get_vocab('YES') . '!</a>     <a href="admin_site.php?action=delete&amp;id='.$id.'&amp;confirm=no">' . get_vocab('NO') . '!</a></h2>';
		}
		else
		{
			if ($_GET['confirm'] == 'yes')
			{
				grr_sql_command("delete from ".TABLE_PREFIX."_site where id='".$_GET['id']."'");
				grr_sql_command("delete from ".TABLE_PREFIX."_j_etablissement_site where id_site='".$_GET['id']."'");
				grr_sql_command("delete from ".TABLE_PREFIX."_j_site_area where id_site='".$_GET['id']."'");
				grr_sql_command("delete from ".TABLE_PREFIX."_j_useradmin_site where id_site='".$_GET['id']."'");
				grr_sql_command("update ".TABLE_PREFIX."_utilisateurs set default_site = '-1' where default_site='".$_GET['id']."'");
				$test = grr_sql_query1("select VALUE from ".TABLE_PREFIX."_setting where NAME='default_site'");
				if ($test == $_GET['id'])
					grr_sql_command("delete from ".TABLE_PREFIX."_setting where NAME='default_site'");
				// On affiche le tableau des sites
				read_sites();
			}
			else
			{
				// On affiche le tableau des sites
				read_sites();
			}
		}
	}
	function check_right($id)
	{
		echo 'Vous voulez vérifier les droits pour l\'identifiant '.$id;
	}
	
	// Debut de l'affichage de la page
	include_once('../include/admin.inc.php');
	
	$grr_script_name = 'admin_site.php';
	
	if (authGetUserLevel(getUserName(), -1, 'site') < 4){
		$back = '';
		if (isset($_SERVER['HTTP_REFERER']))
			$back = htmlspecialchars($_SERVER['HTTP_REFERER']);
		showAccessDenied($back);
		exit();
	}
	$back = "";
	if (isset($_SERVER['HTTP_REFERER']))
		$back = htmlspecialchars($_SERVER['HTTP_REFERER']);
	
	// print the page header
	print_header("", "", "", $type="with_session");
	
	// Affichage de la colonne de gauche
	include_once('admin_col_gauche.php');
	
	if ((isset($_GET['msg'])) && isset($_SESSION['displ_msg']) && ($_SESSION['displ_msg'] == 'yes') )
	{
		$msg = $_GET['msg'];
		affiche_pop_up($msg,'admin');
	}
	else
		$msg = '';
	// Lecture des paramètres passés à la page
	$id_site = isset($_POST['id']) ? $_POST['id'] : (isset($_GET['id']) ? $_GET['id'] : NULL);
	$id_etab = isset($_POST['id_etab']) ? $_POST['id_etab'] : (isset($_GET['id_etab']) ? $_GET['id_etab'] : NULL);
	$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : NULL);
	
	if ($action == NULL)
		$action = 'read';
	// SWITCH sur l'action (CRUD)
	switch($action)
	{
		case 'create':
			create_site($id_site);
		break;
		case 'read':
			read_sites();
		break;
		case 'update':
			update_site($id_site);
		break;
		case 'delete':
			delete_site($id_site);
		break;
		case 'shared':
			shared_sites($id_site);
		break;
		case 'share':
			share_site($id_site,$id_etab);
		break;
		case 'delshare':
			delete_share($id_site,$id_etab);
		break;
		case 'right':
		check_right($id_site);
		break;
		default:
		read_sites();
		break;
	}
	// fin de l'affichage de la colonne de droite
	echo "</td></tr></table>\n";
	?>
</body>
</html>