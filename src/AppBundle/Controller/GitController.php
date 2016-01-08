<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class GitController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        // Affichage du template index.html.twig
        return $this->render('AppBundle:Git:index.html.twig', array(
        ));
    }

    public function checkUserAction(Request $user){

        if (is_string($_POST['git_user'])){
            if ($this->performUrlRequest($user)->total_count > 0){ // Si la requête GitHub retourne des résultats
                return "bien ouej";
            }
            else {
                $error = "Compte GitHub non trouvé";
                return $this->render('AppBundle:Git:index.html.twig', array(
                    'error'         => $error,
                ));
            }
        }
        else {
            $error = "Le champ saisi n'est pas une chaine de caractères";
            return $this->render('AppBundle:Git:index.html.twig', array(
                'error'         => $error,
            ));
        }
    }

    public function performUrlRequest(Request $user){
        /* Recherche de l'utilisateur sur GitHub avec curl */
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.github.com/search/users?q='.$_POST['git_user']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 6.1; fr; rv:1.9.2.13'); // Forge un User Agent Mozilla (sécurité API Git)

        $response = curl_exec($ch);  // Récupération de la réponse
        $data = json_decode($response); // Décodage de la réponse

        return $data;
    }
}
