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

  /** Create a challenge (ajax)*/
  function createAction()
    {
    if(!$this->logged)
      {
      throw new Zend_Exception("You have to be logged in to do this operation");
      }
    $community_id = $this->_getParam('communityId');
    $this->view->communityId = $community_id;
    $action = 'create';
    $form = $this->ModuleForm->Config->createEditChallengeForm($community_id, $action);

    if($this->_request->isPost() && $form->isValid($this->getRequest()->getPost()))
      {
      $args = array();
      $args['communityId'] = $community_id;
      $args['useSession'] = true;
      $args['challengeName'] = $form->getValue('name');
      $args['challengeDescription'] =  $form->getValue('description');
      $args['challengeStatus'] =  $form->getValue('status');
      $challengeId = $this->ModuleComponent->Api->adminCreateChallenge($args);
      $this->_redirect("/community/".$community_id);
      }
    else
      {
      $this->requireAjaxRequest();
      $this->_helper->layout->disableLayout();
      $this->view->form = $this->getFormAsArray($form);
      }
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
    $formInfo = $this->ModuleForm->Config->createEditChallengeForm($community_id, $action);

    //ajax posts
    if($this->_request->isPost() && $formInfo->isValid($this->getRequest()->getPost()))
      {
      $this->_helper->viewRenderer->setNoRender();

      $dashboardDao->setName($formInfo->getValue('name'));
      $dashboardDao->setDescription($formInfo->getValue('description'));
      $forminfo_status = $formInfo->getValue('status');
      $modelLoader = new MIDAS_ModelLoader();
      $dashboardModel = $modelLoader->loadModel('Dashboard', 'validation');
      $dashboardModel->save($dashboardDao);
      $challengeDao->setStatus($forminfo_status);
      $this->Challenge_Challenge->save($challengeDao);
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
    $status = $formInfo->getElement('status');
    $status->setValue($challengeDao->getStatus());
    $this->view->infoForm = $this->getFormAsArray($formInfo);

    }//

}//end class