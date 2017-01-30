<?php
/**
 * admin_etablissement.php
 * 
 * Interface d'accueil de Gestion des établissements de l'application GRR
 * Dernière modification : $Date: 2010-05-07 21:26:44 $
 * @author    Laurent Delineau <laurent.delineau@ac-poitiers.fr>
 * @author    Marc-Henri PAMISEUX <marcori@users.sourceforge.net>
 * @copyright Copyright 2003-2008 Laurent Delineau
 * @copyright Copyright 2008 Marc-Henri PAMISEUX
 * @link      http://www.gnu.org/licenses/licenses.html
 * @package   admin
 * @version   $Id: admin_site.php,v 1.1 2010-05-07 21:26:44 grr Exp $
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

//include_once "../include/misc.inc.php";

/**
 * Compte le nombre d'établissement définis
 *
 * @return integer number of rows
 */
 function count_etablissements()
 {
   $sql = "SELECT COUNT(*)
   		FROM ".TABLE_PREFIX."_etablissement";
   $res = grr_sql_query($sql);
   if ($res)
   {
     $etablissements = grr_sql_row($res,0);
     if (is_array($etablissements))
       return $etablissements[0];
       else
       {
         echo '      <p>Une erreur est survenue pendant le comptage des établissements.</p>';
         // fin de l'affichage de la colonne de droite
         echo "</td></tr></table>\n</body>\n</html>\n";
         die();
       }
   } else {
     echo '      <p>Une erreur est survenue pendant la préparation de la requète de comptage des établissements.</p>';
     // fin de l'affichage de la colonne de droite
     echo "</td></tr></table>\n</body>\n</html>\n";
     die();
   }
 }

 function create_etablissement($id)
 {
   if ((isset($_POST['back']) or isset($_GET['back']))) {
     // On affiche le tableau des sites
     read_sites();
     exit();
   }
   // Initialisation des variables du formulaire
   if (!isset($id)) $id = isset($_POST['id']) ? $_POST['id'] :  NULL;
   if (!isset($code)) $code = isset($_POST['code']) ? $_POST['code'] : NULL;
   if (!isset($name)) $name = isset($_POST['name']) ? $_POST['name'] :  NULL;
   if (!isset($shortname)) $shortname = isset($_POST['shortname']) ? $_POST['shortname'] :  NULL;
   if (!isset($ville)) $ville = isset($_POST['ville']) ? $_POST['ville'] :  NULL;
   if (!isset($codepostal)) $codepostal = isset($_POST['codepostal']) ? $_POST['codepostal'] :  NULL;
   if (!isset($adresse)) $adresse = isset($_POST['adresse']) ? $_POST['adresse'] :  NULL;
   

   // On affiche le formulaire de saisie quand l'appel de la fonction ne provient pas de la validation de ce même formulaire
   if ((! (isset($_POST['save']) or isset($_GET['save']))) AND ($id==0))
   {
   // Affichage des titres de la page
     echo '      <h2>'.get_vocab('addetablissement').'</h2>';
     echo '
      <form action="admin_etablissement.php?action=create" method="post">
        <table>
          <tr><td>'.get_vocab('etab_code').'&nbsp;*</td><td><input type="text" name="code" value="'.$code.'" size="20" title="'.get_vocab('etab_code').'" /></td></tr>
          <tr><td>'.get_vocab('etab_name').'&nbsp;*</td><td><input type="text" name="name" value="'.$name.'" size="50" title="'.get_vocab('etab_name').'" /></td></tr>
          <tr><td>'.get_vocab('etab_shortName').'&nbsp;*</td><td><input type="text" name="shortname" value="'.$shortname.'" size="30" title="'.get_vocab('etab_shortName').'" /></td></tr>
          <tr><td>'.get_vocab('etab_ville').'</td><td><input type="text" name="ville" value="'.$ville.'" size="50" title="'.get_vocab('etab_ville').'" /></td></tr>
          <tr><td>'.get_vocab('etab_adresse').'</td><td><input type="text" name="adresse" value="'.$adresse.'" size="50" title="'.get_vocab('etab_adresse').'" /></td></tr>
          <tr><td>'.get_vocab('etab_codepostal').'</td><td><input type="text" name="codepostal" value="'.$codepostal.'" size="50" title="'.get_vocab('etab_codepostal').'" /></td></tr>
        </table>
        <div>
        <input type="hidden" name="valid" value="yes" />
        <input type="submit" name="save" value="'.get_vocab('save').'" />
        <input type="submit" name="back" value="'.get_vocab('back').'" />
        </div>
      </form>';
      echo get_vocab("required");
   // Sinon, il faut valider le formulaire
   } else {
     $msg ='';

     // On vérifie que le code et le nom du site ont été renseignés
     if ($code=='' or $code==NULL or $name=='' or $name==NULL)
     {
       $_POST['save'] = 'no';
       $_GET['save'] = 'no';
       echo '<span class="avertissement">'.get_vocab('required').'</span>';
     }
     
     if ($shortname=='' or $shortname==NULL){
     	$shortname = $name;
     }

     // Sauvegarde du record
     if ((isset($_POST['save']) and ($_POST['save']!='no')) or ((isset($_GET['save'])) and ($_GET['save']!='no')))
     {
     	$sql="INSERT INTO ".TABLE_PREFIX."_etablissement
       			SET code='".strtoupper(protect_data_sql($code))."',
       			shortname='".protect_data_sql($shortname)."',
       			name='".protect_data_sql($name)."',
       			ville='".protect_data_sql($ville)."',
       			codepostal='".protect_data_sql($codepostal)."',
       			adresse='".protect_data_sql($adresse)."'";
     	if (grr_sql_command($sql) < 0)
     		fatal_error(0,'<p>'.grr_sql_error().'</p>');
     	$etablissement = grr_sql_insert_id();
     	
     	$sql="INSERT INTO ".TABLE_PREFIX."_site
                  		SET sitecode='".strtoupper(protect_data_sql($code))."',
                  			sitename='".protect_data_sql($shortname)."'";
     	if (grr_sql_command($sql) < 0)
	     	fatal_error(0,'<p>'.grr_sql_error().'</p>');
     	$site = grr_sql_insert_id();

     	$sql="INSERT INTO ".TABLE_PREFIX."_j_etablissement_site
                  		SET id_etablissement = $etablissement , 
     						id_site = $site ";
     	if (grr_sql_command($sql) < 0)
	     	fatal_error(0,'<p>'.grr_sql_error().'</p>');

     }
     // On affiche le tableau des sites
     read_etablissement();
   }
 }

 function read_etablissement()
 {
   global $canEditUserEtab;	
 	
   // Affichage des titres de la page
   echo '      <h2>'.get_vocab('admin_etablissement.php')./*grr_help("aide_grr_multisites").*/'</h2>';
   //echo '      <p>'.get_vocab('admin_etablissement_explications').'</p> ';
   
   if (authGetUserLevel(getUserName(), '-1', 'etab') >= 7){
   		echo ' | <a href="admin_etablissement.php?action=create&amp;id=0">'.get_vocab('display_add_etablissement').'</a> |';
   }   
   
   if (count_etablissements()>0) {

   if (authGetUserLevel(getUserName(), 'etab') >= 7){
	   	$sql = "SELECT E.id,E.code,E.name, E.shortname, E.ville, E.codepostal, E.adresse
			FROM ".TABLE_PREFIX."_etablissement  AS E
	 		ORDER BY E.name,E.id";
   } else {
   		$user = getUserName();
   		$sql = "SELECT E.id,E.code,E.name, E.shortname, E.ville, E.codepostal, E.adresse 
   				FROM ".TABLE_PREFIX."_etablissement AS E
   				JOIN ".TABLE_PREFIX."_j_useradmin_etablissement AS UE ON UE.id_etablissement = E.id
   				WHERE UE.login =  '$user'
   		 		ORDER BY E.name,E.id";
   }
   $res = grr_sql_query($sql);
   if ($res)
   {
     // Affichage de l'entête du tableau
     echo '		<table class="table table-hover table-bordered">
        <thead><tr>
          <th>'.get_vocab('action').'</th>
          <th>'.get_vocab('etab_code').'</th>
          <th>'.get_vocab('etab_name').'</th>
          <th>'.get_vocab('etab_shortName').'</th>
          <th>'.get_vocab('etab_ville').'</th>
          <th>'.get_vocab('etab_adresse').'</th>
          <th>'.get_vocab('etab_codepostal').'</th>
        </tr></thead><tbody>';
     for ($i = 0; ($row=grr_sql_row($res,$i));$i++)
     {
       echo '        <tr>
          <td>
            <a href="admin_etablissement.php?action=update&amp;id='.$row[0].'"><img class="image" title="'.get_vocab('change').'" alt="'.get_vocab('change').'" src="../img_grr/edit_s.png" /></a>';
       
       if (authGetUserLevel(getUserName(), '-1', 'etab') >= 7){
            echo '<a href="admin_etablissement.php?action=delete&amp;id='.$row[0].'"><img class="image" title="'.get_vocab('delete').'" alt="'.get_vocab('delete').'" src="../img_grr/delete_s.png" /></a>';
       }
       
       if ($canEditUserEtab){
	       echo '<a href="admin_access_etablissement.php?id='.$row[0].'">';
       } else {
	       echo "<a href='javascript:centrerpopup(\"../view_rights_etablissement.php?id=$row[0]\",600,480,\"scrollbars=yes,statusbar=no,resizable=yes\")'>";
       }   
       
       echo '  <img class=\"image\" title="'.get_vocab('privileges').'" alt="'.get_vocab('privileges').'" src="../img_grr/rights.png" /></a>';
	  
       #echo "<a href='javascript:centrerpopup(\"../admin/admin_type_etablissement\",600,480,\"scrollbars=yes,statusbar=no,resizable=yes\")' title=\"".get_vocab("privileges")."\">
       #		 <img src=\"../img_grr/type.png\" alt=\"".get_vocab("edittype")."\" class=\"image\" /></a>";
       
       echo '</td>
          <td>'.$row[1].'</td>
          <td>'.$row[2].'</td>
          <td>'.$row[3].'</td>
          <td>'.$row[4].'</td>
          <td>'.$row[5].'</td>
          <td>'.$row[6].'</td>
        </tr>';
     }
     echo '      </tbody></table>';
   } else {
     echo '      <p>Une erreur est survenue pendant la préparation de la requète de lecture des établissements.</p>';
     // fin de l'affichage de la colonne de droite
     echo "</td></tr></table>\n</body>\n</html>\n";
     die();
     }
     // fin de l'affichage de la colonne de droite
     echo "</td></tr></table>\n</body>\n</html>\n";
     die();
   }
 }

 function update_etablissement($id)
 {
 	
   global $desactive_edition_code_etablissement;
   if ((isset($_POST['back']) or isset($_GET['back']))) {
     // On affiche le tableau des sites
     read_etablissement();
     exit();
   }
   // On affiche le formulaire de saisie quand l'appel de la fonction ne provient pas de la validation de ce même formulaire
   if (! (isset($_POST['save']) or isset($_GET['save'])))
   {
     // Initialisation
     $res = grr_sql_query("SELECT * FROM ".TABLE_PREFIX."_etablissement WHERE id='".$id."'");
     if (! $res) fatal_error(0,'<p>'.grr_sql_error().'</p>');
     $row = grr_sql_row_keyed($res, 0);
     grr_sql_free($res);
     $code = $row['code'];
     $name = $row['name'];
     $shortname = $row['shortname'];
     $ville = $row['ville'];
     $adresse = $row['adresse'];
     $codepostal = $row['codepostal'];
     
     // Affichage des titres de la page
     echo '      <h2>'.get_vocab('modifier etablissement').'</h2>';
     echo '
      <form action="admin_etablissement.php?action=update" method="post">
        <table> ';
     if ($desactive_edition_code_etablissement == 1)  {
     	 echo '<tr><td>'.get_vocab('etab_code').'&nbsp;*</td><td> '.$code.'</td></tr>';
     } else {
	     echo '<tr><td>'.get_vocab('etab_code').'&nbsp;*</td><td><input type="text" name="code" value="'.$code.'" size="20" title="'.get_vocab('etab_code').'" /></td></tr>';
     }
     
     echo '<tr><td>'.get_vocab('etab_name').'&nbsp;*</td><td><input type="text" name="name" value="'.$name.'" size="50" title="'.get_vocab('etab_name').'" /></td></tr>
          <tr><td>'.get_vocab('etab_shortName').'&nbsp;*</td><td><input type="text" name="shortname" value="'.$shortname.'" size="30" title="'.get_vocab('etab_shortName').'" /></td></tr>
          <tr><td>'.get_vocab('etab_ville').'</td><td><input type="text" name="ville" value="'.$ville.'" size="50" title="'.get_vocab('etab_ville').'" /></td></tr>
          <tr><td>'.get_vocab('etab_adresse').'</td><td><input type="text" name="adresse" value="'.$adresse.'" size="50" title="'.get_vocab('etab_adresse').'" /></td></tr>
          <tr><td>'.get_vocab('etab_codepostal').'</td><td><input type="text" name="codepostal" value="'.$codepostal.'" size="50" title="'.get_vocab('etab_codepostal').'" /></td></tr>
        </table>
        <div>
		<br/>
        <input type="hidden" name="valid" value="yes" />
        <input type="hidden" name="id" value="'.$id.'" />
        <input class="btn btn-primary" type="submit" name="save" value="'.get_vocab('save').'" />
        <input class="btn btn-primary" type="submit" name="back" value="'.get_vocab('back').'" /></div>
        </form>';
      echo get_vocab("required");
   // Sinon, il faut valider le formulaire
   } else {
     $msg ='';
     if (!isset($id)) $id = isset($_POST['id']) ? $_POST['id'] :  NULL;
   if (!isset($code)) $code = isset($_POST['code']) ? $_POST['code'] : NULL;
   if (!isset($name)) $name = isset($_POST['name']) ? $_POST['name'] :  NULL;
   if (!isset($shortname)) $shortname = isset($_POST['shortname']) ? $_POST['shortname'] :  NULL;
   if (!isset($ville)) $ville = isset($_POST['ville']) ? $_POST['ville'] :  NULL;
   if (!isset($codepostal)) $codepostal = isset($_POST['codepostal']) ? $_POST['codepostal'] :  NULL;
   if (!isset($adresse)) $adresse = isset($_POST['adresse']) ? $_POST['adresse'] :  NULL;

     // On vérifie que le code et le nom du site ont été renseignés
      if ( (($code=='' or $code==NULL ) AND $desactive_edition_code_etablissement == 0) or $name=='' or $name==NULL)
     {
       $_POST['save'] = 'no';
       $_GET['save'] = 'no';
       echo '<span class="avertissement">'.get_vocab('required').'</span>';
     }

     if ($shortname=='' or $shortname==NULL){
     	$shortname = $name;
     }
     // Sauvegarde du record
     if ((isset($_POST['save']) and ($_POST['save']!='no')) or ((isset($_GET['save'])) and ($_GET['save']!='no')))
     {
         $sql="update ".TABLE_PREFIX."_etablissement
       		SET ";
         if ($desactive_edition_code_etablissement == 0)  {
	       	 $sql .="code='".strtoupper(protect_data_sql($code))."',";
         }
         $sql .= "		shortname='".protect_data_sql($shortname)."',
            			name='".protect_data_sql($name)."',
            			ville='".protect_data_sql($ville)."',
       					codepostal='".protect_data_sql($codepostal)."',
       					adresse='".protect_data_sql($adresse)."' 
            where id='".$id."'";
         
         if (grr_sql_command($sql) < 0)
           fatal_error(0,'<p>'.grr_sql_error().'</p>');
           $site = grr_sql_insert_id();
     }
     // On affiche le tableau des établissements
     read_etablissement();
   }

 }

 function delete_etablissement($id)
 {
  if (!(isset($_GET['confirm']))) {
  	//Demande de confirmation
  	echo '<h2>'.get_vocab('supprimer etablissement').'</h2>';
  	echo '<h2 style="text-align:center;">' .  get_vocab('sure') . '</h2>';
  	echo '<h2 style="text-align:center;"><a href="admin_etablissement.php?action=delete&amp;id='.$id.'&amp;confirm=yes">' . get_vocab('YES') . '!</a> &nbsp;&nbsp;&nbsp; <a href="admin_etablissement.php?action=delete&amp;id='.$id.'&amp;confirm=no">' . get_vocab('NO') . '!</a></h2>';
  } else {
  	if ($_GET['confirm']=='yes') {
  		grr_sql_command("delete from ".TABLE_PREFIX."_etablissement where id='".$_GET['id']."'");
  		grr_sql_command("delete from ".TABLE_PREFIX."_j_etablissement_site where id_etablissement='".$_GET['id']."'");
  		grr_sql_command("delete from ".TABLE_PREFIX."_j_user_etablissement where id_etablissement='".$_GET['id']."'");
  		grr_sql_command("delete from ".TABLE_PREFIX."_j_useradmin_etablissement where id_etablissement='".$_GET['id']."'");
  		grr_sql_command("delete from ".TABLE_PREFIX."_j_etablissement_calendar where id_etablissement='".$_GET['id']."'");
  		grr_sql_command("delete from ".TABLE_PREFIX."_j_etablissement_type_area where id_etablissement='".$_GET['id']."'");
  		grr_sql_command("delete from ".TABLE_PREFIX."_setting_etablissement where code_etab='".$_GET['id']."'");
  		grr_sql_command("update ".TABLE_PREFIX."_utilisateurs set default_etablissement = '-1' where default_etablissement='".$_GET['id']."'");
  	}
  	// On affiche le tableau des sites
  	read_etablissement();
  }

 }

 function check_right($id)
 {
   echo 'Vous voulez vérifier les droits pour l\'identifiant '.$id;
 }

