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
  public $_components = array('Date');
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
    foreach($challenges as $challengdId => $challengeDetails)
      {
      $selectOptions[$challengdId] = $challengeDetails['name'];
      }

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

  /** Ajax element used to select a results folder*/
  public function selectresultsfolderAction()
    {
    $this->requireAjaxRequest();
    $this->disableLayout();
    if(isset($policy) && $policy == 'read')
      {
      $policy = MIDAS_POLICY_READ;
      }
    else
      {
      $policy = MIDAS_POLICY_WRITE;
      }

    $this->view->selectEnabled = true;
    $this->view->Date = $this->Component->Date;
    $this->view->policy = $policy;
    $this->view->user = $this->userSession->Dao;
    }

  /** Ajax element used to select an output folder*/
  public function selectoutputfolderAction()
    {
    $this->requireAjaxRequest();
    $this->disableLayout();
    //$policy = $this->_getParam("policy");
    if(isset($policy) && $policy == 'read')
      {
      $policy = MIDAS_POLICY_READ;
      }
    else
      {
      $policy = MIDAS_POLICY_WRITE;
      }

    $this->view->selectEnabled = true;
    $this->view->Date = $this->Component->Date;
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
    $challenges = array();
    $showAllChallenges = true;
    if(!isset($challengeId))
      {
      $this->disableLayout();
      $args['useSession'] = true;
      $challenges = $this->ModuleComponent->Api->competitorListChallenges($args);
      }
    else
      {
      $showAllChallenges = false;
      $dashboardDao = $this->Challenge_Challenge->load($challengeId)->getDashboard();
      $challenges = array($challengeId => array('name' => $dashboardDao->getName(), 'description' => $dashboardDao->getDescription() ) );
      }
    $tableData = array();
    foreach($challenges as $challengeId => $challengeDetails)
      {
      $apiargs = array();
      $apiargs['useSession'] = true;
      $apiargs['challengeId'] = $challengeId;
      $tableData[$challengeDetails['name']] = $this->ModuleComponent->Api->competitorListResults($apiargs);
      }

    $this->view->showAllChallenges = $showAllChallenges;
    $this->view->tableData = $tableData;
    $this->view->user = $this->userSession->Dao;
    $this->view->tableHeaders = array('testing item', 'result item', 'metric', 'score', 'output item');
    $this->view->tableData_resultsColumns = array('test_item_name', 'result_item_name', 'metric_item_name', 'score', 'output_item_name');

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

      $testItemCount = count($apiResults['test_items']);
      if(count($apiResults['competitor_scores']) > 0) // has scores from at least one competitor
        {
        $tableHeaders[$challengeId] = array('competitor id', 'aggregated score');
        foreach($apiResults['test_items'] as $testItemId => $testItemName)
          {
          $tableHeaders[$challengeId][] = $testItemName;
          }

        foreach($apiResults['competitor_scores'] as $competitorId => $scores)
          {
          $tableData[$challengeId][$competitorId] = array_fill(0, $testItemCount + 1, 0.0);
          $aggregated_score = 0.0;
          foreach($scores as $testItemId => $testScore)
            {
            $tableData[$challengeId][$competitorId][array_search($testItemId, array_keys($apiResults['test_items'])) + 1 ] = floatval($testScore['score']);
            $aggregated_score += floatval($testScore['score']);
            }
          $tableData[$challengeId][$competitorId][0] = $aggregated_score;
          }
        }
      }
    $this->view->challengeInfo = $challengeInfo;
    $this->view->tableData = $tableData;
    $this->view->tableHeaders = $tableHeaders;
    }

}//end class
