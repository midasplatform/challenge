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
      'community_id' => array('type' => MIDAS_DATA)
       );
    $this->initialize(); // required
    }

    
  /** Create a challenge
   * @return ChallengeDao */
  function createChallenge($userDao, $communityDao, $challengeName, $challengeDescription)
    {
    if(!$communityDao instanceof CommunityDao)
      {
      throw new Zend_Exception("communityDao should be a valid instance.");
      }
    // create a new dashboard
    $modelLoad = new MIDAS_ModelLoader();
    $dashboardModel = $modelLoad->loadModel('Dashboard', 'validation');
    $this->loadDaoClass('Dashboard', 'validation');
    $dashboardDao = new Validation_DashboardDao();
    $dashboardDao->setName($challengeName);
    $dashboardDao->setDescription($challengeDescription);
    $dashboardModel->save($dashboardDao);
    $challengeModel = $modelLoad->loadModel('Challenge', 'challenge');
    $this->loadDaoClass('Challenge', 'challenge');
    $challengeDao = new Challenge_ChallengeDao();
    $challengeDao->setValidationDashboardId($dashboardDao->getDashboardId());
    $challengeDao->setCommunityId($communityDao->getCommunityId());
    $challengeModel->save($challengeDao);
    // something with user, perms?
    // probalby should be at least a moderator of the comm to do this
    // create the training folder
    // setTruth, setTraining setTesting dashboard, folder
    // 
    // create the testing folder
    // create the truth folder
    // need to set perms
    
    // for now just create 3 folders, should they be in the comm?
    // and can we have folders in a comm which are private to most members
    // in that community
    $parentFolderId = $communityDao->getPublicfolderId();
    $folderModel = $modelLoad->loadModel('Folder');
    $parentFolderDao = $folderModel->load($parentFolderId);
    $truthFolderDao = $folderModel->createFolder("Truth", "Private folder for Truth data", $parentFolderDao);
    $dashboardModel->setTruth($dashboardDao, $truthFolderDao);
        
    $trainingFolderDao = $folderModel->createFolder("Training", "Public folder for Training data", $parentFolderDao);
    $dashboardModel->setTraining($dashboardDao, $trainingFolderDao);
    
    $testingFolderDao = $folderModel->createFolder("Testing", "Public folder for Testing data", $parentFolderDao);
    $dashboardModel->setTesting($dashboardDao, $testingFolderDao);
    
    return $challengeDao;
    }

    
        
        
    
    
    
    
    
    
    
}  // end class Challenge_ChallengeModelBase