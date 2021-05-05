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
//imput de type hidden pour la redirection 

// traitement si soumission du formulaire d'inscription




em_aff_debut('BookShop | Mon compte', '../styles/bookshop.css', 'main');

em_aff_enseigne_entete();

ab_aff_contenu();

em_aff_pied();

em_aff_fin('main');

ob_end_flush();

function ab_aff_contenu($err = array()) {
    $bd = em_bd_connecter();
    $sql = 'SELECT * FROM clients WHERE cliID = \''.$_SESSION['id'].'\''; 
    $res = mysqli_query($bd,$sql) or em_bd_erreur($bd,$sql);
    $t = mysqli_fetch_assoc($res);
    echo '<h1>Les informations de votre compte : </h1>';
    echo '<table>';
    lb_aff_ligne_info("Adresse mail :",$t['cliEmail'],"email",'./modif.php');
    lb_aff_ligne_info("Mot de passe :",'******','type','./modif.php');
    lb_aff_ligne_info("Nom prénom :",$t['cliNomPrenom'],"nomprenom",'./modif.php');
    lb_aff_ligne_info("Adresse :",$t['cliAdresse'],"adresse",'./modif.php');
    lb_aff_ligne_info("Code postal :",$t['cliCP'],"cp",'./modif.php');
    lb_aff_ligne_info("Ville :",$t['cliVille'],"ville",'./modif.php');
    lb_aff_ligne_info("Pays :",$t['cliPays'],"pays",'./modif.php');
    echo '</table>';
    mysqli_free_result($res);
    mysqli_close($bd);

   
}



// function eml_traitement_inscription() {
//     $erreurs = array();

//     if( !em_parametres_controle('post', array('email', 'passe', 'btnConnexion','redirection'))) {
//         $erreurs [] = 'Tous les champs doivent être remplis';
//         echo 'blabla',$_POST['email'],'';
//         return $erreurs; $erreurs = array();

//     }

   

//     // vérification du format de l'adresse email
//     $email = trim($_POST['email']);
//     if (empty($email)){
//         $erreurs[] = 'L\'adresse mail ne doit pas être vide.'; 
//         return $erreurs;
//     }
//     else {
//         if (mb_strlen($email, 'UTF-8') > LMAX_EMAIL){
//             $erreurs[] = 'L\'adresse mail ne peut pas dépasser '.LMAX_EMAIL.' caractères.';
//             return $erreurs;
//         }
//         // la validation faite par le navigateur en utilisant le type email pour l'élément HTML input
//         // est moins forte que celle faite ci-dessous avec la fonction filter_var()
//         // Exemple : 'l@i' passe la validation faite par le navigateur et ne passe pas
//         // celle faite ci-dessous
//         if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
//             $erreurs[] = 'L\'adresse mail n\'est pas valide.';
//             return $erreurs;
//         }
//         // vérification de l'existance de l'adresse email
//         $bd = em_bd_connecter();
//         $email = em_bd_proteger_entree($bd, $email);
//         $sql = "SELECT cliID FROM clients WHERE cliEmail = '$email'"; 

//         $res = mysqli_query($bd,$sql) or em_bd_erreur($bd,$sql);
        
//         if (mysqli_num_rows($res) == 0) {
//             $erreurs[] = 'L\'adresse email spécifiée n\'existe pas.';
//             // libération des ressources
//             mysqli_free_result($res);
//             mysqli_close($bd);
//             return $erreurs;
//         }

//     }

//     // vérification des mots de passe
//     $passe = trim($_POST['passe']);
//     $nb = mb_strlen($passe, 'UTF-8');
//     if (empty($passe)){
//         $erreurs[] = 'Le mot de passe ne doit pas être vide.'; 
//         return $erreurs;
//     }else {
//         if ($nb < LMIN_PASSWORD || $nb > LMAX_PASSWORD){
//             $erreurs[] = 'Le mot de passe doit être constitué de '. LMIN_PASSWORD . ' à ' . LMAX_PASSWORD . ' caractères.';
//             return $erreurs;
//         }
//         // vérification de l'existance du mot de passe
//         $bd = em_bd_connecter();
//         $email = em_bd_proteger_entree($bd, $email);
//         $sql = "SELECT cliID, cliPassword FROM clients WHERE cliEmail = '$email'"; 

//         $res = mysqli_query($bd,$sql) or em_bd_erreur($bd,$sql);
//         $t = mysqli_fetch_assoc($res);
//         $passBd = $t['cliPassword'];
//         if (!password_verify($passe, $passBd)) {
//             $erreurs[] = 'Le mot de passe est incorrect.';
//             // libération des ressources
//             mysqli_free_result($res);
//             mysqli_close($bd);

//         }else {
//             $_SESSION['id'] = $t['cliID'];
//             $erreurs []= 'bien connecté';
//             header("Location: ".$_POST['redirection']);
//         }
//         /*****FAIRE LA REDIRECTION SUR LA PAGE D'ORIGINE OU INDEX  */
//     }


    
//         return $erreurs;    
    

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// }

?>