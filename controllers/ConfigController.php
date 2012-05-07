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
/** challenge module configuration*/
class Challenge_ConfigController extends Challenge_AppController
{
  public $_models = array('Community');
  public $_moduleModels = array('EnabledCommunity');

  public $_daos = array('Community');
  public $_moduleDaos = array('EnabledCommunity');

  /** index action*/
  function indexAction()
    {
    $this->requireAdminPrivileges();

    $communities = $this->Community->getAll();

    $this->view->communities = $communities;
    $enabledCommunityDaos = $this->Challenge_EnabledCommunity->getAll();
    $enabledCommunityIds = array();
    foreach($enabledCommunityDaos as $enabledCommunityDao)
      {
      $enabledCommunityIds[] = $enabledCommunityDao->getCommunityId();
      }
    $this->view->enabledCommunityIds = $enabledCommunityIds;
    $this->view->json['challenge']['message']['enableCommunity'] = "Enable as challenge community";
    $this->view->json['challenge']['message']['disableCommunity'] = "Disable as challenge community";
    }

  /** enable action*/
  function enableAction()
    {
    if(!$this->logged)
      {
      throw new Zend_Exception('Please Log in');
      }
    else if(!$this->userSession->Dao->isAdmin())
      {
      throw new Zend_Exception('You must log in as an administrator');
      }

    $communityIds = explode('-', $this->_getParam('communityIds'));
    foreach($communityIds as $communityId)
      {
      //$challengeEnabledCommnityDao = false;
      $challengeEnabledCommnityDao = $this->Challenge_EnabledCommunity->getByCommunityId($communityId);
      if(!isset($challengeEnabledCommunityDao))
        {
        $challengeEnabledCommnityDao = new Challenge_EnabledCommunityDao();
        }
      $challengeEnabledCommnityDao->setCommunityId($communityId);
      $this->Challenge_EnabledCommunity->save($challengeEnabledCommnityDao);
      }
    $this->_redirect("/challenge/config");
    }

  /** disable action*/
  function disableAction()
    {
    if(!$this->logged)
      {
      throw new Zend_Exception('Please Log in');
      }
    else if(!$this->userSession->Dao->isAdmin())
      {
      throw new Zend_Exception('You must log in as an administrator');
      }

    $communityIds = explode('-', $this->_getParam('communityIds'));
    foreach($communityIds as $communityId)
      {
      $challengeEnabledCommnityDao = false;
      $challengeEnabledCommnityDao = $this->Challenge_EnabledCommunity->getByCommunityId($communityId);
      if($challengeEnabledCommnityDao != false)
        {
        $this->Challenge_EnabledCommunity->delete($challengeEnabledCommnityDao);
        }
      }
    $this->_redirect("/challenge/config");
    }

}//end class
