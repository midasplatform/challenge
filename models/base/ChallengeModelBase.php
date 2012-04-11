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
include_once BASE_PATH . '/modules/challenge/constant/module.php';
/** ChallengeModel Base class */
abstract class Challenge_ChallengeModelBase extends Challenge_AppModel {



  /**
   * constructor
   */
  public function __construct()
    {
    parent::__construct();
    $this->_name = 'challenge_challenge';
    $this->_key = 'challenge_id';
    $this->_daoName = 'ChallengeDao';

    $this->_mainData = array(
      'challenge_id' => array('type' => MIDAS_DATA),
      'validation_dashboard_id' => array('type' => MIDAS_DATA),
      'community_id' => array('type' => MIDAS_DATA),
      'status' => array('type' => MIDAS_DATA),
      'dashboard' =>  array('type' => MIDAS_MANY_TO_ONE,
                        'module' => 'validation',
                        'model' => 'Dashboard',
                        'parent_column' => 'validation_dashboard_id',
                        'child_column' => 'dashboard_id'),
      'community' =>  array('type' => MIDAS_MANY_TO_ONE,
                        'model' => 'Community',
                        'parent_column' => 'community_id',
                        'child_column' => 'community_id')

       );
    $this->initialize(); // required
    }

  /** Returns challenge(s) by a communityId */
  abstract function getByCommunityId($communityId);

  abstract function findAvailableChallenges($userDao, $status);

  /**
   * checks whether the user is a moderator of the challenge
   */
  function isChallengeModerator($userDao, $challengeDao)
    {
    if(!$userDao)
      {
      throw new Exception('You must be logged in.');
      }
    if(!$userDao instanceof UserDao)
      {
      throw new Zend_Exception("userDao should be a valid instance.");
      }
    if(!$challengeDao)
      {
      throw new Exception('Invalid instance of a challenge.');
      }
    if(!$challengeDao instanceof Challenge_ChallengeDao)
      {
      throw new Exception('Invalid instance of a challenge.');
      }

    $modelLoad = new MIDAS_ModelLoader();
    $communityModel = $modelLoad->loadModel('Community');

    $communityDao = $communityModel->load($challengeDao->getCommunityId());
    if(!$communityDao)
      {
      throw new Exception('Challenge is not linked with a valid community.');
      }
    if(!$communityDao instanceof CommunityDao)
      {
      throw new Exception('Challenge is not linked with a valid community.');
      }

    return $communityModel->policyCheck($communityDao, $userDao, MIDAS_POLICY_WRITE);
    }

