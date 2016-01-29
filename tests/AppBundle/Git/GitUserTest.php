<?php

namespace Tests\AppBundle\Git;

use Mockery as m;

class UserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @after
     */
    public function closeMockup(){
        m::close();
    }
    
    /**
     * get a Mock Object of GitUser class
     */
    public function mockGitUser(){
        return m::mock('AppBundle\Git\GitUser')->makePartial()->shouldAllowMockingProtectedMethods();
    }
    
    /**
     * Test the validate function of GitUser class if user not exist
     */
    public function testValidateIfUserNotExist()
    {
        $mockedGitUser = $this->mockGitUser();
        $mockedGitUser->shouldReceive('setError')->once();
        $mockedGitUser->validate('usernotexist', array('total_count' => 0));
    }

    /**
     * Test the validate function of GitUser class if no username
     */
    public function testValidateIfNoUsername()
    {
        $mockedGitUser = $this->mockGitUser();
        $mockedGitUser->shouldReceive('setError')->twice();
        $mockedGitUser->validate('', array('total_count' => 0));
    } 
    
    /**
     * Test the validate function of GitUser class if no username and no data
     */
    public function testValidateIfNoUsernameAndNoData()
    {
        $mockedGitUser = $this->mockGitUser();
        $mockedGitUser->shouldReceive('setError')->twice();
        $mockedGitUser->validate('', array());
    } 
    
    /**
     * Test the validate function of GitUser class if everything is good
     */
    public function testValidateIfEverythingIsGood()
    {
        $mockedGitUser = $this->mockGitUser();
        $mockedGitUser->shouldReceive('setError')->never();
        $mockedGitUser->validate('userexists', array('total_count' => 1));
    }    
}

