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
ini_set( 'default_charset', "iso-8859-1" );
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
<title><?=$app_section?> - <?=libelleTrad('gestionatelier')?></title>
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
function callPage(sel) {
	document.location.href='<?=$_SESSION['home']?>/../../'+sel.value+'/';
}
</script>
</head>
<body onkeydown="readKey(event);" >
<script type="text/javascript" src="/js/traduction.js"></script>
<script type="text/javascript" src="/js/wz_tooltip.js"></script>
<script type="text/javascript" src="/js/msg.js"></script>
<link rel="stylesheet" media="all" href="/js/msg.css" />

<div id="wrapper">

<div id="header">
	<div id="logo">
		<br>
		<table border='0' width="100%"><tr><td>
		<h1>
		<?php
			if(!empty($configurationAPP)&&hasAdminRigth()) {
				echo "<select name='app' id='selapp' onChange='callPage(this)'>";
				foreach($configurationAPP as $app_name) {
					echo "<option value='".$app_name."' ";
					if($app_name===$app_section) echo " selected";
					echo " id='selapp'>".$app_name."</option>";
				}
				echo "</select>";
			} else {
					echo $app_section;
			}
		?>
		<?php if(hasAdminRigth()) { ?>
		 - <?=libelleTradUpdAll('gestionatelier')?>
		<?php } else { ?>
		 - <?=libelleTradUpdAll('gestionpersonnelle')?>
		<?php } ?>
		</h1></td>
		<td align="right"><a href='<?=$_SESSION['home']?>comp/compList.php'><?=libelleTrad('menuconso')?></a>
		<br><a href='<?=$_SESSION['home']?>doc/dossiers.php'><?=libelleTrad('menudoc')?></a>
		<?php if(hasAdminRigth()) { ?>
			<br><a href='<?=$_SESSION['home']?>admin/listes/atelier.php?modeHTML'><?=libelleTrad('gestionatelier')?></a>
		<?php } else { ?>
			<br><a href="<?=$_SESSION['home']?>admin/detail/activites.php?nom=<?=$nom?>&prenom=<?=$prenom?>&idEleve=<?=$IDEleve?>"><?=libelleTrad('gestionpersonnelle')?></a>
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
				<li class="current_page_item"><a href="<?=$_SESSION['home']?>admin/listes/journaux.php"><?=libelleTrad('retjournaux')?></a></li>
				<?php } else if($from=="theme") { ?>
				<!--li class="current_page_item"><a href="<?=$_SESSION['home']?>admin/detail/themes.php?nom=<?=$nom?>&prenom=<?=$prenom?>&idEleve=<?=$IDEleve?>&IDTheme=<?=$IDTheme?>">Retour th�me</a></li -->
				<?php } else { ?>
				<li class="current_page_item"><a href="<?=$_SESSION['home']?>admin/listes/atelier.php?modeHTML"><?=libelleTrad('reteleves')?></a></li>
				<?php } ?>
			<li class="current_page_item"><a href="<?=$_SESSION['home']?>admin/detail/detailEleve.php?nom=<?=$nom?>&prenom=<?=$prenom?>&idEleve=<?=$IDEleve?>"><?=libelleTrad('fiche')?></a></li>
			<li class="current_page_item"><a href="<?=$_SESSION['home']?>admin/detail/docEleveCIE.php?nom=<?=$nom?>&prenom=<?=$prenom?>&idEleve=<?=$IDEleve?>"><?=libelleTrad('menucie')?></a></li>
			<!-- li class="current_page_item"><a href="<?=$_SESSION['home']?>admin/detail/themes.php?nom=<?=$nom?>&prenom=<?=$prenom?>&idEleve=<?=$IDEleve?>">Th�mes</a></li -->
			<!-- li class="current_page_item"><a href="<?=$_SESSION['home']?>admin/detail/suiviEleve.php?nom=<?=$nom?>&prenom=<?=$prenom?>&idEleve=<?=$IDEleve?>">Suivi</a></li -->
			<li class="current_page_item"><a href="<?=$_SESSION['home']?>admin/detail/activites.php?nom=<?=$nom?>&prenom=<?=$prenom?>&idEleve=<?=$IDEleve?>"><?=libelleTrad('suiviform')?></a></li>
			<li class="current_page_item"><a href="<?=$_SESSION['home']?>admin/detail/notesEleve.php?nom=<?=$nom?>&prenom=<?=$prenom?>&idEleve=<?=$IDEleve?>"><?=libelleTrad('menunotes')?></a></li>
			<li class="current_page_item"><a href="<?=$_SESSION['home']?>admin/detail/theorie.php?nom=<?=$nom?>&prenom=<?=$prenom?>&idEleve=<?=$IDEleve?>"><?=libelleTrad('menutheorie')?></a></li>
			<?php } else { ?>
			<li class="current_page_item"><a href="<?=$_SESSION['home']?>admin/detail/infoEleve.php?nom=<?=$nom?>&prenom=<?=$prenom?>&idEleve=<?=$IDEleve?>"><?=libelleTrad('infopers')?></a></li>
			<li class="current_page_item"><a href="<?=$_SESSION['home']?>admin/detail/docEleveCIE.php?nom=<?=$nom?>&prenom=<?=$prenom?>&idEleve=<?=$IDEleve?>"><?=libelleTrad('coursinter')?></a></li>
			<li class="current_page_item"><a href="<?=$_SESSION['home']?>admin/detail/activites.php?nom=<?=$nom?>&prenom=<?=$prenom?>&idEleve=<?=$IDEleve?>&vue=1"><?=libelleTrad('suiviform')?></a></li>
			<li class="current_page_item"><a href="<?=$_SESSION['home']?>admin/detail/theorie.php?nom=<?=$nom?>&prenom=<?=$prenom?>&idEleve=<?=$IDEleve?>"><?=libelleTrad('menutheorieapp')?></a></li>
			<!-- li class="current_page_item"><a href="<?=$_SESSION['home']?>admin/detail/evaluations.php?nom=<?=$nom?>&prenom=<?=$prenom?>&idEleve=<?=$IDEleve?>">Auto-�valuations</a></li -->
			<!-- li class="current_page_item"><a href="<?=$_SESSION['home']?>admin/detail/impressionJournal.php?nom=<?=$nom?>&prenom=<?=$prenom?>&idEleve=<?=$IDEleve?>">Imprimer</a></li -->
			<!-- li class="current_page_item"><a href="<?=$_SESSION['home']?>admin/detail/evalCoursCIE.php?nom=<?=$nom?>&prenom=<?=$prenom?>&idEleve=<?=$IDEleve?>">CIE</a></li -->
			<?php  } ?>
			<li class="last"><a href="<?=$_SESSION['home']?>index.php?logout=out"><?=libelleTrad('menulogout')?></a></li>
			<?php if(hasAdminRigth()) { ?>
				<li class='last'>&nbsp;</li><li class='context'><a href="<?=$_SESSION['home']?>admin/detail/activites.php?nom=<?=$nom?>&prenom=<?=$prenom?>&idEleve=<?=$IDEleve?>&vue=app"><?=libelleTrad('vueapp')?></a></li>
			<?php  } ?>

		</ul>

	</div>
</div>
