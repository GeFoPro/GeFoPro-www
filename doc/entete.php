<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
<title><?=$app_section?> - Gestion des documents</title>
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
		<h1><?=$app_section?> - Gestion des documents</h1>
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
			<li class="current_page_item"><a href="<?=$_SESSION['home']?>doc/dossiers.php?cat=<?=CatAtelier?>">Cours Atelier</a></li>
			<?php if(hasAdminRigth()) { ?>
			<li class="current_page_item"><a href="<?=$_SESSION['home']?>doc/listeCours.php">Liste des cours</a></li>
			<?php } ?>
			<li class="current_page_item"><a href="<?=$_SESSION['home']?>doc/dossiers.php?cat=<?=CatElectro?>">Références</a></li>
			<!--li class="current_page_item"><a href="<?=$_SESSION['home']?>doc/dossiers.php?cat=<?=CatInfo?>">Informatique</a></li-->
			<!--li class="current_page_item"><a href="<?=$_SESSION['home']?>doc/dossiers.php?cat=<?=CatSoft?>">Logiciels</a></li-->
			<li class="last"><a href="<?=$_SESSION['home']?>index.php?logout=out">Déconnecter</a></li>

		</ul>

	</div>
</div>
