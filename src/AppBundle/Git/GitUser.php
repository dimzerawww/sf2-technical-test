<?php

namespace AppBundle\Git;

class GitUser
{
    private $error;
    
    /**
     * Get the latest error message
     * 
     * @return string
     */
    public function getError(){
        return $this->error;
    }
    
    /**
     * Check if git response have user
     * 
     * @param $git_response
     * @return boolean
     */
    public function isValid($git_response){
        if (isset($git_response['total_count']) && $git_response['total_count'] > 0){
            return true;
        }
        else {
            $this->setError("Compte GitHub non trouvé");
            return false;
        }
    }
    
    /**
     * Check if username is string and is not empty
     * 
     * @param $username
     * @return boolean
     */
    public function validate($username){
        if (is_string($username) && !empty($username)){
            return true;
        }
        else {
            $this->setError("Le champ saisi n'est pas une chaine de caractères ou est vide");
            return false;
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

