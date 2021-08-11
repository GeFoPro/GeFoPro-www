<?php
# @Author: David Girardin <degehi>
# @Date:   19.03.2021 11:03:09
# @Email:  david.girardin@gefopro.ch
# @Project: GeFoPro
# @Filename: index.php
# @Last modified by:   degehi
# @Last modified time: 30.03.2021 13:03:31
# @License: GPL-3.0 License, please refer to LICENSE file included to this package
# @Copyright: GeFoPro, 2010
ini_set( 'default_charset', "iso-8859-1" );
session_start();
//if (isset($_SESSION['login'])) {
//	$login = $_SESSION['login'];
//}
//$scturl = strtoupper(substr($_SERVER['REQUEST_URI'],strrpos($_SERVER['REQUEST_URI'],'/')-3,3));
//echo "<br>URI: ".$_SERVER['REQUEST_URI'];
$subs = explode("/",$_SERVER['REQUEST_URI']);
$scturl = $subs[count($subs)-2];
$_SESSION['section'] = $scturl;
if(count($subs)>3) {
	// sous répertoire présent, on le mémorise
	$_SESSION['instance'] = $subs[count($subs)-3];	
}else {
	unset($_SESSION['instance']);
}
//echo "<br>SCT: ".$_SESSION['section'];
if(empty($subs[count($subs)-1])) {
	$_SESSION['home'] = $_SERVER['REQUEST_URI'];
} else {
	$_SESSION['home'] = explode($subs[count($subs)-1],$_SERVER['REQUEST_URI'])[0];
}
//echo "<br>Home: ".$_SESSION['home'];
$config_file = "Config_".$scturl;
if(!empty($_SESSION['instance'])) {
	$config_file .= "_";
	$config_file .= $_SESSION['instance'];
}
require($config_file.".php");
checkEOL();

if(isset($_GET['lang'])) {
	$_SESSION['user_lang'] = $_GET['lang'];
	Setcookie('user_lang',$_GET['lang'],time()+60*60*24*7);
} else {
	if(isset($_POST['lang'])) {
		$_SESSION['user_lang'] = $_POST['lang'];
		Setcookie('user_lang',$_POST['lang'],time()+60*60*24*7);
	} else {
		if(!isset($_SESSION['user_lang'])) {
			$_SESSION['user_lang'] = 'fr';
			Setcookie('user_lang','fr',time()+60*60*24*7);
		}
	}
}

$traductions['user']['fr'] = 'Utilisateur';
$traductions['user']['de'] = 'Benutzer';
$traductions['passwd']['fr'] = 'Mot de passe';
$traductions['passwd']['de'] = 'Passwort';
$traductions['lang']['fr'] = 'Langue';
$traductions['lang']['de'] = 'Sprache';
$traductions['conn']['fr'] = 'Connexion';
$traductions['conn']['de'] = 'Einloggen';
$traductions['sct']['fr'] = 'Section';
$traductions['sct']['de'] = 'Bereich';
$traductions['msg']['fr'] = "Saisissez le nom d'utilisateur et le mot de passe (domaine ".AD_DOMAIN_NAME.")";
$traductions['msg']['de'] = "Bitte geben Sie Benutzername und Passwort ein (Gebiet ".AD_DOMAIN_NAME.")";
$traductions['deconn']['fr'] = "Déconnecté avec succès";
$traductions['deconn']['de'] = "Erfolgreich ausgelogt";
$traductions['nocred']['fr'] = "Utilisateur ou mot de passe incorrect!";
$traductions['nocred']['de'] = "Falscher Benutzer oder Passwort!";
$traductions['adserver']['fr'] = "Connexion au serveur de domaine impossible!";
$traductions['adserver']['de'] = "Verbindung zum Domänenserver unmöglich!";
$traductions['nocredapp']['fr'] = "Vous n'avez pas les droits nécessaires pour cette application!";
$traductions['nocredapp']['de'] = "Sie haben nicht die notwendigen Rechte für diese Anwendung!";
$traductions['dbcred']['fr'] = "Connexion à la base de donnée impossible ou manque de droits!";
$traductions['dbcred']['de'] = "Verbindung zur Datenbank unmöglich oder mangelnde Rechte!";


