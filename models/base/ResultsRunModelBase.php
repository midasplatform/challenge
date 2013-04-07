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
/** ResultsRunModel Base class */
abstract class Challenge_ResultsRunModelBase extends Challenge_AppModel {



  /**
   * constructor
   */
  public function __construct()
    {
    parent::__construct();
    $this->_name = 'challenge_results_run';
    $this->_key = 'challenge_results_run_id';
    $this->_daoName = 'ResultsRunDao';

    $this->_mainData = array(
      'challenge_results_run_id' => array('type' => MIDAS_DATA),
      'challenge_id' => array('type' => MIDAS_DATA),
      'results_type' => array('type' => MIDAS_DATA),
      'date' => array('type' => MIDAS_DATA),
      'batchmake_task_id' => array('type' => MIDAS_DATA),
      'results_folder_id' => array('type' => MIDAS_DATA),
      'output_folder_id' => array('type' => MIDAS_DATA),
      'challenge_competitor_id' => array('type' => MIDAS_DATA),
      'status' => array('type' => MIDAS_DATA),
      'submission_name' => array('type' => MIDAS_DATA),
      'scoreboard_name' => array('type' => MIDAS_DATA),
      'challenge' =>  array('type' => MIDAS_MANY_TO_ONE,
                        'module' => 'challenge',
                        'model' => 'Challenge',
                        'parent_column' => 'challenge_id',
                        'child_column' => 'challenge_id'),
      'competitor' =>  array('type' => MIDAS_MANY_TO_ONE,
                        'module' => 'challenge',
                        'model' => 'Competitor',
                        'parent_column' => 'challenge_competitor_id',
                        'child_column' => 'challenge_competitor_id'),
      'batchmake_task' =>  array('type' => MIDAS_MANY_TO_ONE,
                        'module' => 'batchmake',
                        'model' => 'Task',
                        'parent_column' => 'batchmake_task_id',
                        'child_column' => 'batchmake_task_id'),
      'results_folder' =>  array('type' => MIDAS_MANY_TO_ONE,
                        'model' => 'Folder',
                        'parent_column' => 'results_folder_id',
                        'child_column' => 'folder_id'),
      'output_folder' =>  array('type' => MIDAS_MANY_TO_ONE,
                        'model' => 'Folder',
                        'parent_column' => 'output_folder_id',
                        'child_column' => 'folder_id')
       );
    $this->initialize(); // required
    }

  /** Create a ResultsRun
   * @return ResultsRunDao */
  function createResultsRun($userDao, $challengeId, $resultsType, $batchmakeTaskId, $resultsFolderId, $outputFolderId)
    {
    $challengeModel = MidasLoader::loadModel('Challenge', 'challenge');
    $competitorModel = MidasLoader::loadModel('Competitor', 'challenge');
    $communityModel = MidasLoader::loadModel('Community');
    $folderModel = MidasLoader::loadModel('Folder');
    $folderpolicyggroupModel = MidasLoader::loadModel('Folderpolicygroup');
    $folderpolicyuserModel = MidasLoader::loadModel('Folderpolicyuser');
    $this->loadDaoClass('ResultsRunDao', 'challenge');
    $competitor = $competitorModel->findChallengeCompetitor($userDao->getUserId(), $challengeId);
    // create a new resultsrun
    $resultsrunDao = new Challenge_ResultsRunDao();
    $resultsrunDao->setResultsType($resultsType);
    $resultsrunDao->setChallengeId($challengeId);
    $resultsrunDao->setChallengeCompetitorId($competitor->getChallengeCompetitorId());
    $resultsrunDao->setBatchmakeTaskId($batchmakeTaskId);
    $resultsrunDao->setResultsFolderId($resultsFolderId);
    $resultsrunDao->setStatus(MIDAS_CHALLENGE_RR_STATUS_CREATED);
    $resultsrunDao->setSubmissionName($submissionName);
    $challenge = $challengeModel->load($challengeId);
    $resultsrunDao->setScoreboardName($challenge->getCurrentScoreboardStage());

    $this->save($resultsrunDao);

    // now that we have saved, we can get the results run id

    // create a new child folder of the output folder, this is where any outputs
    // will live.
    $challengeDao = $challengeModel->load($challengeId);
    $communityDao = $challengeDao->getCommunity();
    if($outputFolderId)
      {
      $outputFolderParentDao = $folderModel->load($outputFolderId);
      $outputFolderDao = $folderModel->createFolder("Results Output " . $resultsrunDao->getChallengeResultsRunId(), "Output folder from running results", $outputFolderParentDao);

      // give user ownership rights
      $folderpolicyuserModel->createPolicy($userDao, $outputFolderDao, MIDAS_POLICY_ADMIN);

      // give community moderators read access
      $moderatorGroup = $communityDao->getModeratorGroup();
      $outputFolderModeratorsReadPolicy = $folderpolicyggroupModel->createPolicy($moderatorGroup, $outputFolderDao, MIDAS_POLICY_READ);
  
      $resultsrunDao->setOutputFolderId($outputFolderDao->getFolderId());
      $this->save($resultsrunDao);
      }
    else
      {
      $resultsrunDao->setOutputFolderId('');
      $this->save($resultsrunDao);
      }

    return $resultsrunDao;
    }


  abstract function getAllUsersResultsRuns($userId);  
    
  abstract function loadLatestResultsRun($userId, $challengeId, $resultsType);

  abstract function getUsersLatestTestingResults($challengeId, $scoreboardName);

  abstract function getAllScoreboards($challengeId);





}  // end class Challenge_ResultsRunModelBase