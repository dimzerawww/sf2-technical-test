<?php

namespace AppBundle\Git;

use Guzzle\Http\Exception\RequestException;

class GitRepository
{
    /**
     * Check if data is correct and if repository username match with username
     * 
     * @param $data
     * @param $comment
     * @param $username
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function isValid($data, $comment, $username){
       if(!isset($data['owner'])
            || !isset($data['owner']['login'])
            || !$data['owner']['login'] == $username
        ) {
            $error = "Ce dépôt n'appartient pas à l'utilisateur Git saisi précédemment";
            // return ?
        } elseif(empty($comment)) {
            $error = "Veuillez saisir un commentaire";
            // return ?
        } else {
            return true;
        }
    }
    
    /**
     * Check if request will not returns error
     * 
     * @param $gitapi_request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function testSend($gitapi_request){
        try{
            $gitapi_request->send();
        } catch (RequestException $e) {
            $error = $e->getResponse()->getStatusCode();
            //return ?
        }
        
        return true;
    }
    
}

