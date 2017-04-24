<?php
/**
 * config.inc.php
 * Fichier de configuration de GRR
 * Ce script fait partie de l'application GRR
 * Dernière modification : $Date: 2011-05-17 15:05:45 $
 * @author    Laurent Delineau <laurent.delineau@ac-poitiers.fr>
 * @copyright Copyright 2003-2008 Laurent Delineau
 * @link      http://www.gnu.org/licenses/licenses.html
 * @package   root
 * @version   $Id: config.inc.php,v 1.9 2011-05-17 15:05:45 grr Exp $
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

/*
Problème de sessions qui expirent prématurément :
Chez certains prestataire qui utilisent des serveurs en clustering, il arrive que les sessions expirent aléatoirement.
Une solution consiste à enregistrer les sessions PHP dans un autre répertoire que le répertoire par défaut.
Pour cela, il suffit de décommenter la ligne suivante (en supprimant le premier caractère #)
et en indiquant à la place de "le_chemin_de_stockage_de_la_session", l'emplacement du nouveau dossier de stockage des sessions.
*/
# ini_set ('session.save_path' , 'le_chemin_de_stockage_de_la_session');


/*
$nb_year_calendar permet de fixer la plage de choix de l'année dans le choix des dates de début et fin des réservations
La plage s'étend de année_en_cours - $nb_year_calendar à année_en_cours + $nb_year_calendar
Par exemple, si on fixe $nb_year_calendar = 5 et que l'on est en 2005, la plage de choix de l'année s'étendra de 2000 à 2010
*/
$nb_year_calendar = 2;

# Avance en nombre d'heure du serveur sur les postes clients
# Le paramètre $correct_diff_time_local_serveur permet de corriger une différence d'heure entre le serveur et les postes clients
# Exemple : si Grr est installé sur un serveur configuré GMT+1 alors qu'il est utilisé dans un pays dont le fuseau horaire est GMT-5
# Le serveur a donc six heures d'avance sur les postes clients
# On indique alors : $correct_diff_time_local_serveur=6;
$correct_diff_time_local_serveur=0;

/* Paramétrage du fuseau horaire (imposer à GRR un fuseau horaire différent de celui du serveur)
 TZ (Time Zone) est une variable permettant de préciser dans quel fuseau horaire, GRR travaille.
 L'ajustement de cette variable TZ permet au programme GRR de travailler dans la zone de votre choix.
 la valeur à donner à TZ diffère d'un système à un autre (Windows, Linux, ...)
 Par exemple, sur un système Linux, si on désire retarder de 7 heures l'heure système de GRR, on aura :
 putenv("TZ=posix/Etc/GMT-7")
 Remarque : putenv() est la fonction php  qui permet de fixer la valeur d'une variable d'environnement.
 Cette valeur n'existe que durant la vie du script courant, et l'environnement initial sera restauré lorsque le script sera terminé.
 En résumé, pour activer cette fonctionnalité, décommentez la ligne suivante (en supprimant le premier caractère #,
 et remplacez -7 par +n ou -n où "n" est le nombre d'heures d'avance ou de retard de GRR sur l'heure système du serveur.
*/
#putenv("TZ=posix/Etc/GMT-7");

# Changement d'heure été<->hiver
# $correct_heure_ete_hiver = 1 => GRR prend en compte les changements d'heure
# $correct_heure_ete_hiver = 0 => GRR ne prend en compte les changements d'heure
# Par défaut ($correct_heure_ete_hiver non définie) GRR prend en compte les changements d'heure.
$correct_heure_ete_hiver = 1;

# Affichage d'un domaine par defaut en fonction de l'adresse IP de la machine cliente (voir documentation)
# Mettre 0 ou 1 pour désactiver ou activer la fonction dans la page de gestion des domaines
define('OPTION_IP_ADR', 0);

# Nom de la session PHP.
# Le nom de session fait référence à l'identifiant de session dans les cookies.
# Il ne doit contenir que des caractères alpha-numériques; si possible, il doit être court et descriptif.
# Normalement, vous n'avez pas à modifier ce paramètre.
# Mais si un navigateur est amené à se connecter au cours de la même session, à deux sites GRR différents,
# ces deux sites GRR doivent avoir des noms de session différents.
# Dans ce cas, il vous faudra changer la valeur GRR ci-dessous par une autre valeur.
define('SESSION_NAME', "GRR");

# Nombre maximum (+1) de réservations autorisés lors d'une réservation avec périodicité
$max_rep_entrys = 365 + 1;

# Positionner la valeur $unicode_encoding à 1 pour utiliser l'UTF-8 dans toutes les pages et dans la base
# Dans le cas contraire, les textes stockés dans la base dépendent des différents encodage selon la langue selectionnée par l'utilisateur
# Il est fortement conseillé de lire le fichier notes-utf8.txt à la racine de cette archive
$unicode_encoding = 1;

# Après installation de GRR, si vous avez le message "Fatal error: Call to undefined function: mysql_real_escape_string() ...",
# votre version de PHP est inférieure à 4.3.0.
# En effet, la fonction mysql_real_escape_string() est disponible à partir de la version 4.3.0 de php.
# Vous devriez mettre à jour votre version de php.
# Sinon, positionnez la variable suivante à "0"; (valeur par défaut = 1)
$use_function_mysql_real_escape_string = 1;

