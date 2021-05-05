<?php

ob_start(); //démarre la bufferisation
session_start();
require_once 'bibli_generale.php';
require_once 'bibli_bookshop.php';

error_reporting(E_ALL); // toutes les erreurs sont capturées (utile lors de la phase de développement)
if (!em_est_authentifie()){
    $chemin = isset($_POST['redirection'])? $_POST['redirection'] : ( isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../index.php');
    header('Location: '.$chemin);
    exit();
}

em_aff_debut('BookShop | Mon panier', '../styles/bookshop.css', 'main');

em_aff_enseigne_entete();

ab_aff_contenu();
em_aff_pied();

em_aff_fin('main');

ob_end_flush();

function ab_aff_contenu (){
    echo '<h1>Votre panier</h1>';
    echo '<table class="panier">
    <tr><td><p>Article</p></td> <td><p>Quantité</p></td><td><p>Prix/unit</p></td> <td><p>Prix</p></td></tr>';
    //afficher le contenu du panier
    echo'</table>';
}
?>