  /** Create a challenge
   * @return ChallengeDao */
  function createChallenge($userDao, $communityDao, $challengeName, $challengeDescription)
    {
    if(!$userDao)
      {
      throw new Exception('You must be logged in to create a challenge');
      }
    if(!$userDao instanceof UserDao)
      {
      throw new Zend_Exception("userDao should be a valid instance.");
      }

    if(!$communityDao instanceof CommunityDao)
      {
      throw new Zend_Exception("communityDao should be a valid instance.");
      }

    $modelLoad = new MIDAS_ModelLoader();
    $communityModel = $modelLoad->loadModel('Community');
    $dashboardModel = $modelLoad->loadModel('Dashboard', 'validation');
    $dashboardModel->loadDaoClass('DashboardDao', 'validation');
    $challengeModel = $modelLoad->loadModel('Challenge', 'challenge');
    $challengeModel->loadDaoClass('ChallengeDao', 'challenge');
    $folderModel = $modelLoad->loadModel('Folder');
    $folderpolicyggroupModel = $modelLoad->loadModel('Folderpolicygroup');

    if(!$communityModel->policyCheck($communityDao, $userDao, MIDAS_POLICY_ADMIN))
      {
      throw new Zend_Exception('You must be an administrator of this community to create a challenge');
      }

    // create a new dashboard
    $dashboardDao = new Validation_DashboardDao();
    $dashboardDao->setName($challengeName);
    $dashboardDao->setDescription($challengeDescription);
    $dashboardDao->setOwnerId($userDao->getUserId());
    $dashboardModel->save($dashboardDao);

    // create a new challenge
    $challengeDao = new Challenge_ChallengeDao();
    $challengeDao->setValidationDashboardId($dashboardDao->getDashboardId());
    $challengeDao->setCommunityId($communityDao->getCommunityId());
    // closed by default
    $challengeDao->setStatus(MIDAS_CHALLENGE_STATUS_CLOSED);
    $challengeModel->save($challengeDao);

    // create 3 folders in the community, Truth, Testing, Training
    // when folders are created programmitically, there are no default perms
    // enabling community moderators or members to access the folders
    // so these perms must be added as needed.
    $topFolderId = $communityDao->getFolderId();
    $topFolderDao = $folderModel->load($topFolderId);
    $moderatorGroup = $communityDao->getModeratorGroup();
    $memberGroup = $communityDao->getMemberGroup();

    // Testing is writable by moderators and readable by members
    $testingFolderDao = $folderModel->createFolder("Testing", "Public folder for Testing data", $topFolderDao);
    $dashboardModel->setTesting($dashboardDao, $testingFolderDao);
    $testingFolderModeratorsWritePolicy = $folderpolicyggroupModel->createPolicy($moderatorGroup, $testingFolderDao, MIDAS_POLICY_WRITE);
    $testingFolderMembersReadPolicy = $folderpolicyggroupModel->createPolicy($memberGroup, $testingFolderDao, MIDAS_POLICY_READ);

    // Training is writable by moderators and readable by members
    $trainingFolderDao = $folderModel->createFolder("Training", "Public folder for Training data", $topFolderDao);
    $dashboardModel->setTraining($dashboardDao, $trainingFolderDao);
    $trainingFolderModeratorsWritePolicy = $folderpolicyggroupModel->createPolicy($moderatorGroup, $trainingFolderDao, MIDAS_POLICY_WRITE);
    $trainingFolderMembersReadPolicy = $folderpolicyggroupModel->createPolicy($memberGroup, $trainingFolderDao, MIDAS_POLICY_READ);

    // Truth is writable by moderators and not readable by anyone else
    $truthFolderDao = $folderModel->createFolder("Truth", "Private folder for Truth data", $topFolderDao);
    $dashboardModel->setTruth($dashboardDao, $truthFolderDao);
    $truthFolderModeratorsWritePolicy = $folderpolicyggroupModel->createPolicy($moderatorGroup, $truthFolderDao, MIDAS_POLICY_WRITE);

    return $challengeDao;
    }  // createChallenge

  /**
   * Open a challenge
   **/
  function openChallenge($userDao, $challengeId)
    {
    $challengeDao = $this->load($challengeId);
    if(!$this->isChallengeModerator($userDao, $challengeDao))
      {
      throw new Zend_Exception("You must be a moderator of this challenge.");
      }

    $challengeDao->setStatus(MIDAS_CHALLENGE_STATUS_OPEN);
    $this->save($challengeDao);
    }

  /**
   * Close a challenge
   **/
  function closeChallenge($userDao, $challengeId)
    {
    $challengeDao = $this->load($challengeId);
    if(!$this->isChallengeModerator($userDao, $challengeDao))
      {
      throw new Zend_Exception("You must be a moderator of this challenge.");
      }

    $challengeDao->setStatus(MIDAS_CHALLENGE_STATUS_CLOSED);
    $this->save($challengeDao);
    }

  /**
   * ensure the challenge is valid and the user is a member.
   * return an array of challengeDao, communityDao and memberGroupDao if all
   * conditions are valid,
   * otherwise throws an exception relevant to the condition violated.
   */
  function validateChallengeUser($userDao, $challengeId)
    {
    $modelLoad = new MIDAS_ModelLoader();
    $groupModel = $modelLoad->loadModel('Group');

    $challengeDao = $this->load($challengeId);
    if(!$challengeDao)
      {
      throw new Zend_Exception('You must enter a valid challenge.');
      }

    // check that the community is valid and the user is a member
    $communityDao = $challengeDao->getCommunity();
    if(!$communityDao)
      {
      throw new Zend_Exception('This challenge does not have a valid community');
      }

    $memberGroupDao = $communityDao->getMemberGroup();
    if(!$groupModel->userInGroup($userDao, $memberGroupDao))
      {
      throw new Zend_Exception('You must join this community to view the challenge');
      }

    return array($challengeDao, $communityDao, $memberGroupDao);
    }