# Apres installation de GRR, si vous avez le message "Fatal error: Call to undefined function: html_entity_decode() ...",
# votre version de PHP est inferieure a 4.3.0.
# En effet, la fonction html_entity_decode() est disponible a partir de la version 4.3.0 de php.
# Vous devriez mettre a jour votre version de php.
# Sinon, positionnez la variable suivante a "0"; (valeur par defaut = 1)
$use_function_html_entity_decode = 1;

###################################
# Cas d'une authentification SSO  #
###################################

/*
$sso_super_admin : false|true
Mettre la valeur du paramètre $sso_super_admin à "true" pour rendre possible l'accès à la page login.php même si l'administrateur a coché dans l'interface en ligne le choix "Empêcher l'accès à la page de login".
*/
$sso_super_admin = false;

/*
 $sso_restrictions : false|true
 Mettre la valeur du paramètre $sso_restrictions à "true" permet de cacher dans l'interface de GRR l'affichage de la rubrique "Authentification et ldap"
*/
$sso_restrictions = false;

// Le paramètre $Url_CAS_setFixedServiceURL est le paramètre utilisé dans la méthode phpCAS::setFixedServiceURL(), dans le fichier cas.inc.php
// Si ce paramètre est non vide, il sera utilisé par le service CAS
// Set the fixed URL that will be set as the CAS service parameter. When this method is not called, a phpCAS script uses its own URL.
$Url_CAS_setFixedServiceURL = '';


#####################################################
# Paramètres propres à une authentification SSO LASSO
#####################################################
// Indiquez ci-dessous le répertoire d'installation du package spkitlasso
// (la valeur par défaut le cherche dans le 'include_path' de PHP)
define('SPKITLASSO',"spkitlasso");

##############################################################
# Paramètres propres à une authentification sur un serveur LCS
##############################################################
# Page d'authentification LCS
define('LCS_PAGE_AUTHENTIF',"../../lcs/auth.php");
# Page de la librairie ldap
define('LCS_PAGE_LDAP_INC_PHP',"/var/www/Annu/includes/ldap.inc.php");
# Réalise la connexion à la base d'authentification du LCS et include_once des fonctions de lcs/includes/functions.inc.php
define('LCS_PAGE_AUTH_INC_PHP',"/var/www/lcs/includes/headerauth.inc.php");

#############
# Entry Types
#############
# Les lignes ci-dessous correspondent aux couleurs disponibles pour les types de réservation
# Vous pouvez modifier les couleurs ou même en rajouter à votre convenance.
$tab_couleur[1] = "#FFCCFF"; # mauve pâle
$tab_couleur[2] = "#99CCCC"; # bleu
$tab_couleur[3] = "#FF9999"; # rose pâle
$tab_couleur[4] = "#FFFF99"; # jaune pâle
$tab_couleur[5] = "#C0E0FF"; # bleu-vert
$tab_couleur[6] = "#FFCC99"; # pêche
$tab_couleur[7] = "#FF6666"; # rouge
$tab_couleur[8] = "#66FFFF"; # bleu "aqua"
$tab_couleur[9] = "#DDFFDD"; # vert clair
$tab_couleur[10] = "#CCCCCC"; # gris
$tab_couleur[11] = "#7EFF7E"; # vert pâle
$tab_couleur[12] = "#8000FF"; # violet
$tab_couleur[13] = "#FFFF00"; # jaune
$tab_couleur[14] = "#FF00DE"; # rose
$tab_couleur[15] = "#00FF00"; # vert
$tab_couleur[16] = "#FF8000"; # orange
$tab_couleur[17] = "#DEDEDE"; # gris clair
$tab_couleur[18] = "#C000FF"; # Mauve
$tab_couleur[19] = "#FF0000"; # rouge vif
$tab_couleur[20] = "#FFFFFF"; # blanc
$tab_couleur[21] = "#A0A000"; # Olive verte
$tab_couleur[22] = "#DAA520"; # marron goldenrod
$tab_couleur[23] = "#40E0D0"; # turquoise
$tab_couleur[24] = "#FA8072"; # saumon
$tab_couleur[25] = "#4169E1"; # bleu royal
$tab_couleur[26] = "#6A5ACD"; # bleu ardoise
$tab_couleur[27] = "#AA5050"; # bordeaux
$tab_couleur[28] = "#FFBB20"; # pêche


###################
# Database settings
###################

# Quel système de base de données : "pgsql"=PostgreSQL, "mysql"=MySQL
# Actuellement, GRR ne supporte que mysql.
$dbsys = "mysql";
# Uncomment this to NOT use PHP persistent (pooled) database connections:
#$db_nopersist = 1;

################################
# Backup information
#################################
#true=sauvegarde la structure des tables
$structure = true;
#true=sauvegarde les donnees des tables
$donnees = true;
#clause INSERT avec nom des champs
$insertComplet = false;

# Global settings array
$grrSettings = array();
$grrSettingsEtab = array();

#########################################################################################
# Utilisé par my_htmlspecialcharacters pour compatibilité php 5.4 - RECIA/CD - 23/07/2015
#########################################################################################
if ($unicode_encoding == 0) {
  define('CHARSET', 'ISO-8859-1');
}
else {
  define('CHARSET', 'UTF-8');
}
define('REPLACE_FLAGS', ENT_COMPAT | ENT_XHTML);
// Fin ajout RECIA/CD

# Make sure notice errors are not reported
error_reporting (E_ALL ^ E_NOTICE);
?>
