<?php

namespace Tests\AppBundle\Git;

use Mockery as m;

class RepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @after
     */
    public function closeMockup(){
        m::close();
    }
    
    /**
     * get a Mock Object of Comment class
     */
    public function mockComment(){
        return m::mock('AppBundle\Entity\Comment');
    }
    
    /**
     * get a Mock Object of GitRepository class
     */
    public function mockGitRepository(){
        return m::mock('AppBundle\Git\GitRepository')->makePartial()->shouldAllowMockingProtectedMethods();
    }
    
    /**
     * Test the trySend function of GitRepository class
     */
    public function testTrySend()
    {
        
    }
    
    /**
     * Test the validate function of GitRepository class if no comment
     */
    public function testValidateIfNoComment()
    {
        $mockedGitRepository = $this->mockGitRepository();
        $mockedComment = $this->mockComment();
        
        $mockedComment->shouldReceive('getContent')->andReturn('');
        $mockedComment->shouldReceive('getUser')->andReturn('username');
        $mockedGitRepository->shouldReceive('setError')->once();
        $mockedGitRepository->validate($mockedComment, array('owner' => array('login' => 'username')));
    }
    
    /**
     * Test the validate function of GitRepository class if username not the same
     */
    public function testValidateIfUsernameNotTheSame()
    {
        $mockedGitRepository = $this->mockGitRepository();
        $mockedComment = $this->mockComment();
        
        $mockedComment->shouldReceive('getContent')->andReturn('commentaire');
        $mockedComment->shouldReceive('getUser')->andReturn('username');
        $mockedGitRepository->shouldReceive('setError')->once();
        $mockedGitRepository->validate($mockedComment, array('owner' => array('login' => 'differentusername')));
    }
    
    /**
     * Test the validate function of GitRepository class if everything is good
     */
    public function testValidateIfEverythingIsGood()
    {
        $mockedGitRepository = $this->mockGitRepository();
        $mockedComment = $this->mockComment();
        
        $mockedComment->shouldReceive('getContent')->andReturn('commentaire');
        $mockedComment->shouldReceive('getUser')->andReturn('username');
        $mockedGitRepository->shouldReceive('setError')->never();
        $mockedGitRepository->validate($mockedComment, array('owner' => array('login' => 'username')));
    }
}