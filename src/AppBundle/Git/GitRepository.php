<?php

namespace AppBundle\Git;

use Guzzle\Http\Exception\RequestException;

class GitRepository
{
    private $error = null ;
    
    /**
     * Get the latest error message
     * 
     * @param $username
     * @return string
     */
    public function getError(){
        return $this->error;
    }
    
    /**
     * Check if repository is valid
     * 
     * @return type
     */
    public function isValid(){
        return $this->error === null;
    }
    
    /**
     * Check if request will not returns error
     * 
     * @param $gitapi_request
     * @return boolean
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
     * Check if data is correct and if repository username match with username
     * 
     * @param \AppBundle\Entity\Comment $comment
     * @param type $data
     */
    public function validate(\AppBundle\Entity\Comment $comment, $data = array()){
        if(empty($comment->getContent())) {
            $this->setError("Veuillez saisir un commentaire");
        } 
        
        if(!isset($data['owner'])
            || !isset($data['owner']['login'])
            || !$data['owner']['login'] == $comment->getUser()
        ) {
            $this->setError("Ce dépôt n'appartient pas à l'utilisateur Git saisi précédemment");
        }
    }
    
    /**
     * Define error text
     * 
     * @param $text
     * @return string
     */
    private function setError($text){
        $this->error = $text;
        
        return $this;
    }
    
}

