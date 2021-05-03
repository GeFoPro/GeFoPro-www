<?php
# @Author: David Girardin <degehi>
# @Date:   19.03.2021 11:03:90
# @Email:  david.girardin@gefopro.ch
# @Project: GeFoPro
# @Filename: entete.php
# @Last modified by:   degehi
# @Last modified time: 30.03.2021 13:03:04
# @License: GPL-3.0 License, please refer to LICENSE file included to this package
# @Copyright: GeFoPro, 2010
ini_set( 'default_charset', "iso-8859-1" );
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
<title><?=$app_section?> - Gestion du consommable et équipement</title>
<meta name="keywords" content="" />
<meta name="description" content="" />
<link href="<?=$_SESSION['home']?>default.css" rel="stylesheet" type="text/css" />
<link rel="icon" href='<?=$_SESSION['home'].Logo?>' type="image/x-icon" />
<link rel="shortcut icon" href='<?=$_SESSION['home'].Logo?>' type="image/x-icon" />

</head>
<body>

<script type="text/javascript" src="/js/wz_tooltip.js"></script>
<script type="text/javascript" src="/js/msg.js"></script>
<link rel="stylesheet" media="all" href="/js/msg.css" />

<div id="wrapper">

<div id="header">
	<div id="logo">
		<br>
		<table border='0' width="100%"><tr><td>
		<h1><?=$app_section?> - Gestion du consommable et équipement</h1>
		</td>
		<td align="right"><a href='<?=$_SESSION['home']?>comp/compList.php'>Gestion du consommable et équipement</a>
		<br><a href='<?=$_SESSION['home']?>doc/dossiers.php'>Gestion des documents</a>
		<?php if(hasAdminRigth()) { ?>
			<br><a href='<?=$_SESSION['home']?>admin/listes/atelier.php?modeHTML'>Gestion de l'atelier</a>
		<?php } else { ?>
			<br><a href="<?=$_SESSION['home']?>admin/detail/activites.php?nom=<?=$nom?>&prenom=<?=$prenom?>&idEleve=<?=$IDEleve?>">Gestion personnelle</a>
		<?php } ?>
		<?php if(!empty($teamsURL)) { ?>
			<br><a href='<?=$teamsURL?>' target='teams'>Teams atelier</a>
		<?php } ?>
		</td></tr></table>

	</div>
	<div id="menu">
		<ul>
			<li class="current_page_item"><a href="compList.php">Articles</a></li>
			<li class="current_page_item"><a href="listePrets.php">Prêts</a></li>
			<li class="current_page_item"><a href="commande.php">Commandes en cours</a></li>
			<?php if(hasAdminRigth()) { ?>
			<li class="current_page_item"><a href="historique.php">Historique commandes</a></li>
			<li class="current_page_item"><a href="lists.php">Listes</a></li>
			<li class="current_page_item"><a href="etiquette.php" target="_pdf">Etiquettes</a></li>
			<?php } ?>
			<li class="last"><a href="<?=$_SESSION['home']?>index.php?logout=out">Déconnecter</a></li>
		</ul>
	</div>
</div>
