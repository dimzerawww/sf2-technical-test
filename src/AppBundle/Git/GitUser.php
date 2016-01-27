<?php

namespace AppBundle\Git;

class GitUser
{
    /**
     * Check if username is string and is not empty
     * 
     * @param $username
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function isValid($username){
        if (is_string($username) && !empty($username)){
            return true;
        }
        else {
            return false;
        }
    }
}

