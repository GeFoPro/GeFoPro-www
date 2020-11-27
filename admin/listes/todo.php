<?php 
include("../../appHeader.php");

if(isset($_POST['ajoutTodo'])) {
	// todo recherche new ID
	$libelle = $_POST['Libelle'];
	$IDProf = $_POST['IDProf'];
	if(empty($IDProf)) {
		$IDProf = 0;
	}
	$date = date("Y-m-d");
	// ajout d'un attribut
	if(empty($_POST['Delai'])) {
		$requete = "INSERT INTO todo (dateCreation, Libelle, IDProf, IDStatus) values (\"$date\", \"$libelle\", $IDProf, 1)";
	} else {
		$delai = date("Y-m-d",strtotime($_POST['Delai']));
		$requete = "INSERT INTO todo (dateCreation, Libelle, Delai, IDProf, IDStatus) values (\"$date\", \"$libelle\", \"$delai\", $IDProf, 1)";
	}
    $resultat =  mysql_query($requete);
}
if(isset($_GET['action'])) {
	// effacement d'une ligne
	if($_GET['action'] == "delete" && isset($_GET['IDTodo'])) {
		$requete = "DELETE FROM todo where IDTodo=$_GET[IDTodo]";
		mysql_query($requete);
	}

	// prise en charge
	if($_GET['action'] == "charge" && isset($_GET['IDTodo'])) {
		// recherche de l'id prof
		$requete = "select IDProf from prof where userid = '".$_SESSION['user_id']."'";
		$resultat =  mysql_query($requete);
		$ligne = mysql_fetch_assoc($resultat);
		$requete = "update todo set IDStatus=2, IDProf=".$ligne['IDProf']." where IDTodo=".$_GET['IDTodo'];
		mysql_query($requete);
	}

	// refuser
	if($_GET['action'] == "annule" && isset($_GET['IDTodo'])) {
		$requete = "update todo set IDStatus=4 where IDTodo=".$_GET['IDTodo'];
		mysql_query($requete);
	}

	// terminer
	if($_GET['action'] == "accept" && isset($_GET['IDTodo'])) {
		$requete = "update todo set IDStatus=3 where IDTodo=".$_GET['IDTodo'];
		mysql_query($requete);
	}
}
$tri = "1,2";
if(isset($_POST['tri'])) {
	$tri = $_POST['tri'];
}
include("entete.php");
?>

<div id="page">
<script>
function toggle(thisname) {

	tr=document.getElementsByTagName('tr')
	for (i=0;i<tr.length;i++){
		if (tr[i].getAttribute(thisname)){
			if ( tr[i].style.display=='none' ){
				tr[i].style.display = '';
			} else {
				tr[i].style.display = 'none';
			}
		}
	}
}
</script>
<?
include($app_section."/userInfo.php");
/* en-tête */

echo "<FORM id='myForm' ACTION='todo.php'  METHOD='POST'>";
// transfert info

