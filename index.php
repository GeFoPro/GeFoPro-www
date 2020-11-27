<?php
session_start();
//if (isset($_SESSION['login'])) {
//	$login = $_SESSION['login'];
//} 
$scturl = strtoupper(substr($_SERVER['REQUEST_URI'],1,3));
require("Config_".$scturl.".php");
checkEOL();

function printLDAPInfo($ldap_cnx,$user_login) {
	//Récupération des informations de l'utilisateur depuis AD
	$msg = "Information LDAP de l'utilisateur $user_login: <br>";
	if (($search=ldap_search($ldap_cnx, AD_DN_PERSONNEL, "sAMAccountName=".$user_login)) && (ldap_count_entries($ldap_cnx,$search)>0)) {
		$msg = $msg . "Groupe Personnel<br>";
	} else if (($search=ldap_search($ldap_cnx, AD_DN_APPRENANTS, "sAMAccountName=".$user_login)) && (ldap_count_entries($ldap_cnx,$search)>0)) {
		$msg = $msg . "Groupe Apprentis<br>";
	} else if (($search=ldap_search($ldap_cnx, AD_DN_ADMINISTRATION, "sAMAccountName=".$user_login)) && (ldap_count_entries($ldap_cnx,$search)>0)) {
		$msg = $msg . "Groupe Administration<br>";
	}
	
	if (ldap_count_entries($ldap_cnx,$search)>0) {
		$entries= ldap_get_entries($ldap_cnx,$search);
		//foreach($entries[0] as $pos => $value) {
		//	echo $pos . " -> " . $value . "<br>";
		//}
		$msg = $msg . "Nom: ". $entries[0]["displayname"][0]. "<br>";
		$msg = $msg . "Decription: ". $entries[0]["description"][0]. "<br>";
		$memberof = $entries[0]["memberof"];
		$msg = $msg . "Membre de:<br><ul>";
		for($i=0;$i<$memberof["count"];$i++) {
			//foreach($entrie[0]["memberof"] as $pos => $value) {
			$msg = $msg . "<li>". $memberof[$i]."</li>";
		}
		$msg = $msg . "</ul>";
	}
	//$msg .= $_SESSION['sct']." - ".$_SESSION['login']." - ".$_SESSION['user_type'];
	return $msg;
}
$loginMsg = "";
if(isset($_GET['logout'])) {
	// effacer la session
	$loginMsg = "Déconnecté avec succès";
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
		$loginMsg = "Saisissez le nom d'utilisateur et le mot de passe (domaine ".AD_DOMAIN_NAME.")";
	} else {
		// Tentative de connexion au serveur LDAP
		if (!($ldap_cnx=ldap_connect(AD_SERVER))) {
			//session_destroy();
			$loginMsg = "Connexion au serveur de domaine impossible !";
		} else {
			// Tentative d'authentification au serveur LDAP
			if (!($bind=ldap_bind($ldap_cnx, AD_DOMAIN_NAME."\\".$user_login, $user_mdp))) {
				//session_destroy();
				$loginMsg = "Utilisateur ou mot de passe incorrect !";
			}  else {
				// test droits
				readLDAP($ldap_cnx,$user_login);

				//Test si l'utilisateur à au moins le droit de lecture (APP ou MAI avec droits)
				if(!isAPP()&&!hasReadRigth()) {
					//Droits de connxion insuffisant
					$loginMsg ="L'utilisateur ".$user_login." n'a pas les droits nécessaires pour cette application!<br>";
					$loginMsg = $loginMsg . printLDAPInfo($ldap_cnx,$user_login);
				} else {
					// test de connexion avec l'utilisateur DB récupéré
					$connexion = connexionAdmin($serveur,$_SESSION['login'],$_SESSION['mdp']);
					if(!isset($connexion)) {
						//DB inaccessible
						//session_destroy();
						$loginMsg ="Connexion à la base de donnée impossible ou manque de droits!";
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
	}	
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
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
		<h1>Section <?=$scturl?></h1>
	</div>
</div>
<div id="page">
<form action="index.php"  METHOD="POST">
<div id='corners' style='width: 500px'>
<div id='legend'>Connexion</div><br>
  <table border="0" align="center">
  <tr><td colspan=2><b><?=$loginMsg?></b></td></tr>
  
  <tr><td colspan=2>&nbsp;</td></tr>
  <tr><td>Utilisateur:</td><td><input type="texte" name="login" value=""></td></tr>
  <tr><td>Mot de passe:</td><td><input type="password" name="mdp" value=""></td></tr>
  <tr><td colspan=2>&nbsp;</td></tr>
  <tr><td>&nbsp;</td><td><input type="submit" name="submit" value="Login"></td></tr>
  <? if(empty($_SERVER['HTTPS'])) { ?>
  <tr><td>&nbsp;</td></tr>
  <tr><td colspan=2><b><font color='red'>La connexion à ce server est dorénavant sécurisée. Pour y accéder, veuillez utiliser dès à présent l'adresse <a href='https://ateliers.divtec.ch/<?=$scturl?>'>https://ateliers.divtec.ch/<?=$scturl?></a>. Attention, l'ancienne adresse sera très bientôt désactivée!</font></b></td></tr>
  <? } ?>
  </table>
<script language="javascript">document.getElementsByName("login")[0].focus();</script>  
</form>
</div>


<?php include("piedPage.php"); ?>
