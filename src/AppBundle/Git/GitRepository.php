<?php

namespace AppBundle\Git;

use Guzzle\Http\Exception\RequestException;
use Guzzle\Http\Message\RequestInterface;
use AppBundle\Entity\Comment;

class GitRepository
{
    private $error = null;
    
    /**
     * Get the latest error message
     * 
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }
    
    /**
     * Check if repository is valid
     * 
     * @return boolean
     */
    public function isValid()
    {
        return $this->error === null;
    }
    
    /**
     * Check if request will not returns error
     * 
     * @param RequestInterface $gitapi_request 
     * @return boolean
     */
    public function trySend(RequestInterface $gitapi_request)
    {
        try {
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
     * @param Comment $comment
     * @param array $data
     */
    public function validate(Comment $comment, $data = array())
    {
        if (empty($comment->getContent())) {
            $this->setError("Veuillez saisir un commentaire");
        } 
        
        if (!isset($data['owner'])
            || !isset($data['owner']['login'])
            || !$data['owner']['login'] == $comment->getUser()
        ) {
            $this->setError("Ce dépôt n'appartient pas à l'utilisateur Git saisi précédemment");
        }
    }
    
    /**
     * Define error text
     * 
     * @param string $text
     * @return string
     */
    private function setError($text)
    {
        $this->error = $text;
        
        return $this;
    }
    
}

