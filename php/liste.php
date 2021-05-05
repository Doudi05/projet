<?php

ob_start(); //démarre la bufferisation
session_start();
require_once 'bibli_generale.php';
require_once 'bibli_bookshop.php';

error_reporting(E_ALL); // toutes les erreurs sont capturées (utile lors de la phase de développement)

if (!isset($_SESSION['cliID'])){
    redirige('../index.php');
}

$idClient = $_SESSION['cliID'];
$clientConnecte = TRUE;//pour savoir s'il s'agit de la liste de l'utilisateur connecté ou s'il s'agit d'une recherche
$nbPage = 1;//Initialisation à la première page


if ($_GET){
	$nbPage = control_get_livre();
	$valueID = control_get_delete();
	delete($valueID);
}

if ($_POST){
	$valueMail = control_post_client();
	$idClient = verification_mail($valueMail);
}

em_aff_debut('BookShop | Liste de cadeaux', '../styles/bookshop.css', 'main');

em_aff_enseigne_entete();

if($idClient == -1){
	echo
		'<p class="error">',
			'Aucune adresse mail correspondant à votre recherche',
		'</p>';
	$idClient = $_SESSION['cliID'];
}
if($nbPage == 1){
	recup_liste($idClient);
}

if($idClient == $_SESSION['cliID']){
	echo '<h1>Votre liste de cadeaux</h1>';
}else{
	echo '<h1>Liste de voeux de votre recherche</h1>'; //on ne met pas le nom pour ne pas donner des informations sur nos utilisateurs.
	$clientConnecte = FALSE;//indique qu'il ne s'agit pas de la liste de cadeau de l'utilisateur connecté
}

afficher_liste($_SESSION['Liste'], 'Liste', '../', $nbPage, $clientConnecte, $idClient);

em_aff_pied();

em_aff_fin('main');

ob_end_flush();

// ---------- Fonctions ----------- //

/**
 *	Récupère la liste de voeux d'un client et la met dans la variable globale $_SESSION['Liste']
 *
 * 	@param		int		$idClient	l'identifiant du client connecté ou du client recherché
 *  @session  	array   $_SESSION
 */
function recup_liste($idClient) {
	
	$bd = em_bd_connecter();
	$sql = 	"SELECT liID, liTitre, liPrix, liPages, liResume, edWeb, edNom, auNom, auPrenom 
			FROM livres INNER JOIN editeurs ON liIDEditeur = edID 
						INNER JOIN listes ON listIDLivre = liID
						INNER JOIN aut_livre ON al_IDLivre = liID 
						INNER JOIN auteurs ON al_IDAuteur = auID
						
			WHERE listIDClient = $idClient";

	$res = mysqli_query($bd, $sql) or em_bd_erreur($bd,$sql);

	$lastID = -1;
	$_SESSION['Liste'] = array();//TODO expliquer pour la pagination
	while ($t = mysqli_fetch_assoc($res)) {
		if ($t['liID'] != $lastID) {
			$lastID = $t['liID'];
			$_SESSION['Liste'][$lastID] = array('id' => $t['liID'], 
							'titre' => $t['liTitre'],
							'edNom' => $t['edNom'],
							'edWeb' => $t['edWeb'],
							'resume' => $t['liResume'],
							'pages' => $t['liPages'],
							'prix' => $t['liPrix'],
							'auteurs' => array(array('prenom' => $t['auPrenom'], 'nom' => $t['auNom']))
						);
		}
		else {
			$_SESSION['Liste'][$lastID]['auteurs'][] = array('prenom' => $t['auPrenom'], 'nom' => $t['auNom']);
		}		
	}
    // libération des ressources
	mysqli_free_result($res);
	mysqli_close($bd);   		
}

/**
 *	Affichage de la liste de voeux d'un utilisateur
 *
 *	@param	array		$livre 		tableau associatif des infos sur un livre (id, auteurs(nom, prenom), titre, prix, pages, ISBN13, resumé, edWeb, edNom)
 *	@param 	string 		$class		classe de l'élement div 
 *  @param 	String		$prefix		Prefixe des chemins vers le répertoire images (usuellement "./" ou "../")
 *  @session  array     $_SESSION
 */
