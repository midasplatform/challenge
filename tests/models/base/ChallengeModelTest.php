<?php
/*=========================================================================
MIDAS Server
Copyright (c) Kitware SAS. 20 rue de la Villette. All rights reserved.
69328 Lyon, FRANCE.

See Copyright.txt for details.
This software is distributed WITHOUT ANY WARRANTY; without even
the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
PURPOSE.  See the above copyright notices for more information.
=========================================================================*/
/** test Challenge model*/
class ChallengeModelTest extends DatabaseTestCase
{

  protected $modelLoad;
  protected $challengeModel;
  protected $dashboardModel;

  /** set up tests*/
  public function setUp()
    {
    $this->enabledModules = array('challenge', 'validation');
    $this->_models = array('Folder', 'Item', 'Community');
    $this->_daos = array('Folder', 'Item');
    Zend_Registry::set('modulesEnable', array());
    Zend_Registry::set('notifier', new MIDAS_Notifier(false, null));
    parent::setUp();
    $this->setupDatabase(array('challenge'), 'challenge'); // module dataset
    $this->modelLoad = new MIDAS_ModelLoader();
    $this->challengeModel = $this->modelLoad->loadModel('Challenge', 'challenge');
    $this->dashboardModel = $this->modelLoad->loadModel('Dashboard', 'validation');
    }

  /** helper method for createChallenge calls that throw exceptions. */
  function createChallengeErrorCase($user, $community, $name, $description)
    {
    try
      {
      $this->challengeModel->createChallenge($user, $community, $name, $description);
      $this->assertTrue(false, "createChallengeErrorCase expected an exception but did not get one");
      }
    catch(Zend_Exception $ze)
      {
      $this->assertTrue(true);
      }
    }


  /** testCreateChallenge */
  public function testCreateChallenge()
    {
    $this->setupDatabase(array('challenge'), 'challenge'); // module dataset
    list($noncommunityMember, $competitor1, $competitor2, $communityModerator1, $communityModerator2) = $this->loadData('User', 'challenge', '', 'challenge');
    list($challenge1Community, $challenge2Community) = $this->loadData('Community', 'challenge', '', 'challenge');

    // try to create a challenge with a non-community member
    $this->createChallengeErrorCase($noncommunityMember, $challenge1Community, "challenge1", "challenge1 description");
    // try to create a challenge with a community member
    $this->createChallengeErrorCase($competitor1, $challenge1Community, "challenge1", "challenge1 description");
    // create a challenge with a community moderator
    $challenge1 = $this->challengeModel->createChallenge($communityModerator1, $challenge1Community, "challenge1", "challenge1 description");

    // test the three folders of this challenge for permissions
    $challenge1Dashboard = $challenge1->getDashboard();
    $challenge1Testing = $challenge1Dashboard->getTesting();
    $challenge1Training = $challenge1Dashboard->getTraining();
    $challenge1Truth = $challenge1Dashboard->getTruth();

    // check that non-community members cannot read any of the the three folders
    // TODO: not sure if this is an important permission to ensure
    $this->assertFalse($this->Folder->policyCheck($challenge1Testing, $noncommunityMember, MIDAS_POLICY_READ),
            "Non-community members should not be able to read Testing folders");
    $this->assertFalse($this->Folder->policyCheck($challenge1Training, $noncommunityMember, MIDAS_POLICY_READ),
            "Non-community members should not be able to read Training folders");
    $this->assertFalse($this->Folder->policyCheck($challenge1Truth, $noncommunityMember, MIDAS_POLICY_READ),
            "Non-community members should not be able to read Truth folders");

    // check that a moderator that didn't create the folders has write access
    $this->assertTrue($this->Folder->policyCheck($challenge1Testing, $communityModerator2, MIDAS_POLICY_WRITE),
            "Challenge moderators should be able to write Testing folders");
    $this->assertTrue($this->Folder->policyCheck($challenge1Training, $communityModerator2, MIDAS_POLICY_WRITE),
            "Challenge moderators should be able to write Training folders");
    $this->assertTrue($this->Folder->policyCheck($challenge1Truth, $communityModerator2, MIDAS_POLICY_WRITE),
            "Challenge moderators should be able to write Truth folders");

    // check that community members can read the testing folder, but not write
    $this->assertTrue($this->Folder->policyCheck($challenge1Testing, $competitor1, MIDAS_POLICY_READ),
            "Challenge competitors should be able to read Testing folders");
    $this->assertFalse($this->Folder->policyCheck($challenge1Testing, $competitor1, MIDAS_POLICY_WRITE),
            "Challenge competitors should not be able to write Testing folders");

    // check that community members can read the training folder, but not write
    $this->assertTrue($this->Folder->policyCheck($challenge1Training, $competitor1, MIDAS_POLICY_READ),
            "Challenge competitors should be able to read Training folders");
    $this->assertFalse($this->Folder->policyCheck($challenge1Training, $competitor1, MIDAS_POLICY_WRITE),
            "Challenge competitors should not be able to write Training folders");

    // check that community members cannot read the truth folder
    $this->assertFalse($this->Folder->policyCheck($challenge1Truth, $competitor1, MIDAS_POLICY_READ),
            "Challenge competitors should not be able to read Truth folders");
    }


}
