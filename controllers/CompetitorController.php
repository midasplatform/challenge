<?php
/*=========================================================================
 MIDAS Server
 Copyright (c) Kitware SAS. 26 rue Louis Guérin. 69100 Villeurbanne, FRANCE
 All rights reserved.
 More information http://www.kitware.com

 Licensed under the Apache License, Version 2.0 (the "License");
 you may not use this file except in compliance with the License.
 You may obtain a copy of the License at

         http://www.apache.org/licenses/LICENSE-2.0.txt

 Unless required by applicable law or agreed to in writing, software
 distributed under the License is distributed on an "AS IS" BASIS,
 WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 See the License for the specific language governing permissions and
 limitations under the License.
=========================================================================*/
/** competitor controller*/
class Challenge_CompetitorController extends Challenge_AppController
{
  public $_moduleComponents = array('Api');
  public $_moduleModels = array('Challenge', 'Competitor', 'ResultsRun', 'ResultsRunItem');
  public $_models = array('Folder', 'User');
  public $_moduleForms = array('Config');

  /** init a job*/
  function initAction()
    {
    $this->view->header = "Submission Scoring";
    if(!$this->logged)
      {
      $this->haveToBeLogged();
      return false;
      }

    $args['useSession'] = true;
    $args['trainingStatus'] = MIDAS_CHALLENGE_STATUS_OPEN;
    $trainingChallenges = $this->ModuleComponent->Api->competitorListChallenges($args);
    unset($args['trainingStatus']);
    $args['testingStatus'] = MIDAS_CHALLENGE_STATUS_OPEN;
    $testingChallenges = $this->ModuleComponent->Api->competitorListChallenges($args);
    $challenges = $trainingChallenges + $testingChallenges;
    // will need to keep the status of training/testing to pass to ui
    $userDao = $this->userSession->Dao;    
    
    
    
    $this->view->user = $userDao;
    $this->view->challenges = $challenges;
    $selectOptions = false;
    
    $challengeResultsFolders = array();
    foreach($challenges as $challengeId => $challengeDetails)
      {
      $competitor = $this->Challenge_Competitor->findChallengeCompetitor($userDao->getUserId(), $challengeId);  
      if($competitor !== false)
        {
        $trainingFolder = $competitor->getTrainingSubmissionFolder();
        $testingFolder = $competitor->getTestingSubmissionFolder();
        
        $challengeResultsFolders[$challengeId] =
          array('training_submission_folder_id' => $trainingFolder ? $trainingFolder->getFolderId() : "",
                'training_submission_folder_name' => $trainingFolder ? $trainingFolder->getName() : "",
                'testing_submission_folder_id' => $testingFolder ? $testingFolder->getFolderId() : "",
                'testing_submission_folder_name' => $testingFolder ? $testingFolder->getName() : "");
        }
      $selectOptions[$challengeId] = $challengeDetails['name'];
      }
    if($selectOptions)
      {
      $configForm = $this->ModuleForm->Config->createSelectChallengeForm($selectOptions);
      $formArray = $this->getFormAsArray($configForm);
      $this->view->configForm = $formArray;
      $this->view->json['challengeResultsFolders'] = $challengeResultsFolders;
      $this->view->challengeResultsFolders = $challengeResultsFolders;
      if($this->_request->isPost())
        {
        $submitSelect = $this->_getParam('submitSelect');
        if(isset($submitSelect))
          {
          $this->view->targetChallengeId = $this->_getParam('challengeList');
          $this->view->targetChallengeName = $selectOptions[$this->_getParam('challengeList')];
          $this->view->targetChallengeDesc = $challenges[$this->_getParam('challengeList')]['description'];
          }
        }
      }
    }

  /** Ajax element used to select a results folder*/
  public function selectresultsfolderAction()
    {
    $this->requireAjaxRequest();
    $this->disableLayout();
    $policy = MIDAS_POLICY_WRITE;

    $this->view->selectEnabled = true;
    $this->view->policy = $policy;
    $this->view->user = $this->userSession->Dao;
    }

  /** Ajax element used to select an output folder*/
  public function selectoutputfolderAction()
    {
    $this->requireAjaxRequest();
    $this->disableLayout();
    $policy = MIDAS_POLICY_WRITE;

    $this->view->selectEnabled = true;
    $this->view->policy = $policy;
    $this->view->user = $this->userSession->Dao;

    }

