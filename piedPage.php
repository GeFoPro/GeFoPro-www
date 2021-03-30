<?php
# @Author: David Girardin <degehi>
# @Date:   19.03.2021 11:03:09
# @Email:  david.girardin@gefopro.ch
# @Project: GeFoPro
# @Filename: piedPage.php
# @Last modified by:   degehi
# @Last modified time: 30.03.2021 13:03:58
# @License: GPL-3.0 License, please refer to LICENSE file included to this package
# @Copyright: GeFoPro, 2010
	$release = "<br><b><i>2.5</i></b>";
	$release .= "<br>- migration vers php7 (utilisation de mysqli)";
	$release .= "<br>- corrections pour fonctionnement sur syst�mes linux (LAMP)";
	$release .= "<br>- param�trage am�lior� pour utilisation de LDAP";
	$release .= "<br>- possibilit� d\'installer l\'application dans une sous structure web";
	$release .= "<br><b><i>2.4.3</i></b>";
	$release .= "<br>Fonctionnalit�s:";
	$release .= "<br>- Textes pour commandes et �tiquettes s�par�s";
	$release .= "<br>- Possibilit� d\'utiliser qu\'un seul champ pour le texte des commandes (s�lectionner deux fois la m�me ligne)";
	$release .= "<br>- Possibilit� de valider une �valuation avec seulement un commentaire dans les observations";
	$release .= "<br><b><i>2.4.2</i></b>";
	$release .= "<br>Bugs:";
	$release .= "<br>- Texte de la ligne de commande coup� si pr�sence d\'apostrophes dans les champs de l\'article";
	$release .= "<br><b><i>2.4.1</i></b>";
	$release .= "<br>Fonctionnalit�s:";
	$release .= "<br>- Possibilit� d\'ajouter ou retourner manuellement un pr�t dans la liste des pr�ts";
	$release .= "<br><b><i>2.4</i></b>";
	$release .= "<br>Fonctionnalit�s: Gestion des pr�ts de mat�riel";
	$release .= "<br>- Gestion de la liste de num�ros d\'inventaire et de s�rie par article";
	$release .= "<br>- Attribution d\'un identifiant RFID par article ou pour l\'ensemble d\'un m�me type d\'article, soit dans l\'inventaire (un par article),";
	$release .= "<br>&nbsp;&nbsp; soit li� au stockage de l\'article (pour l\'ensemble du m�me type d\'articles)";
	$release .= "<br>- Gestion des pr�ts d\'articles, avec historique";
	$release .= "<br>- Liste des pr�ts en cours, par personne ou par emplacement de stockage";
	$release .= "<br>- API REST pour l\'interrogation, le pr�t et le retour d\'un article";
	$release .= "<br><b><i>2.3.3</i></b>";
	$release .= "<br>Fonctionnalit�s:";
	$release .= "<br>- Possibilit� de valider une �valuation hebdomadaire/de th�me, m�me si celle-ci n\'est pas compl�te";
	$release .= "<br>- Proposition automatique de texte dans la mise des notes, en fonction de ce qui a �t� saisi dans les �valuations";
	$release .= "<br><b><i>2.3.2</i></b>";
	$release .= "<br>Fonctionnalit�s:";
	$release .= "<br>- Int�gration d\'un lien sur Teams";
	$release .= "<br><b><i>2.3.1</i></b>";
	$release .= "<br>Fonctionnalit�s:";
	$release .= "<br>- Gestion des projets am�lior�e afin de faciliter les op�rations et attributions sur ceux-ci";
	$release .= "<br>- Possibilit� d\'attribuer directement une classe enti�re � un projet";
	$release .= "<br><b><i>2.3</i></b>";
	$release .= "<br>Fonctionnalit�s (gestion des composants):";
	$release .= "<br>- Possibilit� d\'ajouter des images pour un article, un type de boitier ou une cat�gorie (genre/type)";
	$release .= "<br>- Regroupement des articles en doublons pour une commande et tri selon num�ro d\'article";
	$release .= "<br>- Intitul� automatique de l\'article command� selon la m�me configuration de l\'impression des �tiquettes";
	$release .= "<br>- Pour un article, ajout d\'un nouveau champ de quantit� en stock minimum ainsi qu\'un champ de quantit� de commande";
	$release .= "<br>- Affichage du montant total par commande avec la TVA";
	$release .= "<br><b><i>2.2.2</i></b>";
	$release .= "<br>Bugs:";
	$release .= "<br>- Saisie des journaux le vendredi impossible, apr�s passage � l\'heure d\'hiver";
	$release .= "<br><b><i>2.2.1</i></b>";
	$release .= "<br>Fonctionnalit�s:";
	$release .= "<br>- Affichage des journaux avec tri par th�me ou par jour";
	$release .= "<br>- Correctif sur le calcul de la moyenne des �valuations pour la proposition des notes de semestre";
	$release .= "<br><b><i>2.2</i></b>";
	$release .= "<br>Fonctionnalit�s:";
	$release .= "<br>- Saisie des journaux de travail par tranche horaire ou dur�e";
	$release .= "<br>- Impression du suivi en PDF";
	$release .= "<br>- Navigation am�lior�e avec les vues th�mes/semaine dans les journaux et suivis";
	$release .= "<br><b><i>2.1</i></b>";
	$release .= "<br>Fonctionnalit�s:";
	$release .= "<br>- Ajout attribut Stage pour apprentis (hors listes t�ches et suivis)";
	$release .= "<br>- Apprentis externes hors liste navigation (fl�ches bleues) ";
	$release .= "<br>- Navigation avec les f�ches bleues (apprentis/semaines) possible avec les fl�ches du clavier (et touche CTRL appuy�e)";
	$release .= "<br>- Les projets peuvent �tre associ�s � tout apprenti et non plus uniquement que les 3�me et 4�me ann�es";
	$release .= "<br>- Modification possible de la pond�ration d\'un th�me dans la vue des notes de l\'apprenti (clic sur pond�ration)";
	$release .= "<br>Bugs:";
	$release .= "<br>- Passage � la semaine no 1 n\'incr�mente par l\'ann�e correctement";
	$release .= "<br>- Impression en PDF des �valuations uniquement si valid�es";
	$release .= "<br>- Probl�me de cr�ation globale des p�riodes pour les notes, pour le syst�me d\'�valuation hebdomadaire";
?>
<div id="footer">
	<p id="legal" onmouseover="Tip('<b>Release notes:</b><?=$release?>')" onmouseout="UnTip()">GeFoPro 2.5, 2010 &copy; Designed by DGI</p>

</div>
<!-- end footer -->
</div>

<!-- end wrapper -->
</body>
</html>
