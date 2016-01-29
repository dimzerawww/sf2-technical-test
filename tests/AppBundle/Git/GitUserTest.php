<?php

namespace Tests\AppBundle\Git;

use AppBundle\Git\GitUser;
use Mockery as m;

class UserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * get a Mock Object of GitUser class
     */
    public function getMockObject(){
        return m::mock('AppBundle\Git\GitUser')->makePartial()->shouldAllowMockingProtectedMethods();
    }
    /**
     * @after
     */
    public function closeMockup(){
        m::close();
    }
    
    public function testValidate()
    {
        $mockOne = $this->getMockObject();
        $mockOne->shouldReceive('setError')->once();
        $mockOne->validate('usernotexist', array('total_count' => 0));
        
        $mockTwo = $this->getMockObject();
        $mockTwo->shouldReceive('setError')->twice();
        $mockTwo->validate('', array('total_count' => 0));
        
        $mockThree = $this->getMockObject();
        $mockThree->shouldReceive('setError')->never();
        $mockThree->validate('userexists', array('total_count' => 1));
        
    }
}

