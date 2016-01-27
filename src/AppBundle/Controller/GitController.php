<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Guzzle\Http\Client;
use AppBundle\Entity\Comment;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class GitController extends Controller
{
    /**
     * HomePage
     * @Route("/", name="git")
     */
    public function indexAction()
    {
        return $this->render('AppBundle:Git:index.html.twig', array(
            'error'         => '',
        ));
    }

    /**
     * ???
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
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

    /**
     * ??
     * @param $form_data
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function checkRepositoryAction($form_data, Request $request){
        $git_username = $form_data->getUser();
        $error = "";
        $data = $this->performGitRequest('repos/'.$form_data->getRepository());
        if ($data == 0) {
            $error = "Aucun dépôt de ce nom trouvé sur GitHub";
        }else if(!isset($data['owner'])
            || !isset($data['owner']['login'])
            || !$data['owner']['login'] == $git_username
        ) {
            $error = "Ce dépôt n'appartient pas à l'utilisateur Git saisi précédemment";
        } elseif(empty($form_data->getContent()) {
            $error = "Veuillez saisir un commentaire";
        } else {
            // Add comment
            $this->addComment($form_data);
            // Redirect to git user route
            return $this->redirect($this->generateUrl("git_username", array('git_username' => $git_username)));
        }
        
        // Display page with the error
        return $this->viewAction($git_username, $request, $error);
    }

    /**
     *
     * ???
     * @param $git_username
     * @param Request $request
     * @param string $error
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function viewAction($git_username, Request $request, $error = "")
    {
        // If username is not in session or is not the same as in session
        if ( !$request->getSession()->get('git_username') || $request->getSession()->get('git_username') != $git_username ){
            // Redirect to the form in the "git" route
            return $this->redirectToRoute('git');
        }
        // Get the Comment Form
        $form = $this->generateCommentForm($git_username);
        // Link POST variables and form
        $form->handleRequest($request);
        // If form values are valid
        if ($form->isValid()){
            // Check if the repository exists and if is it a repository of the current user
            $this->checkRepositoryAction($form->getData(), $request);
        }

        // Display template "comment.html.twig"
        return $this->render('AppBundle:Git:comment.html.twig', array(
            'git_username' => $git_username,
            'error' => $error,
            'comments' => $this->getComments($git_username),
            'form' => $form->createView(),
        ));
    }

    /**
     * Create and define new object "Comment" before insert
     *
     * @param $form_data
     */
    private function addComment($form_data){

        $comment = $form_data;

        // persist
        $em = $this->getDoctrine()->getManager();
        $em->persist($comment);
        $em->flush();
    }

    /**
     * @param $user
     * @return mixed
     */
    private function generateCommentForm($user){
        // New Comment object
        $comment = new Comment();
        $comment->setUser($user);
        // Generate the comment form
//        $form  = $this->createBuilder('comment', $comment);
        $form = $this->createFormBuilder('form', $comment)
            ->add('user', HiddenType::class)
            ->add('repository', 'text')
            ->add('content', 'textarea')
            ->add('save', 'submit')
            ->getForm()
        ;
        
        return $form;
    }

    /**
     * @param $user
     * @return mixed
     */
    private function getComments($user){
        // Get the Doctrine EntityManager
        $em = $this->getDoctrine()->getManager();
        // Get comments of the user in parameter
        $comments = $em->getRepository('AppBundle:Comment')->findBy(array('user' => $user), array('id' => 'DESC'));

        return $comments;
    }
    
    private function performGitRequest($parameters){
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
}
