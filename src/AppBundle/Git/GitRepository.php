<?php

namespace AppBundle\Git;

use Guzzle\Http\Exception\RequestException;

class GitRepository
{
    private $error;
    
    /**
     * Get the latest error message
     * 
     * @param $username
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function getError(){
        return $this->error;
    }
    
    /**
     * Check if data is correct and if repository username match with username
     * 
     * @param $data
     * @param $comment
     * @param $username
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function isValid($data, $username){
       if(!isset($data['owner'])
            || !isset($data['owner']['login'])
            || !$data['owner']['login'] == $username
        ) {
            $this->setError("Ce dépôt n'appartient pas à l'utilisateur Git saisi précédemment");
            return false;
        } else {
            return true;
        }
    }
    
    /**
     * Check if data is correct and if repository username match with username
     * 
     * @param $data
     * @param $comment
     * @param $username
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function validate($comment){
        if(!empty($comment)) {
            return true;
        } else {
            $this->setError("Veuillez saisir un commentaire");
            return false;
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
            $this->setError('API Erreur '.$e->getResponse()->getStatusCode());
            return false;
        }
        
        return true;
    }
    
    /**
     * Define error text
     * 
     * @param $text
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    private function setError($text){
        $this->error = $text;
        
        return $this;
    }
    
}

