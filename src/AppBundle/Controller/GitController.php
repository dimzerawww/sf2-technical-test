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
        return $this->render('AppBundle:Git:index.html.twig', array());
    }

    /**
     * Check if user exists in GitHub and redirect to comment page
     * 
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function checkUserAction(Request $request)
    {
        $gitUserService = $this->get('app.git.user');
        $git_username = $request->request->get('git_username');
        $git_response = $this->performGitRequest('search/users?q='.$git_username.'&');
        $gitUserService->validate($git_username, $git_response);
        if ($gitUserService->isValid()) {
            $request->getSession()->set('git_username', $git_username); 
            return $this->redirect($this->generateUrl("git_username", array('git_username' => $git_username)));
        } else {
            $this->addFlash('error', $gitUserService->getError());
        }

        return $this->render('AppBundle:Git:index.html.twig');
    }


    /**
     * Display the comment page with the comment form
     * 
     * @param $git_username
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function viewAction($git_username, Request $request)
    {
        // If username is not in session or is not the same as in session
        if (!$request->getSession()->get('git_username') || $request->getSession()->get('git_username') != $git_username) {
            return $this->redirectToRoute('git');
        }
        
        $gitRepositoryService = $this->get('app.git.repository');
        
        $form = $this->generateCommentForm($git_username);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $form_data = $form->getData();
            $git_username = $form_data->getUser();
            $git_comment = $form_data->getContent();
            
            foreach ($form_data->getRepository() as $git_repository) {
                $git_data = $this->performGitRequest('repos/'.$git_repository.'?');
                $gitRepositoryService->validate($form_data, $git_data);
                if ($gitRepositoryService->isValid()) {
                    $this->addComment($git_username, $git_comment, $git_repository);
                    $this->addFlash('notice', 'Le commentaire a été ajouté sur le dépôt '.$git_repository);
                } else {
                    $this->addFlash('error', $gitRepositoryService->getError());
                }
            }
        }

        return $this->render('AppBundle:Git:comment.html.twig', array(
            'git_username' => $git_username,
            'comments' => $this->getComments($git_username),
            'form' => $form->createView(),
        ));
    }

    /**
     * Persist the posted comment
     *
     * @param $comment
     */
    private function addComment($user, $content, $repository)
    {
        $comment = new Comment();
        $comment->setUser($user);
        $comment->setContent($content);
        $comment->setRepository($repository);
        
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
    private function generateCommentForm($user)
    {
        $comment = new Comment();
        $comment->setUser($user);
        $form = $this->createForm(new CommentType(), $comment, array('repositories' => $this->getRepositories($user)));
        
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
     * Get repositories of the user (with Git API)
     * 
     * @param $user
     * @return array
     */
    private function getRepositories($user)
    {
        $git_data = $this->performGitRequest('users/'.$user.'/repos?');
        $git_repositories = array();
        foreach ($git_data as $repository) {
            $git_repositories[$repository['full_name']] = $repository['name'];
        }
        
        return $git_repositories;
    }
    
    /**
     * Request the Git API
     * 
     * @param $parameters
     * @return mixed
     */
    private function performGitRequest($parameters)
    {
        $gitClientService = $this->get('guzzle.git.client');
        $gitRepositoryService = $this->get('app.git.repository');
        $git_request = $gitClientService->get($parameters.'client_id='.$this->getParameter('git.client_id').'&client_secret='.$this->getParameter('git.client_secret'));
        if ($gitRepositoryService->trySend($git_request)) {
            $data = $git_request->send()->json();
        } else {
            $data = "";
            $this->addFlash('error', $gitRepositoryService->getError());
        }
        
        return $data;
    }
}
