<?php

namespace AppBundle\Git;

class GitUser
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
     * Check if user is valid
     * 
     * @return boolean
     */
    public function isValid()
    {
        return $this->error === null;
    }
    
    /**
     * Check if username is string or is not empty and if git response returned a user
     * 
     * @param string $username
     * @param array $git_response
     */
    public function validate($username, $git_response)
    {
        if (!is_string($username) || empty($username)) {
            $this->setError("Le champ saisi n'est pas une chaine de caractères ou est vide");
        }
        
        if (!isset($git_response['total_count']) || $git_response['total_count'] == 0) {
            $this->setError("Compte GitHub non trouvé");
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

