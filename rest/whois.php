<?php
$scturl = strtoupper(substr($_SERVER['REQUEST_URI'],1,3));
require("Config_".$scturl.".php");
$id = "";
if(isset($_GET['id'])) {
	$id = $_GET['id'];
}
if(isset($_POST['id'])) {
	$id = $_POST['id'];
}
$nom = "?";
$prenom = "?";
$connexion = connexionAdmin("localhost",DBUserAdmin,DBPwdAdmin);
mysql_select_db(DBAdmin);

// required headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods:  GET, PUT, POST, DELETE, HEAD");
header("Access-Control-Allow-Credentials: true");
header('Content-Type: application/json');



//echo "select nom,prenom from eleves as el join elevesbk as elbk on el.IDGDN = elbk.IDGDN where Userid = ".$id;
$result = mysql_query("select nom,prenom from eleves as el join elevesbk as elbk on el.IDGDN = elbk.IDGDN where Userid = '".$id."'");
if($result!=null && !empty($result)) {
	if(mysql_num_rows($result)==1) {
		$user = mysql_fetch_assoc($result);
		$nom = $user['nom'];
		//echo " - nom: ".$nom;
		$prenom = $user['prenom'];
		// set response code - 200 OK
		http_response_code(200);
		echo $nom." ".$prenom;
	} else {
		// not found or multiple results
		http_response_code(404);
	}
}


//header("Access-Control-Allow-Origin: *");
//header("Access-Control-Allow-Credentials: true");
//header("Access-Control-Max-Age: 1000");
//header("Access-Control-Allow-Headers: X-Requested-With, Content-Type, Origin, Cache-Control, Pragma, Authorization, Accept, Accept-Encoding");
//header("Access-Control-Allow-Methods: PUT, POST, GET, OPTIONS, DELETE");


?>