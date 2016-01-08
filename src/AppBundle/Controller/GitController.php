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

    public function checkUserAction(Request $request){

        if (is_string($_POST['git_user'])){
            if ($this->performUrlRequest($request)->total_count > 0){ // Si la requête GitHub retourne des résultats
                // Redirection vers l'url personnalisée en fonction du nom d'utilisateur
                return $this->redirect(
                    $this->generateUrl("git_username", array("git_username" => $request->request->get('git_user')))
                );
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

    public function checkRepositoryAction(Request $request){
        /* Récupération des dépôts sur GitHub avec curl (pour vérification) */
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.github.com/users/'.$request->request->get('git_username').'/repos');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 6.1; fr; rv:1.9.2.13'); // Forge un User Agent Mozilla (sécurité API Git)

        $response = curl_exec($ch);  // Récupération de la réponse
        $data = json_decode($response); // Décodage de la réponse

        $totalRepos = count($data);
        $tmpCount = 0;

        /* On parcours l'ensemble des dépôts */
        foreach($data as $repo){
            if ($repo->full_name == $request->request->get('git_repository')){
                /* actions à effectuer (projet non terminé) */
                break;
            }
            else {
                $tmpCount++;
            }
        }
        if ($tmpCount == $totalRepos){
            // Affichage du template comment.html.twig
            return $this->render('AppBundle:Git:comment.html.twig', array(
                'git_username' => $request->request->get('git_username'),
                'error' => "Aucun dépôt de ce nom lié à cet utilisateur",
            ));  
        }
        else {
            return $this->render('AppBundle:Git:comment.html.twig', array(
                'git_username' => $request->request->get('git_username'),
                'error' => "Dépôt trouvé, vous pouvez ajouter un commentaire (not done !)",
            ));  
        }
    }

    public function performUrlRequest(Request $request){
        /* Recherche de l'utilisateur sur GitHub avec curl */
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.github.com/search/users?q='.$request->request->get('git_user'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 6.1; fr; rv:1.9.2.13'); // Forge un User Agent Mozilla (sécurité API Git)

        $response = curl_exec($ch);  // Récupération de la réponse
        $data = json_decode($response); // Décodage de la réponse

        return $data;
    }

    public function viewAction($git_username)
    {
        // Affichage du template comment.html.twig
        return $this->render('AppBundle:Git:comment.html.twig', array(
            'git_username' => $git_username,
            'error' => "",
        ));
    }
}