  /** Ajax element used to validate the results folder*/
  public function validateresultsfolderAction()
    {
    $this->requireAjaxRequest();
    $this->disableLayout();

    $args = array();
    $args['useSession'] = true;
    $args['challengeId'] = $this->_getParam("challengeId");
    $args['folderId'] = $this->_getParam("folderId");

    $jsonContent = $this->ModuleComponent->Api->competitorValidateResultsFolder($args);
    $jsonContent['isValid'] = true;

    if(!empty($jsonContent['resultsWithoutTesting']) || !empty($jsonContent['testingWithoutResults']) )
      {
      $jsonContent['isValid'] = false;
      }
    echo JsonComponent::encode($jsonContent);
    }

  /** show score */
  public function showscoreAction()
    {
    if(!$this->logged)
      {
      $this->haveToBeLogged();
      return false;
      }
  
    $userDao = $this->userSession->Dao;
    $resultsRunId = $this->_getParam("resultsRunId");
    $resultsRun = $this->Challenge_ResultsRun->load($resultsRunId);
    if(!$resultsRun)
      {
      throw new Zend_Exception("Invalid resultsRunId.");
      }
    
    // check privileges
    if($userDao->getUserId() !== $resultsRun->getCompetitor()->getUserId() &&
       !$this->Challenge_Challenge->isChallengeModerator($userDao, $resultsRun->getChallenge()))
      {
      throw new Zend_Exception("You are not authorized to see these results.");
      }

      
    $breadcrumbComponent = MidasLoader::loadComponent('Breadcrumb');  
    $referer = $this->_getParam("referer");
    if(!$referer || $referer != 'allscores')
      {
      $breadcrumbs[] = array('type' => 'user', 'object' => $userDao);
      // TODO: this is assuming my challenge scores will be ui-tabs-1, but this is
      // dynamically set by javascript upon adding tabs, i.e. this isn't reliable
      $myscoresLink = $this->view->webroot . '/user/' . $userDao->getUserId() . '#ui-tabs-1';
      $breadcrumbs[] = array('type' => 'custom', 'text' => 'My challenge scores', 'icon' => '', 'href' => $myscoresLink);
      $breadcrumbComponent->setBreadcrumbHeader($breadcrumbs, $this->view);
      }
    else
      {
      $breadcrumbs[] = array('type' => 'community', 'object' => $resultsRun->getChallenge()->getCommunity());
      $allscoresLink = $this->view->webroot . '/community/' . $resultsRun->getChallenge()->getCommunityId() . '#Challenge_Scores';
      $breadcrumbs[] = array('type' => 'custom', 'text' => 'Challenge scores', 'icon' => '', 'href' => $allscoresLink);
      $breadcrumbComponent->setBreadcrumbHeader($breadcrumbs, $this->view);
      }
      
     
      
    // show scores for an individual challenge
    $dashboardDao = $resultsRun->getChallenge()->getDashboard();
    
    $resultsType = $resultsRun->getResultsType();
    if($resultsType === MIDAS_CHALLENGE_TRAINING)
      {
      $datasetFolderId = $dashboardDao->getTraining()->getFolderId();  
      }
    else
      {
      $datasetFolderId = $dashboardDao->getTesting()->getFolderId();  
      }        
    $datasetFolderLink = $this->view->webroot . '/folder/' . $datasetFolderId;
    $this->view->datasetFolderLink = $datasetFolderLink; 
            
    $this->view->challengeLink = $this->view->webroot . '/community/' . $resultsRun->getChallenge()->getCommunityId() . '#tabs-info';
    
    $this->view->json['resultsRunId'] = $resultsRunId;
   
    $apiargs = array();
    $apiargs['useSession'] = true;
    $apiargs['resultsRunId'] = $resultsRunId;
    $tableData = $this->ModuleComponent->Api->competitorListResults($apiargs);
    $this->view->json['processingComplete'] = $tableData['processing_complete'];
    $this->view->json['rrisInError'] = $tableData['rris_in_error'];
    $this->view->rrisInError = $tableData['rris_in_error'];
    $this->view->json['rrisComplete'] = $tableData['rris_complete'];
    $this->view->rrisComplete = $tableData['rris_complete'];

    $this->view->challengeName = $dashboardDao->getName();
    $this->view->resultsRun = $resultsRun;
    
    $this->view->tableData = $tableData;
    $this->view->user = $this->userSession->Dao;
    $this->view->submitterEmail = $resultsRun->getCompetitor()->getUser()->getEmail();
    list($scoredColumns, $metricIds) = $this->Challenge_Challenge->getScoredColumns($resultsRun->getChallenge());
    // set the table headers to be Subject plus the scored columns
    array_unshift($scoredColumns, 'Subject');
    $this->view->tableHeaders = $scoredColumns;
    $this->view->metricIds = $metricIds;
    $this->view->json['tableHeaders'] = $scoredColumns;
    $this->view->json['unknownStatus'] = MIDAS_CHALLENGE_RRI_STATUS_UNKNOWN;
    $this->view->anonymizedId = $this->getAnonymizedId($userDao, $dashboardDao->getName());
    }