// Debut de l'affichage de la page
 include_once('../include/admin.inc.php');
 include_once('../include/misc.inc.php');
 
 $grr_script_name = 'admin_etablissement.php';

 //Varible globale permettant de savoir si l'utilisateur peut modifier les utilisateurs de l'établissement.
 //Test  : Administrateur GRR && configuration permet la modification.

 $canEditUserEtab = (isset($desactive_changement_etablissement_user))  && ($desactive_changement_etablissement_user == 0) && (authGetUserLevel(getUserName(), -1,'etab') >= 7 );
 
 if(authGetUserLevel(getUserName(),-1,'etablissement') < 6)
 {
   $back = '';
   if (isset($_SERVER['HTTP_REFERER']))
     $back=htmlspecialchars($_SERVER['HTTP_REFERER']);
   $day   = date('d');
   $month = date('m');
   $year  = date('Y');
   showAccessDenied($day,$month,$year,'',$back);
   exit();
 }

 $back = NULL;
 if (isset($_SERVER['HTTP_REFERER']))
   $back = htmlspecialchars($_SERVER['HTTP_REFERER']);

 // print the page header
 //print_header(NULL,NULL,NULL,NULL,$type='with_session',$page='admin');
 print_header('', '', '', $type = 'with_session');
 
 // Affichage de la colonne de gauche
 include_once('admin_col_gauche.php');

 if ((isset($_GET['msg'])) and isset($_SESSION['displ_msg'])  and ($_SESSION['displ_msg']=='yes') )
 {
   $msg = $_GET['msg'];
   affiche_pop_up($msg,'admin');
 }
 else
   $msg = '';

 // Lecture des paramètres passés à la page
 $id = isset($_POST['id']) ? $_POST['id'] : (isset($_GET['id']) ? $_GET['id'] : NULL);
 $action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : NULL);

 if ($action == NULL)
   $action='read';

 // SWITCH sur l'action (CRUD)
 switch($action)
 {
   case 'create':
     create_etablissement($id);
   break;
   case 'update':
     update_etablissement($id);
   break;
   case 'delete':
     delete_etablissement($id);
   break;
   case 'right':
     check_right($id);
   break;
   case 'read':
   default:
      read_etablissement();
   break;
 }
// fin de l'affichage de la colonne de droite
echo "</td></tr></table>\n";
?>
</body>
</html>