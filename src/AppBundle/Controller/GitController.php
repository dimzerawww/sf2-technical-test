<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Comment;
use AppBundle\Form\CommentType;

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
     * Check if user exists in GitHub and redirect to comment page
     * 
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function checkUserAction(Request $request){
        $gitUserService = $this->container->get('app.git.user');
        $git_username = $request->request->get('git_username');
        if ( $gitUserService->isValid($git_username) ){
            if ($this->performGitRequest('search/users?q='.$git_username)['total_count'] > 0){
                $request->getSession()->set('git_username', $git_username); 
                return $this->redirect($this->generateUrl("git_username", array('git_username' => $git_username)));
            }
            else {
                $error = "Compte GitHub non trouvé";
            }
        }
        else {
            $error = "Le champ saisi n'est pas une chaine de caractères ou est vide";
        }

        return $this->render('AppBundle:Git:index.html.twig', array('error' => $error));
    }

    /**
     * Check if repository exists, if it is a repository of the user and add the comment
     * 
     * 
     * @param $form_data
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function checkRepositoryAction($form_data, Request $request){
        $gitRepositoryService = $this->container->get('app.git.repository');
        $error = "";
        $git_username = $form_data->getUser();
        $git_comment = $form_data->getContent();
        $git_data = $this->performGitRequest('repos/'.$form_data->getRepository());
        if ($gitRepositoryService->isValid($git_data, $git_comment, $git_username)){
            $this->addComment($form_data);
            return $this->redirect($this->generateUrl("git_username", array('git_username' => $git_username)));
        }
        
        return $this->viewAction($git_username, $request, $error);
    }

    /**
     * Display the comment page with the comment form
     * 
     * @param $git_username
     * @param Request $request
     * @param string $error
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function viewAction($git_username, Request $request, $error = "")
    {
        // If username is not in session or is not the same as in session
        if ( !$request->getSession()->get('git_username') || $request->getSession()->get('git_username') != $git_username ){
            return $this->redirectToRoute('git');
        }
        
        $form = $this->generateCommentForm($git_username);
        $form->handleRequest($request);
        if ($form->isValid()){
            $this->checkRepositoryAction($form->getData(), $request);
        }

        return $this->render('AppBundle:Git:comment.html.twig', array(
            'git_username' => $git_username,
            'error' => $error,
            'comments' => $this->getComments($git_username),
            'form' => $form->createView(),
        ));
    }

    /**
     * Persist the posted comment
     *
     * @param $comment
     */
    private function addComment($comment){
        $em = $this->getDoctrine()->getManager();
        $em->persist($comment);
        $em->flush();
    }

    /**
     * Generate the comment form
     * 
     * @param $user
     * @return mixed
     */
    private function generateCommentForm($user){
        $comment = new Comment();
        $comment->setUser($user);
        $form = $this->createForm(new CommentType(), $comment);
        
        return $form;
    }

    /**
     * Get comments of the user
     * 
     * @param $user
     * @return mixed
     */
    private function getComments($user){
        $em = $this->getDoctrine()->getManager();
        $comments = $em->getRepository('AppBundle:Comment')->findBy(array('user' => $user), array('id' => 'DESC'));

        return $comments;
    }
    
    /**
     * Request the Git API to see if user exists
     * 
     * @param $parameters
     * @return mixed
     */
    private function performGitRequest($parameters){
        $gitClientService = $this->container->get('guzzle.git.client');
        $gitRepositoryService = $this->container->get('app.git.repository');
        $git_request = $gitClientService->get($parameters);
        if ($gitRepositoryService->testSend($git_request)){
            $data = $gitClientService->get($parameters)->send()->json();
        }
        
        return $data;
    }
}