echo "<div class='post'>";
echo "<br><table width='100%' border='0'><tr><td><!-- h2>ToDo</h2 --></td><td align='right'>Tri: <select name='tri' onChange='submit()'>";
echo "<option value='1,2'";
if("1,2"==$tri) {
	echo " selected";
}
echo ">En cours</option>";
echo "<option value='3,4'";
if("3,4"==$tri) {
	echo " selected";
}
echo ">Terminés</option></select></td></tr></table><br>\n";
echo "<div id='corners'>";
echo "<div id='legend'>ToDo</div>";
echo "<table id='hor-minimalist-b' width='100%'>\n";
echo "<tr><th width='100'>Date</th><th width='470'>Texte</th><th width='100' align='center'>Délai</th><th width='100' align='center'>Responsable</th><th width='100' align='center'>Status</th><th></th></tr>";
// recherche des todos
$requete = "SELECT * FROM todo tod left outer join prof pr on tod.IDProf=pr.IDProf join status st on tod.IDStatus=st.IDStatus where tod.IDStatus in (".$tri.") order by tod.IDStatus, dateCreation desc";
$resultat =  mysql_query($requete);
$cnt=0;
while ($ligne = mysql_fetch_assoc($resultat)) {
	if($ligne['delai']==0) {
		$delaistr = "Aucun";
	} else {
		$delaistr = date('d.m.Y', strtotime($ligne['delai']));
	}
	echo "<tr><td valign='top'>".date('d.m.Y', strtotime($ligne['dateCreation']))."</td><td valign='top' width='470'>".nl2br($ligne['libelle'])."</td><td align='center' valign='top'>".$delaistr."</td><td align='center' valign='top'>".$ligne['Nom']."</td><td align='center' valign='top'>".$ligne['Etat']."</td><td valign='top'>";
	if($ligne['IDStatus']==1 || ($ligne['IDStatus']==2 && strtolower($_SESSION['user_id']) != $ligne['userid'])) {
		echo "<a href='todo.php?IDTodo=$ligne[IDTodo]&action=charge'><img src='/iconsFam/user_add.png' align='absmiddle' onmouseover=\"Tip('Prendre en charge la tâche')\" onmouseout='UnTip()'></a> ";
	} else {
		echo "<img src='/iconsFam/empty.png' align='absmiddle'> ";
	}
	//echo strtolower($_SESSION['user_id']." - ".$ligne['userid']);
	if($ligne['IDStatus']==2 && strtolower($_SESSION['user_id']) == $ligne['userid']) {
		echo "<a href='todo.php?IDTodo=$ligne[IDTodo]&action=accept'><img src='/iconsFam/accept.png' align='absmiddle' onmouseover=\"Tip('Tâche terminée')\" onmouseout='UnTip()'></a> ";
		echo "<a href='todo.php?IDTodo=$ligne[IDTodo]&action=annule'><img src='/iconsFam/delete.png' align='absmiddle' onmouseover=\"Tip('Annuler la tâche')\" onmouseout='UnTip()'></a> ";
	} else {
		echo "<img src='/iconsFam/empty.png' align='absmiddle'> ";
		echo "<img src='/iconsFam/empty.png' align='absmiddle'> ";
	} 
	if($ligne['IDStatus']==1 || $ligne['IDStatus']==3 ||  $ligne['IDStatus']==4) {
		echo "<a href='todo.php?IDTodo=$ligne[IDTodo]&action=delete'><img src='/iconsFam/table_row_delete.png' align='absmiddle' onmouseover=\"Tip('Supprimer cette ligne')\" onmouseout='UnTip()'></a>";
	}
	
	echo "</td></tr>";
	$cnt++;
}
if ($cnt==0) {
	echo "<tr><td colspan='6' align='center'><i>Aucun enregistrement</i></td></tr>";
} 
// ligne d'ajout
$requete = "SELECT * FROM prof";
$resultat =  mysql_query($requete);
$optionProf = "<option value=''></option>";
while ($ligne = mysql_fetch_assoc($resultat)) {
	$optionProf .= "<option value=".$ligne['IDProf'].">".$ligne['Nom']."</option>";
}

echo "<tr><td colspan='6' bgColor='#5C5C5C'></td></tr>";
echo "<tr newTodo='1'><td colspan='5'></td><td align='right'><img src='/iconsFam/add.png' onmouseover=\"Tip('Ajouter une tâche')\" onmouseout='UnTip()' onclick='toggle(\"newTodo\");' align='absmiddle'></td></tr>";
echo "<tr newTodo='1' style='display:none'><td colspan='6' valign='bottom' height='30'><b>Nouvelle entrée:<b></td></tr>";
echo "<tr newTodo='1' style='display:none'><td></td>";
echo "<td valign='top'><textarea name='Libelle' COLS=60 ROWS=2></textarea></td>";
echo "<td valign='top' align='center'><input name='Delai' size='8' maxlength='10' value=''></input></td>";
echo "<td valign='top' align='center'><select name='IDProf'>".$optionProf."</select></td>";
//echo "<tr><td></td>";
echo "<td valign='top' colspan='2' align='right'><input type='submit' name='ajoutTodo' value='Ajouter'></input></td><tr>";
echo "</table></div><br><br>";
 ?>

</div> <!-- post -->
</form>

</div> <!-- page -->

<?php include($app_section."/piedPage.php"); ?>