  /**
   * validates that a user has the right permissions for a results folder,
   * adds read permissions on the folder for community moderators.
   *
   * Will return the resultsFolderDao or else throw an exception describing
   * the condition violated.
   */
  function validateChallengeUserFolder($userDao, $communityDao, $folderId)
    {
    $modelLoad = new MIDAS_ModelLoader();
    $groupModel = $modelLoad->loadModel('Group');
    $folderModel = $modelLoad->loadModel('Folder');
    $folderpolicyuserModel = $modelLoad->loadModel('Folderpolicyuser');
    $folderpolicygroupModel = $modelLoad->loadModel('Folderpolicygroup');

    // get the results folder
    $folderDao = $folderModel->load($folderId);

    // ensure user has ownership/admin
    $folderpolicyuserDao = $folderpolicyuserModel->getPolicy($userDao, $folderDao);
    if($folderpolicyuserDao->getPolicy() != MIDAS_POLICY_ADMIN)
      {
      throw new Zend_Exception('You must have admin rights to this folder to submit it as a results folder.');
      }

    // ensure that anonymous users cannot access the folder
    $anonymousgroupDao = $groupModel->load(MIDAS_GROUP_ANONYMOUS_KEY);
    $anonymousfolderpolicygroupDao = $folderpolicygroupModel->getPolicy($anonymousgroupDao, $folderDao);
    if($anonymousfolderpolicygroupDao)
      {
      throw new Zend_Exception('You must remove anonymous access to this results folder');
      }

    // ensure that community members cannot access the folder
    $memberGroup = $communityDao->getMemberGroup();
    $membersfolderpolicygroupDao = $folderpolicygroupModel->getPolicy($memberGroup, $folderDao);
    if($membersfolderpolicygroupDao)
      {
      throw new Zend_Exception('You must remove challenge community members access to this results folder');
      }

    // add read access to challenge community moderators for the folder
    $moderatorGroup = $communityDao->getModeratorGroup();
    $moderatorReadPolicy = $folderpolicygroupModel->createPolicy($moderatorGroup, $folderDao, MIDAS_POLICY_READ);
    if(!$moderatorReadPolicy)
      {
      throw new Zend_Exception('Cannot add read access to challenge moderators to your results folder.');
      }

    return $folderDao;
    }

  /**
   * generate the names of expected results items based on testing folder items.
   * @param type $testingItems
   * @return string
   */
  function getExpectedResultsItems($userDao, $challengeDao, $resultFolderId = null) //$testingItems, $resultsItems = null)
    {
    $modelLoad = new MIDAS_ModelLoader();
    //$challengeModel = $modelLoad->loadModel('Challenge', 'challenge');
    //$communityModel = $modelLoad->loadModel('Community');
    //$groupModel = $modelLoad->loadModel('Group');
    //$dashboardModel = $modelLoad->loadModel('Dashboard', 'validation');
    $folderModel = $modelLoad->loadModel('Folder');


    // get all the items in the Testing folder
    $dashboardDao = $challengeDao->getDashboard();
    $testingFolderDao = $dashboardDao->getTesting();
    $testingItems = $folderModel->getItemsFiltered($testingFolderDao, $userDao, MIDAS_POLICY_READ);
    $testingResults = array();
    foreach($testingItems as $item)
      {
      $name = $item->getName();
      $testingResults[$name] = "result_" . $name;
      }

    if($resultFolderId === null)
      {
      return $testingResults;
      }
    else
      {
      // get all items in Results folder
      $resultsFolder = $folderModel->load($resultFolderId);
      if(!$resultsFolder)
        {
        throw new Zend_Exception("The results folder is invalid");
        }
      $resultsItems = $folderModel->getItemsFiltered($resultsFolder, $userDao, MIDAS_POLICY_READ);
      $resultsItemNames = array();
      $resultsWithoutTesting = array();
      foreach($resultsItems as $item)
        {
        $resultsItemName = $item->getName();
        $resultsItemNames[$resultsItemName] = $resultsItemName;
        if(!in_array($resultsItemName, $testingResults))
          {
          $resultsWithoutTesting[] = $resultsItemName;
          }
        }
      $testingWithoutResults = array();
      $matchedTestingResults = array();
      foreach($testingResults as $testItem => $resultItem)
        {
        if(in_array($resultItem, $resultsItemNames))
          {
          $matchedTestingResults[$testItem] = $resultItem;
          }
        else
          {
          $testingWithoutResults[$testItem] = $resultItem;
          }
        }
      return array('resultsWithoutTesting' => $resultsWithoutTesting, 'testingWithoutResults' => $testingWithoutResults, 'matchedTestingResults' => $matchedTestingResults);
      }
    }



}  // end class Challenge_ChallengeModelBase