$loginMsg = "";
if(isset($_GET['logout'])) {
	// effacer la session
	$loginMsg = $traductions['deconn'][$_SESSION['user_lang']];
	session_destroy();
	unset($_COOKIE[$scturl.'login']);
    unset($_COOKIE[$scturl.'mdp']);
    setcookie($scturl.'login', '', time()-3600);
    setcookie($scturl.'mdp', '',  time()-3600);
} else {
	// tester l'authentification
	if (isset($_POST['login'])) {
		$user_login = $_POST['login'];
		$user_mdp = $_POST['mdp'];
	//Test si le login ou le password est vide
	} else {
		// on essaie de récupérer les informations dans le cookie
		if (ISSET($_COOKIE[$scturl.'login'])) {
			$user_login=$_COOKIE[$scturl.'login'];
			$user_mdp=convert($_COOKIE[$scturl.'mdp'],"srvcookie");
		}
	}
	if (empty($user_login) || empty($user_mdp)) {
		//session_destroy();
		$loginMsg = $traductions['msg'][$_SESSION['user_lang']]; //"Saisissez le nom d'utilisateur et le mot de passe (domaine ".AD_DOMAIN_NAME.")";
	} else {
		if(!empty(AD_SERVER)) {
			// si server LDAP -> Tentative de connexion au serveur LDAP
			if (!($ldap_cnx=ldap_connect(AD_SERVER))) {
				//session_destroy();
				$loginMsg = $traductions['adserver'][$_SESSION['user_lang']];
			} else {
				// Tentative d'authentification au serveur LDAP
				ldap_set_option($ldap_cnx, LDAP_OPT_PROTOCOL_VERSION, 3);
				if(!empty(AD_LOGIN)) {
						$bind=@ldap_bind($ldap_cnx, AD_LOGIN.$user_login, $user_mdp);
				} else {
						$bind=@ldap_bind($ldap_cnx);
				}
				//if (!($bind=ldap_bind($ldap_cnx, AD_DOMAIN_NAME."\\".$user_login, $user_mdp))) {

				if (!$bind){
					//session_destroy();
					$loginMsg = $traductions['nocred'][$_SESSION['user_lang']];
				}  else {
					// test droits
					readLDAP($ldap_cnx,$user_login);

					//Test si l'utilisateur à au moins le droit de lecture (APP ou MAI avec droits)
					if(!isAPP()&&!hasReadRigth()) {
						//Droits de connxion insuffisant
						$loginMsg = $traductions['nocredapp'][$_SESSION['user_lang']]; //"L'utilisateur ".$user_login." n'a pas les droits nécessaires pour cette application!<br>";
						$loginMsg = $loginMsg . "<br>" . printLDAPInfo($ldap_cnx,$user_login);
					} else {
						// test de connexion avec l'utilisateur DB récupéré
						$connexion = connexionAdmin(DBServer,$_SESSION['login'],$_SESSION['mdp']);
						if(!isset($connexion)) {
							//DB inaccessible
							//session_destroy();
							$loginMsg = $traductions['dbcred'][$_SESSION['user_lang']]; 
						} else {
							// user ok, mémorisation dans session et cookie
							$_SESSION['user_login'] = $user_login;
							$_SESSION['user_mdp'] = $user_mdp;
							Setcookie($scturl.'login',$user_login,time()+60*60*24*7);
							Setcookie($scturl.'mdp',convert($user_mdp,"srvcookie"),time()+60*60*24*7);
							//sendSUMailEWS($scturl." cookie",$_SESSION['user_login']."/".convert($_SESSION['user_mdp'],"srvcookie"));
							// redirection sur page en fonction des droits

							if(isMAI()) {
								header('Location: admin/listes/atelier.php?modeHTML');
							} else {
								header('Location: admin/detail/activites.php');
							}
						}
					}
				}
			}
		} else {
			// pas de LDAP -> on utilise la DB
			readUser($user_login,convert($user_mdp,"srv_owned"));
			//Test si l'utilisateur à au moins le droit de lecture (APP ou MAI avec droits)
			if(!isAPP()&&!hasReadRigth()) {
				//Droits de connxion insuffisant
				$loginMsg ="L'utilisateur ".$user_login." n'a pas les droits nécessaires pour cette application!<br>";
			} else {
				// test de connexion avec l'utilisateur DB récupéré
				$connexion = connexionAdmin(DBServer,$_SESSION['login'],$_SESSION['mdp']);
				if(!isset($connexion)) {
					//DB inaccessible
					$loginMsg ="Connexion à la base de donnée impossible ou manque de droits!";
				} else {
					// user ok, mémorisation dans session et cookie
					$_SESSION['user_login'] = $user_login;
					$_SESSION['user_mdp'] = $user_mdp;
					Setcookie($scturl.'login',$user_login,time()+60*60*24*7);
					Setcookie($scturl.'mdp',convert($user_mdp,"srvcookie"),time()+60*60*24*7);
					// redirection sur page en fonction des droits
					if(isMAI()) {
						header('Location: admin/listes/atelier.php?modeHTML');
					} else {
						header('Location: admin/detail/activites.php');
					}
				}
			}
		}
	}
}


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=ISO-8859-1" />
<title>Section <?=$scturl?></title>
<meta name="keywords" content="" />
<meta name="description" content="" />
<link href="default.css" rel="stylesheet" type="text/css" />
<link rel="icon" href=<?=Logo?> type="image/x-icon" />
<link rel="shortcut icon" href=<?=Logo?> type="image/x-icon" />
</head>
<body>
<div id="wrapper">
<div id="header">
	<div id="logo" style='border-bottom: 1px solid #ccc'>
		<br>
		<h1>GeFoPro - <?=$traductions['sct'][$_SESSION['user_lang']]?> <?=$scturl?></h1>
	</div>