  /** Challenge dashboard */
  public function dashboardAction()
    {
    $this->disableLayout();
    $args['communityId'] = $this->_getParam("communityId");
    $challenges = $this->ModuleComponent->Api->anonymousGetChallenge($args);
    $tableHeaders = array();
    $tableData = array();
    foreach($challenges as $challengeId => $statuses)
      {
      $dashboardDao = $this->Challenge_Challenge->load($challengeId)->getDashboard();
      $challengeName = $dashboardDao->getName();
      $challengeDesc = $dashboardDao->getDescription();
      $challengeInfo[$challengeId] = array('name' => $challengeName, 'description' => $challengeDesc);

      $apiargs = array();
      $apiargs['useSession'] = true;
      $apiargs['challengeId'] = $challengeId;
      $apiResults = array();
      $apiResults = $this->ModuleComponent->Api->anonymousListDashboard($apiargs);

      // key of apiResults is user id
      // change this to be an anonymized id
      $anonymizedResults = array();
      foreach($apiResults['competitor_scores'] as $userId => $results)
        {
        $competitorDao = $this->User->load($userId);
        $anonymizedId = $this->getAnonymizedId($competitorDao, $challengeName);
        $anonymizedResults[$anonymizedId] = $results;
        }
      
      $tableHeaders[$challengeId] = array(
        'Competitor', 
        'Ave Dist 1',
        'Ave Dist 2',
        'Dice 1',
        'Dice 2',
        'Hausdorff Dist 1',
        'Hausdorff Dist 2',
        'Kappa',
        'Sensitivity 1',
        'Sensitivity 2',
        'Specificity 1',
        'Specificity 2',
        'Average Rank');
      $resultColumns = array(
        'AveDist(A_1, B_1)',
        'AveDist(A_2, B_2)',
        'Dice(A_1, B_1)',
        'Dice(A_2, B_2)',
        'HausdorffDist(A_1, B_1)',
        'HausdorffDist(A_2, B_2)',
        'Kappa(A,B)',
        'Sensitivity(A_1, B_1)',
        'Sensitivity(A_2, B_2)',
        'Specificity(A_1, B_1)',
        'Specificity(A_2, B_2)',
        'Average Rank');
        
      break;
      }
    $this->view->challengeInfo = $challengeInfo;
    $this->view->tableData = array();
    $this->view->tableData[$challengeId] = $anonymizedResults;
    $this->view->resultColumns = $resultColumns;
    $this->view->tableHeaders = $tableHeaders;
    $this->view->resultColumns = $resultColumns;
    }

    
  /** listing of all user's scoring runs */
  public function scorelistingAction()
    {
    if(!$this->logged)
      {
      $this->haveToBeLogged();
      return false;
      }
    $this->disableLayout();

    $userDao = $this->userSession->Dao;
    $resultsRuns = $this->Challenge_ResultsRun->getAllUsersResultsRuns($userDao->getUserId());
    $resultsRuns = $resultsRuns[$userDao->getUserId()];
    $tableHeaders = array("Challenge", "Dataset", "Run Date", );
    $tableColumns = array("challenge_name", "dataset", "run_date");
    $scorelistingRows = array();
    
    
    $fc = Zend_Controller_Front::getInstance();
    $webRoot = $fc->getBaseUrl();
    foreach($resultsRuns as $resultRun)
      {
      $scorelistingRow = array();
      
      $challenge = $resultRun->getChallenge();
      $challengeName = $resultRun->getChallenge()->getDashboard()->getName();
      $dashboard = $resultRun->getChallenge()->getDashboard();
      
      $scorelistingRow["challenge_name"] =
        array('text' => $challengeName,
              'link' => $webRoot  . '/community/' . $challenge->getCommunityId() . '#tabs-info');

      $resultsType = $resultRun->getResultsType();
      if($resultsType === MIDAS_CHALLENGE_TRAINING)
        {
        $datasetFolderId = $dashboard->getTraining()->getFolderId();  
        }
      else
        {
        $datasetFolderId = $dashboard->getTesting()->getFolderId();  
        }
      $scorelistingRow["dataset"] =
        array('text' => $resultsType,
              'link' => $webRoot  . '/folder/' . $datasetFolderId);
      
      $scorelistingRow["run_date"] =
        array('text' => $resultRun->getDate(),
              'link' => $webRoot  . "/challenge/competitor/showscore?resultsRunId=" . $resultRun->getChallengeResultsRunId());
      
      $scorelistingRows[] = $scorelistingRow;
      }
    
    $this->view->tableHeaders = $tableHeaders;
    $this->view->tableColumns = $tableColumns;
    $this->view->scorelistingRows = $scorelistingRows;
    
  }

