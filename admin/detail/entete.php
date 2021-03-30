<?php
# @Author: David Girardin <degehi>
# @Date:   19.03.2021 11:03:56
# @Email:  david.girardin@gefopro.ch
# @Project: GeFoPro
# @Filename: entete.php
# @Last modified by:   degehi
# @Last modified time: 30.03.2021 15:03:83
# @License: GPL-3.0 License, please refer to LICENSE file included to this package
# @Copyright: GeFoPro, 2010

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
<title><?=$app_section?> - Gestion atelier</title>
<meta name="keywords" content="" />
<meta name="description" content="" />
<link href="<?=$_SESSION['home']?>default.css?rdn=123" rel="stylesheet" type="text/css" />
<link rel="icon" href='<?=$_SESSION['home'].Logo?>' type="image/x-icon" />
<link rel="shortcut icon" href='<?=$_SESSION['home'].Logo?>' type="image/x-icon" />
<script>
var toggle_ctrl;
function readKey(event) {
	var x = event.charCode || event.keyCode;  // Get the Unicode value
	var ref;
	if(x==37 && event.ctrlKey) {
		ref = document.getElementById("prev");
		ref.click();
		//window.location=ref.href;
	} else if(x==39 && event.ctrlKey){
		ref = document.getElementById("next");
		ref.click();
		//window.location=ref.href;
	} else if(x==38 && event.ctrlKey){
		ref = document.getElementById("down");
		ref.click();
	} else if(x==40 && event.ctrlKey){
		ref = document.getElementById("up");
		ref.click();
	}
}
</script>
</head>
<body onkeydown="readKey(event);" >

<script type="text/javascript" src="/js/wz_tooltip.js"></script>
<script type="text/javascript" src="/js/msg.js"></script>
<link rel="stylesheet" media="all" href="/js/msg.css" />

<div id="wrapper">

<div id="header">
	<div id="logo">
		<br>
		<table border='0' width="100%"><tr><td>
		<?php if(hasAdminRigth()) { ?>
		<h1><?=$app_section?> - Gestion atelier</h1>
		<?php } else { ?>
		<h1><?=$app_section?> - Gestion personnelle</h1>
		<?php } ?>
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
			<?php if(hasAdminRigth()) {
				if(empty($from)) $from="";
				 ?>
				<?php if($from=="journaux") { ?>
				<li class="current_page_item"><a href="<?=$_SESSION['home']?>admin/listes/journaux.php">Retour journaux</a></li>
				<?php } else if($from=="theme") { ?>
				<li class="current_page_item"><a href="<?=$_SESSION['home']?>admin/detail/themes.php?nom=<?=$nom?>&prenom=<?=$prenom?>&idEleve=<?=$IDEleve?>&IDTheme=<?=$IDTheme?>">Retour thème</a></li>
				<?php } else { ?>
				<li class="current_page_item"><a href="<?=$_SESSION['home']?>admin/listes/atelier.php?modeHTML">Retour liste élèves</a></li>
				<?php } ?>
			<li class="current_page_item"><a href="<?=$_SESSION['home']?>admin/detail/detailEleve.php?nom=<?=$nom?>&prenom=<?=$prenom?>&idEleve=<?=$IDEleve?>">Fiche élève</a></li>
			<li class="current_page_item"><a href="<?=$_SESSION['home']?>admin/detail/docEleveCIE.php?nom=<?=$nom?>&prenom=<?=$prenom?>&idEleve=<?=$IDEleve?>">CIE</a></li>
			<!-- li class="current_page_item"><a href="<?=$_SESSION['home']?>admin/detail/themes.php?nom=<?=$nom?>&prenom=<?=$prenom?>&idEleve=<?=$IDEleve?>">Thèmes</a></li -->
			<!-- li class="current_page_item"><a href="<?=$_SESSION['home']?>admin/detail/suiviEleve.php?nom=<?=$nom?>&prenom=<?=$prenom?>&idEleve=<?=$IDEleve?>">Suivi</a></li -->
			<li class="current_page_item"><a href="<?=$_SESSION['home']?>admin/detail/activites.php?nom=<?=$nom?>&prenom=<?=$prenom?>&idEleve=<?=$IDEleve?>">Suivi de formation</a></li>
			<li class="current_page_item"><a href="<?=$_SESSION['home']?>admin/detail/notesEleve.php?nom=<?=$nom?>&prenom=<?=$prenom?>&idEleve=<?=$IDEleve?>">Notes</a></li>
			<?php } else { ?>
			<li class="current_page_item"><a href="<?=$_SESSION['home']?>admin/detail/infoEleve.php?nom=<?=$nom?>&prenom=<?=$prenom?>&idEleve=<?=$IDEleve?>">Info personnelles</a></li>
			<li class="current_page_item"><a href="<?=$_SESSION['home']?>admin/detail/docEleveCIE.php?nom=<?=$nom?>&prenom=<?=$prenom?>&idEleve=<?=$IDEleve?>">Cours interentreprises</a></li>
			<li class="current_page_item"><a href="<?=$_SESSION['home']?>admin/detail/activites.php?nom=<?=$nom?>&prenom=<?=$prenom?>&idEleve=<?=$IDEleve?>&vue=1">Mon suivi de formation</a></li>
			<!-- li class="current_page_item"><a href="<?=$_SESSION['home']?>admin/detail/evaluations.php?nom=<?=$nom?>&prenom=<?=$prenom?>&idEleve=<?=$IDEleve?>">Auto-évaluations</a></li -->
			<!-- li class="current_page_item"><a href="<?=$_SESSION['home']?>admin/detail/impressionJournal.php?nom=<?=$nom?>&prenom=<?=$prenom?>&idEleve=<?=$IDEleve?>">Imprimer</a></li -->
			<!-- li class="current_page_item"><a href="<?=$_SESSION['home']?>admin/detail/evalCoursCIE.php?nom=<?=$nom?>&prenom=<?=$prenom?>&idEleve=<?=$IDEleve?>">CIE</a></li -->
			<?php  } ?>
			<li class="last"><a href="<?=$_SESSION['home']?>index.php?logout=out">Déconnecter</a></li>
			<?php if(hasAdminRigth()) { ?>
				<li class='last'>&nbsp;</li><li class='context'><a href="<?=$_SESSION['home']?>admin/detail/activites.php?nom=<?=$nom?>&prenom=<?=$prenom?>&idEleve=<?=$IDEleve?>&vue=app">Vue apprenti</a></li>
			<?php  } ?>

		</ul>

	</div>
</div>
