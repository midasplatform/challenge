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

  /*
  Used by debugging only
  function _createTestingdata()
    {
    $args = array();
    $args['useSession'] = true;
    $args['communityId'] = 38;
    $args['challengeName'] = 'Demo Challenge';
    $args['challengeDescription'] = 'challenge for Demo';
    $this->ModuleComponent->Api->adminCreateChallenge($args);
    }
   */

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

  /** start a get score job */
  public function getscoreAction()
    {
    $this->disableLayout();
    //$this->disableView();
    /* TODO
    $args = array();
    $args['useSession'] = true;
    $args['challengeId'] = $this->_getParam("challengeId");
    $args['outputFolderId'] = $this->_getParam("outputFolderId");
    $args['resultsFolderId'] = $this->_getParam("resultsFolderId");

    $this->view->args = $args();
    $this->view->score = $this->ModuleComponent->Api->competitorScoreResultsFolder($args);
    */
    }
    
  public function showscoreAction()
    {
    //$this->disableLayout();
    //$this->disableView();
    /* TODO
    $args = array();
    $args['useSession'] = true;
    $args['challengeId'] = $this->_getParam("challengeId");
    $args['outputFolderId'] = $this->_getParam("outputFolderId");
    $args['resultsFolderId'] = $this->_getParam("resultsFolderId");

    $this->view->args = $args();
    $this->view->score = $this->ModuleComponent->Api->competitorScoreResultsFolder($args);
    */
    }
    
   public function showscoreAction()
    {
    //  TODO: use api to get scores for individual competitor
    } 
   
    
   public function scoredashboardAction()
    {
    //  TODO: use api to get scores for a community
    } 

}//end class
