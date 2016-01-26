<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Guzzle\Http\Client;
use AppBundle\Entity\Comment;

class GitController extends Controller
{
    /**
     * @Route("/", name="git")
     */
    public function indexAction()
    {
        // Display template "index.html.twig"
        return $this->render('AppBundle:Git:index.html.twig', array(
            'error'         => '',
        ));
    }

    public function checkUserAction(Request $request){
        $git_username = $request->request->get('git_username');
        if ( is_string($git_username) && !empty($git_username) ){
            // If Git Request returns results
            if ($this->performGitRequest('search/users?q='.$git_username)['total_count'] > 0){
                // Save username in session
                $request->getSession()->set('git_username', $git_username); 
                // Redirect to custom username route
                return $this->redirect($this->generateUrl("git_username", array('git_username' => $git_username)));
            }
            else {
                $error = "Compte GitHub non trouvé";
            }
        }
        else {
            $error = "Le champ saisi n'est pas une chaine de caractères ou est vide";
        }
        // Display template "index.html.twig"
        return $this->render('AppBundle:Git:index.html.twig', array('error' => $error));
    }

    public function checkRepositoryAction(Request $request){
        /* Define variables */
        $git_username = $request->request->get('git_username');
        $git_repository = $request->request->get('git_repository'); // {user}/{repo}
        $git_comment = $request->request->get('git_comment');
        $error = "";
        $data = $this->performGitRequest('repos/'.$git_repository);
        if ($data != 0){
            if ($data['owner']['login'] == $git_username){
                if (!empty($git_comment)){
                    // Add comment
                    $this->addComment($git_comment, $git_username, $git_repository);
                    // Redirect to git user route
                    return $this->redirect($this->generateUrl("git_username", array('git_username' => $git_username)));
                }
                else {
                    $error = "Veuillez saisir un commentaire";
                }
            }
            else {
                $error = "Ce dépôt n'appartient pas à l'utilisateur Git saisi précédemment";
            }
        }
        else {
            $error = "Aucun dépôt de ce nom trouvé sur GitHub";
        }
        // Display page with the error
        return $this->viewAction($git_username, $request, $error);
    }

    public function performGitRequest($parameters){
        /* Send request to Git API and return the response into json format */
        $client = new Client('https://api.github.com');
        $request = $client->get($parameters);
        try {
            $response = $request->send();
        } catch (\Guzzle\Http\Exception\ClientErrorResponseException $e) {
            return 0;
        }
        $data = $response->json();
        
        return $data;
    }

    public function addComment($content, $user, $repository){
        /* Create and define new object "Comment" before insert */
        $comment = new Comment();
        $comment->setContent($content);
        $comment->setUser($user);
        $comment->setRepository($repository);
        
        // Get the Doctrine EntityManager
        $em = $this->getDoctrine()->getManager();
        // Entity Persistence
        $em->persist($comment);
        // Insert in database (by flush)
        $em->flush();
    }
    
    public function getComments($user){
        // Get the Doctrine EntityManager
        $em = $this->getDoctrine()->getManager();
        // Get comments of the user in parameter
        $comments = $em->getRepository('AppBundle:Comment')->findBy(array('user' => $user), array('id' => 'DESC'));

        return $comments;
    }
    
    public function viewAction($git_username, Request $request, $error = "")
    {
        // If username is not in session or is not the same as in session
        if ( !$request->getSession()->get('git_username') || $request->getSession()->get('git_username') != $git_username ){
            // Redirect to the form in the "git" route
            return $this->redirectToRoute('git');
        }
        // Display template "comment.html.twig"
        return $this->render('AppBundle:Git:comment.html.twig', array(
            'git_username' => $git_username,
            'error' => $error,
            'comments' => $this->getComments($git_username),
        ));
    }
}
