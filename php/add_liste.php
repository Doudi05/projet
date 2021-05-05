<?php

ob_start(); //démarre la bufferisation
session_start();
require_once 'bibli_generale.php';
require_once 'bibli_bookshop.php';

error_reporting(E_ALL); // toutes les erreurs sont capturées (utile lors de la phase de développement)

$valueQuoi = '';


if ($_GET){
	$valueQuoi = control_get ();
}

if(!isset($_SESSION['cliID'])){
    redirige('login.php');
}
ajout_liste_contenu();
redirige($_SERVER['HTTP_REFERER']);
ob_end_flush();

/**
 * Ajoute un livre à la liste de voeux d'un client
 *
 *
 * @global  array     $_GET
 *
 */
function ajout_liste_contenu(){
    $bd = em_bd_connecter();
    $sql_ajout_liste = "INSERT INTO listes(listIDClient, listIDLivre) VALUES ({$_SESSION['cliID']}, {$_GET['id']})";
    $sql = bd_protect($bd, $sql_ajout_liste);
    $res = mysqli_query($bd,$sql_ajout_liste) or em_bd_erreur($bd,$sql_ajout_liste);
    mysqli_close($bd);
    $_SESSION['listeCadeau'][$_GET['id']] = $_GET['id'];
}

/**
 *	Contrôle de la validité des informations reçues via la query string 
 *
 * En cas d'informations invalides, la session de l'utilisateur est arrêtée et il redirigé vers la page index.php
 *
 * @global  array     $_GET
 *
 * @return string     Id du livre à ajouter
 */
function control_get (){
	(count($_GET) != 1) && em_session_exit();
	(! isset($_GET['id'])) && em_session_exit();

    $valueQ = trim($_GET['id']);
    $notags = strip_tags($valueQ);
    (mb_strlen($notags, 'UTF-8') != mb_strlen($valueQ, 'UTF-8')) && em_session_exit();
    
	return $valueQ;
}

?>