  /** listing of all users' scoring runs, need to be a challenge moderator */
  public function allscoresAction()
    {
    if(!$this->logged)
      {
      $this->haveToBeLogged();
      return false;
      }
    $this->disableLayout();

    $userDao = $this->userSession->Dao;
    $challengeId = $this->_getParam('challengeId');
    if(!isset($challengeId))
      {
      throw new Zend_Exception('Must set challengeId parameter');
      }
    $challenge = $this->Challenge_Challenge->load($challengeId);
    if(!$challenge)
      {
      throw new Zend_Exception('Challenge with that id does not exist');
      }
    if(!$this->Challenge_Challenge->isChallengeModerator($userDao, $challenge))
      {
      throw new Zend_Exception("You are not authorized to see these results.");  
      }
    
    $resultsRuns = $this->Challenge_ResultsRun->getAllUsersResultsRuns();

    $tableHeaders = array("User", "Dataset", "Run Date", );
    $tableColumns = array("user", "dataset", "run_date");
    $scorelistingRows = array();   
    
    $fc = Zend_Controller_Front::getInstance();
    $webRoot = $fc->getBaseUrl();
    $competitors = array();
    foreach($resultsRuns as $competitorUserId => $competitorResultRuns)
      {
      foreach($competitorResultRuns as $resultRun)
        {
        $scorelistingRow = array();
        $dashboard = $resultRun->getChallenge()->getDashboard();
      
        if(array_key_exists($competitorUserId, $competitors))
          {
          $competitorUser = $competitors[$competitorUserId];
          }
        else
          {
          $competitorUser = $this->User->load($competitorUserId);
          $competitors[$competitorUserId] = $competitorUser; 
          }
        
        if(!$competitorUser)
          {
          $scorelistingRow["user"] =
            array('text' => 'missing user',
                  'link' => '');
              }
        else
          {
          $scorelistingRow["user"] =
            array('text' => $competitorUser->getEmail(),
                  'link' => $webRoot  . '/user/' . $competitorUserId);
          }
      
        $resultsType = $resultRun->getResultsType();
        if($resultsType === MIDAS_CHALLENGE_TRAINING)
          {
          $datasetFolderId = $dashboard->getTraining()->getFolderId();  
          }
        else
          {
          $datasetFolderId = $dashboard->getTesting()->getFolderId();  
          }
        $scorelistingRow["dataset"] =
          array('text' => $resultsType,
                'link' => $webRoot  . '/folder/' . $datasetFolderId);
      
        $scorelistingRow["run_date"] =
          array('text' => $resultRun->getDate(),
                'link' => $webRoot  . "/challenge/competitor/showscore?referer=allscores&resultsRunId=" . $resultRun->getChallengeResultsRunId());
        
        $scorelistingRows[] = $scorelistingRow;
        }
      }
    
    $this->view->tableHeaders = $tableHeaders;
    $this->view->tableColumns = $tableColumns;
    $this->view->scorelistingRows = $scorelistingRows;
    
  }  
  
  
  function getAnonymizedId($userDao, $challengeName)
    {
    // use the global config password prefix as a hash salt  
    $passwordPrefix = Zend_Registry::get('configGlobal')->password->prefix;
    $email = $userDao->getEmail();
    // this isn't cryptographically secure b/c of the reuse of salts, but should
    // be fine for this relatively low security application
    $anonymizedId = substr(md5($passwordPrefix . $email . $challengeName), 0, 8);
    return $anonymizedId;  
    }
  
  
  /** list contact info and possibly an FAQ */
  public function troubleshootingAction()
    {
    $this->view->header = "Challenge Troubleshooting";
    }
    
