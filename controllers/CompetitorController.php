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
  public $_moduleModels = array('Challenge');
  public $_moduleForms = array('Config');

  /** init a job*/
  function initAction()
    {
    $this->view->header = "Competitor Wizard";
    if(!$this->logged)
      {
      $this->haveToBeLogged();
      return false;
      }

    $args['useSession'] = true;
    $args['status'] = MIDAS_CHALLENGE_STATUS_OPEN;
    $this->view->user = $this->userSession->Dao;
    $challenges = $this->ModuleComponent->Api->competitorListChallenges($args);
    $this->view->challenges = $challenges;
    $selectOptions = false;
    foreach($challenges as $challengdId => $challengeDetails)
      {
      $selectOptions[$challengdId] = $challengeDetails['name'];
      }
    if($selectOptions)
      {
      $configForm = $this->ModuleForm->Config->createSelectChallengeForm($selectOptions);
      $formArray = $this->getFormAsArray($configForm);
      $this->view->configForm = $formArray;
      
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
    $challengeId = $this->_getParam("challengeId");
    $resultsType = $this->_getParam("resultsType");
    $challenges = array();
    $showAllChallenges = true;
    if(!isset($challengeId))
      {
      $this->disableLayout();
      $args['useSession'] = true;
      $challenges = $this->ModuleComponent->Api->competitorListChallenges($args);
      }
    else
      { // show scores for an individual challenge
      $showAllChallenges = false;
      $dashboardDao = $this->Challenge_Challenge->load($challengeId)->getDashboard();
      $challenges = array($challengeId => array('name' => $dashboardDao->getName(), 'description' => $dashboardDao->getDescription() ) );
      $this->view->json['challengeId'] = $challengeId;
      $this->view->json['resultsType'] = $resultsType;
      }
    $tableData = array();
    foreach($challenges as $challengeId => $challengeDetails)
      {
      $apiargs = array();
      $apiargs['useSession'] = true;
      $apiargs['challengeId'] = $challengeId;
      $apiargs['resultsType'] = $resultsType;
      $tableData[$challengeDetails['name']] = $this->ModuleComponent->Api->competitorListResults($apiargs);
      $this->view->json['processingComplete'] = $tableData[$challengeDetails['name']]['processing_complete'];
      }

    $this->view->showAllChallenges = $showAllChallenges;
    $this->view->tableData = $tableData;
    $this->view->user = $this->userSession->Dao;
    $this->view->tableData_resultsColumns = array(
        'Subject', 
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
        'Specificity(A_2, B_2)');
    $this->view->tableHeaders = array(
        'Subject', 
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
        'Specificity 2');

    }

  /** Challenge dashboard */
  public function dashboardAction()
    {
    $this->disableLayout();
    $args['communityId'] = $this->_getParam("communityId");
    $challenges = $this->ModuleComponent->Api->anonymousGetChallenge($args);
    $tableHeaders = array();
    $tableData = array();
    foreach($challenges as $challengeId => $status)
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

//      $testItemCount = count($apiResults['test_items']);
    //  if(count($apiResults['competitor_scores']) > 0) // has scores from at least one competitor
    //    {
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
        //foreach($apiResults['test_items'] as $testItemId => $testItemName)
        //  {
        //  $tableHeaders[$challengeId][] = $testItemName;
        //  }
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
        
      //  foreach($apiResults['competitor_scores'] as $competitorId => $scores)
        //  {
          //$tableData[$challengeId][$competitorId] = array_fill(0, $testItemCount + 1, 0.0);
          //$aggregated_score = 0.0;
          //foreach($scores as $testItemId => $testScore)
          //  {
          //  $tableData[$challengeId][$competitorId][array_search($testItemId, array_keys($apiResults['test_items'])) + 1 ] = floatval($testScore['score']);
          //  $aggregated_score += floatval($testScore['score']);
          //  }
          //$tableData[$challengeId][$competitorId][0] = $aggregated_score / $testItemCount;
      //    $tableData[$challengeId][$competitorId] = array();
          
        //  }
        //}
        
      }
    $this->view->challengeInfo = $challengeInfo;
    $this->view->tableData = array();
    $this->view->tableData[$challengeId] = $apiResults['competitor_scores'];//$tableData;
    $this->view->resultColumns = $resultColumns;
    //$this->view->challengeResults = $apiResults['competitor_scores'];
  $this->view->tableHeaders = $tableHeaders;
  $this->view->resultColumns = $resultColumns;
    }

}//end class
