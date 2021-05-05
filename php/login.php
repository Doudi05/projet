<?php

ob_start(); //démarre la bufferisation
session_start();
require_once 'bibli_generale.php';
require_once 'bibli_bookshop.php';

error_reporting(E_ALL); // toutes les erreurs sont capturées (utile lors de la phase de développement)

if (em_est_authentifie()){
    $chemin = isset($_POST['redirection'])? $_POST['redirection'] : '../index.php';
    header('Location: '.$chemin);
    exit();
}
//imput de type hidden pour la redirection 

// traitement si soumission du formulaire d'inscription
$err = isset($_POST['btnConnexion']) ? eml_traitement_inscription() : array(); 



em_aff_debut('BookShop | Connexion', '../styles/bookshop.css', 'main');

em_aff_enseigne_entete();

ab_aff_contenu($err);

em_aff_pied();

em_aff_fin('main');

ob_end_flush();

function ab_aff_contenu($err) {

    // réaffichage des données soumises en cas d'erreur, sauf les mots de passe
    $email = isset($_POST['email']) ? em_html_proteger_sortie(trim($_POST['email'])) : '';
    echo 
        '<h1>Connexion à BookShop</h1>';
        
    if (count($err) > 0) {
        echo '<p class="error">Votre connexion n\'a pas pu être réalisée à cause des erreurs suivantes : ';
        foreach ($err as $v) {
            echo '<br> - ', $v;
        }
        echo '</p>';    
    }
    echo    
        '<p>Pour vous connecter, merci de fournir les informations suivantes. </p>',
        '<form method="post" action="login.php">',
            '<table>';

    em_aff_ligne_input('Votre adresse email :', array('type' => 'email', 'name' => 'email', 'value' => $email, 'required' => true));
    em_aff_ligne_input('saisissez votre mot de passe :', array('type' => 'password', 'name' => 'passe', 'value' => '', 'required' => true));
    echo '<input type="hidden" name="redirection" value="'
    ,isset($_POST["redirection"])? $_POST["redirection"] : ( isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../index.php') ,'">';
            
    echo 
                '<tr>',
                    '<td colspan="2">',
                        '<input type="submit" name="btnConnexion" value="Connexion">',
                         '<p"><a href="inscription.php">Inscription</a></p>',
                    '</td>',
                '</tr>',
            '</table>',
        '</form>';
}


function eml_traitement_inscription() {
    $erreurs = array();

    if( !em_parametres_controle('post', array('email', 'passe', 'btnConnexion','redirection'))) {
        $erreurs [] = 'Tous les champs doivent être remplis';
        echo 'blabla',$_POST['email'],'';
        return $erreurs; $erreurs = array();

    }

   

    // vérification du format de l'adresse email
    $email = trim($_POST['email']);
    if (empty($email)){
        $erreurs[] = 'L\'adresse mail ne doit pas être vide.'; 
        return $erreurs;
    }
    else {
        if (mb_strlen($email, 'UTF-8') > LMAX_EMAIL){
            $erreurs[] = 'L\'adresse mail ne peut pas dépasser '.LMAX_EMAIL.' caractères.';
            return $erreurs;
        }
        // la validation faite par le navigateur en utilisant le type email pour l'élément HTML input
        // est moins forte que celle faite ci-dessous avec la fonction filter_var()
        // Exemple : 'l@i' passe la validation faite par le navigateur et ne passe pas
        // celle faite ci-dessous
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erreurs[] = 'L\'adresse mail n\'est pas valide.';
            return $erreurs;
        }
        // vérification de l'existance de l'adresse email
        $bd = em_bd_connecter();
        $email = em_bd_proteger_entree($bd, $email);
        $sql = "SELECT cliID FROM clients WHERE cliEmail = '$email'"; 

        $res = mysqli_query($bd,$sql) or em_bd_erreur($bd,$sql);
        
        if (mysqli_num_rows($res) == 0) {
            $erreurs[] = 'L\'adresse email spécifiée n\'existe pas.';
            // libération des ressources
            mysqli_free_result($res);
            mysqli_close($bd);
            return $erreurs;
        }

    }

    // vérification des mots de passe
    $passe = trim($_POST['passe']);
    $nb = mb_strlen($passe, 'UTF-8');
    if (empty($passe)){
        $erreurs[] = 'Le mot de passe ne doit pas être vide.'; 
        return $erreurs;
    }else {
        if ($nb < LMIN_PASSWORD || $nb > LMAX_PASSWORD){
            $erreurs[] = 'Le mot de passe doit être constitué de '. LMIN_PASSWORD . ' à ' . LMAX_PASSWORD . ' caractères.';
            return $erreurs;
        }
        // vérification de l'existance du mot de passe
        $bd = em_bd_connecter();
        $email = em_bd_proteger_entree($bd, $email);
        $sql = "SELECT cliID, cliPassword FROM clients WHERE cliEmail = '$email'"; 

        $res = mysqli_query($bd,$sql) or em_bd_erreur($bd,$sql);
        $t = mysqli_fetch_assoc($res);
        $passBd = $t['cliPassword'];
        if (!password_verify($passe, $passBd)) {
            $erreurs[] = 'Le mot de passe est incorrect.';
            // libération des ressources
            mysqli_free_result($res);
            mysqli_close($bd);

        }else {
            $_SESSION['id'] = $t['cliID'];
            $erreurs []= 'bien connecté';
            header("Location: ".$_POST['redirection']);
        }
        /*****FAIRE LA REDIRECTION SUR LA PAGE D'ORIGINE OU INDEX  */
    }


    
        return $erreurs;    
    

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
}

?>