  public function jobdetailsAction()
    {
    if(!$this->logged)
      {
      $this->haveToBeLogged();
      return false;
      }
    $this->disableLayout();

    $userDao = $this->userSession->Dao;
    $resultsRunItemId = $this->_getParam('rriid');
    if(!isset($resultsRunItemId))
      {
      throw new Zend_Exception('Must set rriid parameter');
      }
    $resultsRunItem = $this->Challenge_ResultsRunItem->load($resultsRunItemId);
    if(!$resultsRunItem)
      {
      throw new Zend_Exception('ResultsRunItem with that id does not exist', 404);
      }
    $condorJobModel = MidasLoader::loadModel('CondorJob', 'batchmake');
    $condorJob = $condorJobModel->load($resultsRunItem->getCondorDagJobId());
    $batchmakeTask = $resultsRunItem->getChallengeResultsRun()->getBatchmakeTask();
    
    // check privileges
    if($userDao->getUserId() !== $batchmakeTask->getUserId() &&
       !$this->Challenge_Challenge->isChallengeModerator($userDao, $resultsRunItem->getChallengeResultsRun()->getChallenge()))
      {
      throw new Zend_Exception("You are not authorized to see these results.");
      }

    $tmpDir = $batchmakeTask->getWorkDir();
    
    $error = $this->_getParam('error');
    if(!empty($error))
      {
      $this->view->errorJob = true; 
      }
             
    $outputFile = $tmpDir . $condorJob->getOutputFilename();
    $output = file_exists($outputFile) ? file_get_contents($outputFile) : "std out " .MIDAS_CHALLENGE_FILE_NOT_FOUND;
    if(empty($output))
      {
      $output = "No Standard Output data produced.";
      }
      
    $errorFile = $tmpDir . $condorJob->getErrorFilename();
    $error = file_exists($errorFile) ? file_get_contents($errorFile) : "std err " .MIDAS_CHALLENGE_FILE_NOT_FOUND;
    if(empty($error))
      {
      $error = "No Standard Error data produced.";
      }

    $logFile = $tmpDir . $condorJob->getLogFilename();
    $log = file_exists($logFile) ? file_get_contents($logFile) : "log " .MIDAS_CHALLENGE_FILE_NOT_FOUND;

    $processOutputFile = $tmpDir . $resultsRunItem->getProcessOut();
    $processOutput = file_exists($processOutputFile) ? file_get_contents($processOutputFile) : "output " .MIDAS_CHALLENGE_FILE_NOT_FOUND;
    
    
    $this->view->errorText = $error;
    $this->view->outputText = $output;
    $this->view->logText = $log;
    $this->view->processOutputText = $processOutput;
    }  
    
    
  public function metricdetailsAction()
    {
    // no need to check session, this is public info
    
    $metricId = $this->_getParam('metricId');
    if(!isset($metricId))
      {
      throw new Zend_Exception('Must set metricId parameter');
      }  
      
    $metricModel = MidasLoader::loadModel('Metric', 'challenge');
    $metric = $metricModel->load($metricId);
    if(!$metric)
      {
      throw new Zend_Exception('Invalid metricId parameter');
      }
    $this->disableLayout();  
    $this->view->metric = $metric;
    }
    
}//end class
