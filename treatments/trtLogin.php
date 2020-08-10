<?php
/**************************************************************
 *       SCRIPT POUR LA CONNEXION A L'APPLICATION             *
 *************************************************************/
session_start();

if (isset($_POST['mail'], $_POST['password'])){

    $mail = htmlspecialchars($_POST['mail']);
    $mdp = htmlspecialchars(sha1($_POST['password']));

    if (filter_var($mail, FILTER_VALIDATE_EMAIL)){

        $domain = strtolower(substr(strrchr($mail, '@'), 1));

//        if ($domain === "unicef.org"){

            require "../files/functions.php";
            require "../files/dbConnect.php";

            $checkUser = getInfoTable("emailUser", $mail, "user", "*", "AND mdpUser = '".$mdp."'", $db);

            if ($checkUser->rowCount() == 1){

                $infoUser = $checkUser->fetch();

                if ($infoUser['activUser'] == "1"){

                    $_SESSION['infoUser'] = $infoUser;

                    $msg = "Heureux de vous revoir ".$_SESSION['infoUser']['nomPnomUser'];

                    $_SESSION['idUser'] = $_SESSION['infoUser']['matUser'];

                    $action = "Connexion a l'application";
                    auditTrail($_SESSION['infoUser']['nomPnomUser'], $_SESSION['infoUser']['emailUser'], $_SERVER['PHP_SELF'], $action, $db);

                    if(($_SESSION['infoUser']['typePerso'] === 'U') || ($_SESSION['infoUser']['typePerso'] === 'UEdu')){

                        header('location: ../pages/index.php?msg='.base64_encode($msg));

                    }elseif (($_SESSION['infoUser']['typePerso'] === 'P') || ($_SESSION['infoUser']['typePerso'] === 'B')){


                        #Requete pour recuperer les infos sur le partenaire qui tente d'ouvrir une session
                        $checkIfIsBailleur = getInfoTable("libBailleur", $_SESSION['infoUser']['organisation'], "bailleur", "idBailleur", "", $db);

                        $infoBailleur = $checkIfIsBailleur->fetch();
                        $_SESSION['infoUser']['idBailleur'] = $infoBailleur['idBailleur'];

                        $msg = "Vous etes connecte en tant que PARTENAIRE.";
                        header('location: ../pages/index.php?msg='.base64_encode($msg));
                    }
                }else{
                    $error = "Votre n'est pas encore active. Veuillez entrer contact avec l'administrateur.";
                }
            }else{
                $error = "Les informations que vous avez renseignez ne correspondent a aucun utilisateur !";
            }

//        }else{
//            $error = "Votre adresse mail ne vous donne pas acces a cette application !!";
//        }

    }else{
        $error = "Veuillez renseignez svp une adresse mail correcte !";
    }

}else{
    $error = "Aucunes variable n'a ete passee !";
}

if (isset($error)){header('location: ../index.php?errorLog='.base64_encode($error));}