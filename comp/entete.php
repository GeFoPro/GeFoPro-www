<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
<title><?=$app_section?> - Gestion du consommable et équipement</title>
<meta name="keywords" content="" />
<meta name="description" content="" />
<link href="/<?=$app_section?>/default.css" rel="stylesheet" type="text/css" />
<link rel="icon" href='/<?=$app_section."/".Logo?>' type="image/x-icon" />
<link rel="shortcut icon" href='/<?=$app_section."/".Logo?>' type="image/x-icon" />

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
		<td align="right"><a href='/<?=$app_section?>/comp/compList.php'>Gestion du consommable et équipement</a>
		<br><a href='/<?=$app_section?>/doc/dossiers.php'>Gestion des documents</a>
		<?php if(hasAdminRigth()) { ?>
			<br><a href='/<?=$app_section?>/admin/listes/atelier.php?modeHTML'>Gestion de l'atelier</a>
		<?php } else { ?>
			<br><a href="/<?=$app_section?>/admin/detail/activites.php?nom=<?=$nom?>&prenom=<?=$prenom?>&idEleve=<?=$IDEleve?>">Gestion personnelle</a>
		<?php } ?>
		<?php if(!empty($teamsURL)) { ?>
			<br><a href='<?=$teamsURL?>' target='teams'>Teams atelier</a>
		<?php } ?>
		</td></tr></table>
		
	</div>
	<div id="menu">
		<ul>
			<li class="current_page_item"><a href="compList.php">Articles</a></li>
			<li class="current_page_item"><a href="commande.php">Commandes en cours</a></li>
			<?php if(hasAdminRigth()) { ?>
			<li class="current_page_item"><a href="historique.php">Historique commandes</a></li>
			<li class="current_page_item"><a href="lists.php">Listes</a></li>
			<li class="current_page_item"><a href="etiquette.php" target="_pdf">Etiquettes</a></li>
			<?php } ?>
			<li class="last"><a href="/<?=$app_section?>/index.php?logout=out">Déconnecter</a></li>
		</ul>
	</div>
</div>