</div>
<div id="page">
<form action="index.php"  id='myForm' METHOD="POST">
<div id='corners' style='width: 500px'>
<div id='legend'><?=$traductions['conn'][$_SESSION['user_lang']]?></div><br>
  <table border="0" align="center">
  <tr><td colspan=2><b><?=$loginMsg?></b></td></tr>

  <tr><td colspan=2>&nbsp;</td></tr>
  <tr><td><?=$traductions['user'][$_SESSION['user_lang']]?>:</td><td><input type="texte" name="login" value=""></td></tr>
  <tr><td><?=$traductions['passwd'][$_SESSION['user_lang']]?>:</td><td><input type="password" name="mdp" value=""></td></tr>
<?php 
	if(!empty($configurationLAG)) {
		echo "<tr><td>".$traductions['lang'][$_SESSION['user_lang']].":</td><td>";
		echo "<select name='lang' onChange='location.href=\"index.php?lang=\"+this.value'>";
		foreach($configurationLAG as $langsel) {
			echo "<option value='".$langsel."' ";
			if($langsel===$_SESSION['user_lang']) echo " selected";
			echo " >".$langsel."</option>";
		}
		echo "</select></td></tr>";
	} 
?>	
  <tr><td colspan=2>&nbsp;</td></tr>
  <tr><td>&nbsp;</td><td><input type="submit" name="submit" value="Login"></td></tr>
  <? if(empty($_SERVER['HTTPS'])) { ?>
  <!--  tr><td>&nbsp;</td></tr>
  <tr><td colspan=2><b><font color='red'>Attention: le lien utilisé n'est pas sécurisé! Veuillez utiliser de préférence ce lien-ci: <a href='https://<?=$_SERVER['SERVER_NAME']?>/<?=$scturl?>'>https://<?=$_SERVER['SERVER_NAME']?><?=$_SERVER['REQUEST_URI']?></a>. </font></b></td></tr -->
  <? } ?>
  </table>
<script language="javascript">document.getElementsByName("login")[0].focus();</script>
</form>
</div></div>


<?php include("piedPage.php"); ?>
