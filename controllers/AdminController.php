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
class Challenge_AdminController extends Challenge_AppController
{
  public $_moduleComponents = array('Api');
  public $_moduleModels = array('Challenge');
  public $_moduleForms = array('Config');

  /** Create a challenge, set simple defaults*/
  function createAction()
    {
    if(!$this->logged)
      {
      throw new Zend_Exception("You have to be logged in to do this operation");
      }
    $community_id = $this->_getParam('communityId');
    $this->view->communityId = $community_id;
    $challengeMetricModel = MidasLoader::loadModel('Metric', 'challenge');
    $args = array();
    $args['communityId'] = $community_id;
    $args['useSession'] = true;
    $args['challengeName'] = "Challenge";
    $args['challengeDescription'] =  "Default Challenge Description";
    $args['trainingStatus'] =  MIDAS_CHALLENGE_STATUS_CLOSED;
    $args['testingStatus'] =  MIDAS_CHALLENGE_STATUS_CLOSED;
    $args['numberScoredLabels'] =  '0';
    $challengeId = $this->ModuleComponent->Api->adminCreateChallenge($args);
    $this->_redirect("/community/manage?communityId=".$community_id);
    }//end create


  /** Edit a challenge*/
  function editAction()
    {
    if(!$this->logged)
      {
      $this->haveToBeLogged();
      return false;
      }
    if($this->_helper->hasHelper('layout'))
      {
      $this->_helper->layout->disableLayout();
      }

    $community_id = $this->_getParam("communityId");
    $challengeIds = array_keys($this->Challenge_Challenge->getByCommunityId($community_id));
    if(empty($challengeIds))
      {
      throw new Zend_Exception("The community doesn't have a challenge with it!");
      }
    // only allow one challenge per community now
    $challengeId = $challengeIds[0];
    $challengeDao = $this->Challenge_Challenge->load($challengeId);
    $dashboardDao = $challengeDao->getDashboard();
    $action = 'edit';
    $challengeMetricModel = MidasLoader::loadModel('Metric', 'challenge');
    $allMetrics = $challengeMetricModel->fetchAll();
    $formInfo = $this->ModuleForm->Config->createEditChallengeForm($community_id, $action, $allMetrics);

    //ajax posts
    if($this->_request->isPost() && $formInfo->isValid($this->getRequest()->getPost()))
      {
      $this->_helper->viewRenderer->setNoRender();

      $dashboardDao->setName($formInfo->getValue('name'));
      $dashboardDao->setDescription($formInfo->getValue('description'));
      $forminfo_trainingStatus = $formInfo->getValue('training_status');
      $forminfo_testingStatus = $formInfo->getValue('testing_status');
      $dashboardModel = MidasLoader::loadModel('Dashboard', 'validation');
      $dashboardModel->save($dashboardDao);
      $challengeDao->setTrainingStatus($forminfo_trainingStatus);
      $challengeDao->setTestingStatus($forminfo_testingStatus);
      $challengeDao->setNumberScoredLabels($formInfo->getValue('number_scored_labels'));
      $this->Challenge_Challenge->save($challengeDao);
      
      $selectedMetricModel = MidasLoader::loadModel('SelectedMetric', 'challenge');
      $selectedMetrics = $selectedMetricModel->findBy('challenge_id', $challengeId);
      foreach($allMetrics as $metric)
        {
        $metricSelected = $formInfo->getValue($metric->getMetricExeName());
        $selectedMetricExists = false;
        foreach($selectedMetrics as $selectedMetric)
          {
          if($selectedMetric->getChallengeMetricId() == $metric->getChallengeMetricId())
            {
            $selectedMetricExists = true;
            if(!$metricSelected)
              {
              $selectedMetricModel->delete($selectedMetric);
              }
            break;
            }
          }
        if($metricSelected && !$selectedMetricExists)
          {
          // need to create the selected metric  
          $selectedMetric = MidasLoader::newDao('SelectedMetricDao', 'challenge');
          $selectedMetric->setChallengeMetricId($metric->getChallengeMetricId());
          $selectedMetric->setChallengeId($challengeDao->getChallengeId());
          $selectedMetricModel->save($selectedMetric);
          }
        }
      
      if($challengeDao !== false)
        {
        echo JsonComponent::encode(array(true, $this->t('Changes saved')));
        }
      else
        {
        echo JsonComponent::encode(array(false, $this->t('Error')));
        }
      return;
      }//end ajax posts

    //init forms
    $name = $formInfo->getElement('name');
    $name->setValue($dashboardDao->getName());
    $description = $formInfo->getElement('description');
    $description->setValue($dashboardDao->getDescription());
    $trainingStatus = $formInfo->getElement('training_status');
    $testingStatus = $formInfo->getElement('testing_status');
    $trainingStatus->setValue($challengeDao->getTrainingStatus());
    $testingStatus->setValue($challengeDao->getTestingStatus());
    $numberScoredLabels = $formInfo->getElement('number_scored_labels');
    $numberScoredLabels->setValue($challengeDao->getNumberScoredLabels());
    
    // get the currently selected metrics for this challenge
    $selectedMetricModel = MidasLoader::loadModel('SelectedMetric', 'challenge');
    $selectedMetrics = $selectedMetricModel->findBy('challenge_id', $challengeId);
    
    // list the metrics
    foreach($allMetrics as $metric)
      {
      $metricRadio = $formInfo->getElement($metric->getMetricExeName());
      $metricSelected = false;
      foreach($selectedMetrics as $selectedMetric)
        {
        if($selectedMetric->getChallengeMetricId() == $metric->getChallengeMetricId())
          {
          $metricSelected = true;
          break;
          }
        }
      $metricRadio->setValue($metricSelected == false ? '0' : '1');  
      }
    $this->view->allMetrics = $allMetrics;
    $this->view->infoForm = $this->getFormAsArray($formInfo);

    }//

}//end class
