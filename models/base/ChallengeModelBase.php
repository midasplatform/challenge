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
      'root_folder_id' => array('type' => MIDAS_DATA),
      'dashboard' =>  array('type' => MIDAS_MANY_TO_ONE,
                        'module' => 'validation',
                        'model' => 'Dashboard',
                        'parent_column' => 'validation_dashboard_id',
                        'child_column' => 'dashboard_id'),
      'community' =>  array('type' => MIDAS_MANY_TO_ONE,
                        'model' => 'Community',
                        'parent_column' => 'community_id',
                        'child_column' => 'community_id'),
      'root_folder' =>  array('type' => MIDAS_MANY_TO_ONE,
                        'model' => 'Folder',
                        'parent_column' => 'root_folder_id',
                        'child_column' => 'folder_id')


       );
    $this->initialize(); // required
    }

  /** Returns challenge(s) by a communityId */
  abstract function getByCommunityId($communityId);

  abstract function findAvailableChallenges($userDao, $status);
  
  abstract function getUsersWithSubmittedResults($challengeId);
  


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

    
    protected function createPermissions($folderpolicygroupModel, $folderpolicyuserModel, $folder, $folderPermissions)
      {
      foreach($folderPermissions['group'] as $groupPolicy)
        {
        $group = $groupPolicy[0];
        $policy = $groupPolicy[1];
        if($policy !== false)
          {
          $createdPolicy = $folderpolicygroupModel->createPolicy($group, $folder, $policy);
          }
        }
      if(array_key_exists('user', $folderPermissions))
        {
        $user = $folderPermissions['user']['user_dao'];
        $policy = $folderPermissions['user']['policy'];
        if($policy !== false)
          {
          $folderpolicyuserModel->createPolicy($user, $folder, $policy);  
          }
        }
      }
    
    protected function enforcePermissions($folderpolicygroupModel, $folderpolicyuserModel, $folder, $folderPermissions)
      {
      foreach($folderPermissions['group'] as $groupPolicy)
        {
        $group = $groupPolicy[0];
        $policy = $groupPolicy[1];
        $policyGroup = $folderpolicygroupModel->getPolicy($group, $folder);
        if($policy === false)
          {
          if($policyGroup !== false)
            {
            throw new Zend_Exception('Folder id['.$folder->getFolderId().'] should have no policy for group['.$group->getName().']');
            }
          }
        else if($policyGroup->getPolicy() != $policy)
          {
          throw new Zend_Exception('Folder id['.$folder->getFolderId().'] should have policy['.$policy.'] for group['.$group->getName().']');
          }
        }
      if(array_key_exists('user', $folderPermissions))
        {
        $user = $folderPermissions['user']['user_dao'];
        $desiredPolicy = $folderPermissions['user']['policy'];
        $actualPolicy = $folderpolicyuserModel->getPolicy($user, $folder);
        if($actualPolicy === false)
          {
          if($desiredPolicy !== false)
            {
            throw new Zend_Exception('Folder id['.$folder->getFolderId().'] should have policy['.$desiredPolicy.'] for user['.$user->getUserId().'] but has policy['.$actualPolicy.']');  
            }
          }
        else if($actualPolicy->getPolicy() != $desiredPolicy)
          {
          throw new Zend_Exception('Folder id['.$folder->getFolderId().'] should have policy['.$desiredPolicy.'] for user['.$user->getUserId().'] but has policy['.$actualPolicy->getPolicy().']');  
          }
        }
      }
    
    protected function createOrEnforceSubfolders($rootFolder, $subfolders, $folderModel, $folderpolicygroupModel, $folderpolicyuserModel)
      {
      foreach($subfolders as $folderName => $folderProperties)
        {
        $subfolder = $folderModel->getFolderExists($folderName, $rootFolder);
        if($subfolder === false)
          {
          $subfolder = $folderModel->createFolder($folderName, $folderName, $rootFolder);
          $this->createPermissions($folderpolicygroupModel, $folderpolicyuserModel, $subfolder, $folderProperties['permissions']);
          }
        else
          {
          $this->enforcePermissions($folderpolicygroupModel, $folderpolicyuserModel, $subfolder, $folderProperties['permissions']);
          }
        if(isset($folderProperties['subfolders']))
          {
          // recursively call this with the subfolder as root
          $this->createOrEnforceSubfolders($subfolder, $folderProperties['subfolders'], $folderModel, $folderpolicygroupModel, $folderpolicyuserModel);
          }
        }
      }
    
    
    
  /** Create a challenge
   * @return ChallengeDao */
  function createChallenge($userDao, $communityDao, $challengeName, $challengeDescription, $folderId)
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
    $folderpolicygroupModel = $modelLoad->loadModel('Folderpolicygroup');
    $groupModel = $modelLoad->loadModel('Group');

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

    $anonymousGroup = $groupModel->load(MIDAS_GROUP_ANONYMOUS_KEY);

    // challenge top level folder

    $adminGroup = $communityDao->getAdminGroup();
    $moderatorGroup = $communityDao->getModeratorGroup();
    $memberGroup = $communityDao->getMemberGroup();
    $rootFolderPermissions = array('group' => array(
                                   array($adminGroup, MIDAS_POLICY_ADMIN),
                                   array($moderatorGroup, MIDAS_POLICY_WRITE),
                                   array($memberGroup, MIDAS_POLICY_READ),
                                   array($anonymousGroup, false)));
    
    
    if($folderId)
      {
      // a folder is already supplied
      $rootFolderDao = $folderModel->load($folderId);
      // now enforce a few properties
      // existence
      if(!$rootFolderDao)
        {
        throw new Zend_Exception("A folder corresponding to folderId must exist.");
        }
      $communityRoot = $folderModel->getRoot($rootFolderDao);
      // in the community
      if($communityRoot->getFolderId() !== $communityDao->getFolderId())
        {
        throw new Zend_Exception("folder for folderId must be in community for communityId.");
        }
      // permissions
      $this->enforcePermissions($folderpolicygroupModel, $rootFolderDao, $rootFolderPermissions);
      }
    else
      {
      // create a new folder under the community public folder with the name of the challenge
      $publicFolder = $communityDao->getPublicFolder();
      $rootFolderDao = $folderModel->createFolder($challengeName, "Root folder for ".$challengeName, $publicFolder);
      $this->createPermissions($folderpolicygroupModel, $rootFolderDao, $rootFolderPermissions);
      }
    $challengeDao->setRootFolderId($rootFolderDao->getFolderId());      
    $challengeModel->save($challengeDao);
    
    // Create subfolders
    $testingTruthPermissions = array('group' => array(
                                   array($adminGroup, MIDAS_POLICY_ADMIN),
                                   array($moderatorGroup, MIDAS_POLICY_WRITE),
                                   array($memberGroup, false),
                                   array($anonymousGroup, false)));
    
    $subfolders = array(MIDAS_CHALLENGE_TESTING => 
                            array("permissions" => $rootFolderPermissions,
                                  "subfolders" => array(
                                      MIDAS_CHALLENGE_IMAGES => array("permissions" => $rootFolderPermissions),
                                      MIDAS_CHALLENGE_TRUTH => array("permissions" => $testingTruthPermissions))),
                        MIDAS_CHALLENGE_TRAINING => 
                            array("permissions" => $rootFolderPermissions,
                                  "subfolders" => array(
                                      MIDAS_CHALLENGE_IMAGES => array("permissions" => $rootFolderPermissions),
                                      MIDAS_CHALLENGE_TRUTH => array("permissions" => $rootFolderPermissions))));
    
    $this->createOrEnforceSubfolders($rootFolderDao, $subfolders, $folderModel, $folderpolicygroupModel);
    
    // set the testing and training folders in the validation dashboard
    $testing = $folderModel->getFolderExists("Testing", $rootFolderDao);
    $training = $folderModel->getFolderExists("Training", $rootFolderDao);
    $dashboardDao->setTestingfolderId($testing->getFolderId());
    $dashboardDao->setTrainingfolderId($training->getFolderId());
    $dashboardModel->save($dashboardDao);
    
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
   * generate the names of expected results items based on Truth items.
   * @param userDao
   * @param challengeDao
   * @param resultsType one of [Testing|Training]
   * @param resultsFolderId id of folder to test
   * 
   * @return 3 lists: resultsWithoutTruth, truthWithoutResults, matchedTruthResults
   */
  function getExpectedResultsItems($userDao, $challengeDao, $resultsType, $resultFolderId = null)
    {
    $modelLoad = new MIDAS_ModelLoader();
    $folderModel = $modelLoad->loadModel('Folder');

    // get all the items in the correct subfolder's Truth subfolder
    $dashboardDao = $challengeDao->getDashboard();
    if($resultsType === MIDAS_CHALLENGE_TRAINING)
      {
      $subfolder = $dashboardDao->getTraining();  
      }
    else
      {
      $subfolder = $dashboardDao->getTesting();  
      }
    $truthFolder = $folderModel->getFolderExists(MIDAS_CHALLENGE_TRUTH, $subfolder);
    if(!$truthFolder)
      {
      throw new Zend_Exception('Cannot find truth folder under folderId['.$subfolder->getFolderId().']');
      }
    
    $truthItems = $folderModel->getItemsFiltered($truthFolder, $userDao, MIDAS_POLICY_READ);
    $truthResults = array();
    foreach($truthItems as $item)
      {
      $name = $item->getName();
      $truthResults[$name] = str_replace("_truth", "_result", $name);
      }
    
    if($resultFolderId === null)
      {
      return $truthResults;
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
      $resultsWithoutTruth = array();
      foreach($resultsItems as $item)
        {
        $resultsItemName = $item->getName();
        $resultsItemNames[$resultsItemName] = $resultsItemName;
        if(!in_array($resultsItemName, $truthResults))
          {
          $resultsWithoutTruth[] = $resultsItemName;
          }
        }
      $truthWithoutResults = array();
      $matchedTruthResults = array();
      foreach($truthResults as $truthItem => $resultItem)
        {
        if(in_array($resultItem, $resultsItemNames))
          {
          $matchedTruthResults[$truthItem] = $resultItem;
          }
        else
          {
          $truthWithoutResults[$truthItem] = $resultItem;
          }
        }
      return array('resultsWithoutTruth' => $resultsWithoutTruth, 'truthWithoutResults' => $truthWithoutResults, 'matchedTruthResults' => $matchedTruthResults);
      }
    }
    
    
  function addUserToChallenge($userDao, $challenge) 
    {
    if(!$userDao)
      {
      throw new Exception('User must be logged in to be added to a challenge.');
      }
    if(!$userDao instanceof UserDao)
      {
      throw new Zend_Exception("userDao should be a valid instance.");
      }
    $communityDao = $challenge->getCommunity();
      
    if(!$communityDao instanceof CommunityDao)
      {
      throw new Zend_Exception("communityDao should be a valid instance.");
      }

    $modelLoad = new MIDAS_ModelLoader();
    $communityModel = $modelLoad->loadModel('Community');
    $folderModel = $modelLoad->loadModel('Folder');
    $folderpolicygroupModel = $modelLoad->loadModel('Folderpolicygroup');
    $folderpolicyuserModel = $modelLoad->loadModel('Folderpolicyuser');
    $groupModel = $modelLoad->loadModel('Group');

    if(!$communityModel->policyCheck($communityDao, $userDao, MIDAS_POLICY_READ))
      {
      throw new Zend_Exception('User must be a member of the community to join the challenge.');
      }

    $adminGroup = $communityDao->getAdminGroup();
    $moderatorGroup = $communityDao->getModeratorGroup();
    $memberGroup = $communityDao->getMemberGroup();
    $anonymousGroup = $groupModel->load(MIDAS_GROUP_ANONYMOUS_KEY);

    $userFolderPermissions = array('group' => array(array($adminGroup, MIDAS_POLICY_READ),
                                   array($moderatorGroup, MIDAS_POLICY_READ),
                                   array($memberGroup, false),
                                   array($anonymousGroup, false)),
                                   'user' => array('user_dao' => $userDao, 'policy' => MIDAS_POLICY_ADMIN));  
    
      
    // create a top level folder in the User's private area named $challengeName_data
    $subfolders = array($challenge->getDashboard()->getName() . " data" => 
                            array("permissions" => $userFolderPermissions,
                                  "subfolders" => array(
                                      MIDAS_CHALLENGE_TRAINING . ' results' => array("permissions" => $userFolderPermissions),
                                      MIDAS_CHALLENGE_TRAINING . ' output' => array("permissions" => $userFolderPermissions),
                                      MIDAS_CHALLENGE_TESTING . ' results' => array("permissions" => $userFolderPermissions),
                                      MIDAS_CHALLENGE_TESTING . ' output' => array("permissions" => $userFolderPermissions))));
                                      
    $this->createOrEnforceSubfolders($userDao->getPrivateFolder(), $subfolders, $folderModel, $folderpolicygroupModel, $folderpolicyuserModel);
    }
    



}  // end class Challenge_ChallengeModelBase
