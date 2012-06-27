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
/** CompetitorModel Base class */
class Challenge_CompetitorModelBase extends Challenge_AppModel {



  /**
   * constructor
   */
  public function __construct()
    {
    parent::__construct();
    $this->_name = 'challenge_competitor';
    $this->_key = 'challenge_competitor_id';
    $this->_daoName = 'CompetitorDao';
    $this->_mainData = array(
      'challenge_competitor_id' => array('type' => MIDAS_DATA),
      'challenge_id' => array('type' => MIDAS_DATA),
      'user_id' => array('type' => MIDAS_DATA),
      'training_submission_folder_id' => array('type' => MIDAS_DATA),
      'training_output_folder_id' => array('type' => MIDAS_DATA),
      'testing_submission_folder_id' => array('type' => MIDAS_DATA),
      'testing_output_folder_id' => array('type' => MIDAS_DATA),
      'challenge' =>  array('type' => MIDAS_MANY_TO_ONE,
                        'module' => 'challenge',
                        'model' => 'Challenge',
                        'parent_column' => 'challenge_id',
                        'child_column' => 'challenge_id'),
      'training_submission_folder' =>  array('type' => MIDAS_MANY_TO_ONE,
                        'model' => 'Folder',
                        'parent_column' => 'training_submissiont_folder_id',
                        'child_column' => 'folder_id'),
      'training_output_folder' =>  array('type' => MIDAS_MANY_TO_ONE,
                        'model' => 'Folder',
                        'parent_column' => 'training_output_folder_id',
                        'child_column' => 'folder_id'),
      'testing_submission_folder' =>  array('type' => MIDAS_MANY_TO_ONE,
                        'model' => 'Folder',
                        'parent_column' => 'testing_submission_folder_id',
                        'child_column' => 'folder_id'),
      'testing_output_folder' =>  array('type' => MIDAS_MANY_TO_ONE,
                        'model' => 'Folder',
                        'parent_column' => 'testing_output_folder_id',
                        'child_column' => 'folder_id')
       );
    $this->initialize(); // required
    }
    
  /** Create a ResultsRun
   * @return ResultsRunDao */
  function createResultsRun($userDao, $challengeId, $resultsType, $batchmakeTaskId, $resultsFolderId, $outputFolderId)
    {
    $modelLoad = new MIDAS_ModelLoader();
    $challengeModel = $modelLoad->loadModel('Challenge', 'challenge');
    $communityModel = $modelLoad->loadModel('Community');
    $folderModel = $modelLoad->loadModel('Folder');
    $folderpolicyggroupModel = $modelLoad->loadModel('Folderpolicygroup');
    $folderpolicyuserModel = $modelLoad->loadModel('Folderpolicyuser');
    $this->loadDaoClass('ResultsRunDao', 'challenge');

    // create a new resultsrun
    $resultsrunDao = new Challenge_ResultsRunDao();
    $resultsrunDao->setResultsType($resultsType);
    $resultsrunDao->setChallengeId($challengeId);
    $resultsrunDao->setBatchmakeTaskId($batchmakeTaskId);
    $resultsrunDao->setResultsFolderId($resultsFolderId);

    $this->save($resultsrunDao);

    // now that we have saved, we can get the results run id

    // create a new child folder of the output folder, this is where any outputs
    // will live.
    $challengeDao = $challengeModel->load($challengeId);
    $communityDao = $challengeDao->getCommunity();
    $outputFolderParentDao = $folderModel->load($outputFolderId);
    $outputFolderDao = $folderModel->createFolder("Results Output " . $resultsrunDao->getChallengeResultsRunId(), "Output folder from running results", $outputFolderParentDao);

    // give user ownership rights
    $folderpolicyuserModel->createPolicy($userDao, $outputFolderDao, MIDAS_POLICY_ADMIN);

    // give community moderators read access
    $moderatorGroup = $communityDao->getModeratorGroup();
    $outputFolderModeratorsReadPolicy = $folderpolicyggroupModel->createPolicy($moderatorGroup, $outputFolderDao, MIDAS_POLICY_READ);

    $resultsrunDao->setOutputFolderId($outputFolderDao->getFolderId());
    $this->save($resultsrunDao);

    return $resultsrunDao;
    }







}  // end class Challenge_CompetitorModelBase