function afficher_liste($livre, $class, $prefix, $nbPage, $clientConnecte, $idClient) {
	if(count($_SESSION['Liste']) == 0){
		if($clientConnecte){
			echo '<h3>Vous n\'avez pas de livre dans votre liste</h3>';
		}else{
			echo '<h3>L\'utilisateur n\'a pas de livre dans sa liste</h3>';
		}
		return;
	}
	$count = 0;
	$nbLivre = 0;
	echo '<div class="', $class, '">';
	foreach($livre as $data){
		if($nbLivre < 15*($nbPage-1)){
			++$nbLivre;
			continue;
		}
		++$count;
		if(($count % 3) == 1){
			echo
				'<div>';
		}
		afficher_livre($data, $class, $prefix, $nbPage, $clientConnecte);
		if(($count % 3) == 0 ){
			echo
				'</div>';
		}
		if($count == 15){
			break;
		}
	}
	if($count % 3 != 0){
			echo 
				'</div>';
	}
	if(15*($nbPage-1) + $count < count($livre)){
		echo
			'<a href="', $prefix, 'php/liste.php?nbListe=', $nbPage+1, '&cliID=', $idClient,'" ><img id="droite" src="', $prefix, 'images/ajouts/suivant.jpg" alt="suivant" height="35" width="30"></a>';
	}
	if($nbPage > 1){
		echo 
		'<a href="', $prefix, 'php/liste.php?nbListe=', $nbPage-1 , '&cliID=', $idClient,'" ><img id="gauche" src="', $prefix, 'images/ajouts/precedent.jpg" alt="precedent" height="35" width="30"></a>';
	}

	echo '<form action="liste.php" method="post">',
			'<p>Rechercher par adresse e-mail <input type="text" name="email" value=" ">', 
			'<input type="submit" value="Rechercher" name="btnRechercher"></p></form></div>';
}

/**
 *	Affichage d'un livre dans la liste des voeux d'un utilisateur
 *
 *	@param	array		$livre 		tableau associatif des infos sur un livre (id, auteurs(nom, prenom), titre, prix, pages, ISBN13, resumé, edWeb, edNom)
 *	@param 	string 		$class		classe de l'élement div 
 *  @param 	String		$prefix		Prefixe des chemins vers le répertoire images (usuellement "./" ou "../")	
 * 	@session  array     $_SESSION
 */
function afficher_livre($livre, $class, $prefix, $nbPage, $clientConnecte){
	echo 
		'<div>',
			'<a href="', $prefix, 'php/details.php?article=', $livre['id'], '" title="Voir détails">',
			'<img src="', $prefix, 'images/livres/', $livre['id'], '.jpg" alt="', 
			em_html_proteger_sortie($livre['titre']),'">',
			'</a>',
			'<a class="addToCart" href="',$prefix,'php/ajout_panier.php?id=',$livre['id'],'" title="Ajouter au panier"></a>';
		if($clientConnecte){
			echo
			'<a class="delete" href="',$prefix,'php/liste.php?nbListe=',$nbPage,'&liID=',$livre['id'],'" title="Supprimer de la liste"></a>';
		}
			echo
			'<span>',
			'<strong>', em_html_proteger_sortie($livre['titre']), '</strong><br>';
		$i = 0;
		foreach ($livre['auteurs'] as $auteur) {
			$supportLien = $class == 'arRecherche' ? "{$auteur['prenom']} {$auteur['nom']}" : "{$auteur['prenom']{0}}. {$auteur['nom']}";
			if ($i > 0) {
				echo ', ';
			}
			$i++;
			echo '<a href="', $prefix, 'php/recherche.php?type=auteur&quoi=', urlencode($auteur['nom']), '">',em_html_proteger_sortie($supportLien), '</a>';
		}
		echo 
			'<br>',
			'Editeur : <a class="lienExterne" href="http://', em_html_proteger_sortie($livre['edWeb']), '" target="_blank">', em_html_proteger_sortie($livre['edNom']), '</a><br>',
			'Prix : ', $livre['prix'], ' &euro;<br>';
	echo
		'</span>',
	'</div>';
}

/**
 *	Contrôle de la validité des informations reçues via la query string 
 *
 * En cas d'informations invalides, la session de l'utilisateur est arrêtée et il est redirigé vers la page index.php
 *
 * @global  array	$_GET
 *
 * @return	int		L'ID du livre à afficher            
 */
function control_get_livre(){
	(count($_GET) > 2) && em_session_exit();

	if(isset($_GET['nbListe'])){
		$valueL = trim($_GET['nbListe']);
		(! is_numeric($valueL)) && em_session_exit(); 
		
		$notags = strip_tags($valueL);
		(mb_strlen($notags, 'UTF-8') != mb_strlen($valueL, 'UTF-8')) && em_session_exit();

		return $valueL;
	}
	
	!isset($_GET['liID']) && em_session_exit();
	
	return 0;
}

/**
 *	Contrôle de la validité des informations reçues via la query string 
 *
 * En cas d'informations invalides, la session de l'utilisateur est arrêtée et il est redirigé vers la page index.php
 *
 * @global  array	$_GET
 *
 * @return	int		L'ID du livre à enlever de la liste ou -1 si rien à supprimer           
 */
function control_get_delete(){
	if(!isset($_GET['liID']) || !isset($_SESSION['Liste'])){
		return -1;
	}

	$valueID = trim($_GET['liID']);
    (!is_numeric($valueID)) && em_session_exit(); 
    $notags = strip_tags($valueID);
	(mb_strlen($notags, 'UTF-8') != mb_strlen($valueID, 'UTF-8')) && em_session_exit();

	return $valueID;
}

/**
 *	Supprime de la liste de cadeau le livre d'ID $valueID
 *
 *
 * @param	int		L'ID du livre à enlever de la liste ou -1 si rien à supprimer  
 * @global  array	$_GET
 * @session array	$_SESSION
 *  
 */
function delete($valueID){
	if($valueID == -1){
		return;
	}

	$bd = em_bd_connecter();
	$idClient = $_SESSION['cliID'];
	if (isset($_SESSION['listeCadeau'][$valueID])) {
		$sql = "DELETE FROM listes
				WHERE listIDLivre = $valueID
				AND listIDClient = $idClient";
		$res = mysqli_query($bd, $sql) or em_bd_erreur($bd,$sql);
		recup_liste($_SESSION['cliID']);
	}
	mysqli_close($bd);
	unset($_SESSION['listeCadeau'][$valueID]);
}

/**
 *	Contrôle de la validité des informations reçues via la query string 
 *
 * En cas d'informations invalides, la session de l'utilisateur est arrêtée et il est redirigé vers la page index.php
 *
 * @global  array	$_POST
 *
 * @return	string	l'adresse mail de l'utilisateur recherché           
 */
function control_post_client(){
	(count($_POST) != 2) && em_session_exit();
	(! isset($_POST['btnRechercher']) || $_POST['btnRechercher'] != 'Rechercher') && em_session_exit();
	(! isset($_POST['email'])) && em_session_exit();
    $valueMail = trim($_POST['email']);
    $notags = strip_tags($valueMail);
	(mb_strlen($notags, 'UTF-8') != mb_strlen($valueMail, 'UTF-8')) && em_session_exit();
	return $valueMail;
}


/**
 *	Contrôle la validité de l'adresse mail recherché
 *
 *
 * @param   string	$valueMail		l'adresse mail de l'utilisateur recherché 
 * @return	int		$cliID			l'id de l'utilisateur si l'adresse mail est valide, -1 sinon	          
 */
function verification_mail($valueMail){
	$cliID = -1;
	if($valueMail == ''){
		return -1;
	}
	$bd = em_bd_connecter();
	$sql = "SELECT cliID, cliEmail
			FROM clients";
	$res = mysqli_query($bd, $sql) or em_bd_erreur($bd,$sql);
	while($t = mysqli_fetch_assoc($res)){
		if(strcmp($t['cliEmail'], $valueMail) == 0){
			echo 'salut';
			$cliID = $t['cliID'];
		}
	}
	mysqli_free_result($res);
	mysqli_close($bd);

    return $cliID;
}

?>