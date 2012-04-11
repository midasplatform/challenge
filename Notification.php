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

require_once BASE_PATH . '/modules/api/library/APIEnabledNotification.php';

class Challenge_Notification extends ApiEnabled_Notification
  {
  public $moduleName = 'challenge';
  public $_moduleComponents=array('Api');
  public $_models=array();

  /** init notification process*/
  public function init()
    {
    $this->enableWebAPI($this->moduleName);
    $this->addCallBack('CALLBACK_CORE_GET_LEFT_LINKS', 'getLeftLink');
    $this->addCallBack('CALLBACK_CORE_GET_USER_ACTIONS', 'getUserAction');
    $this->addCallBack('CALLBACK_CORE_GET_USER_TABS', 'getUserTab');
    $this->addCallBack('CALLBACK_CORE_GET_COMMUNITY_VIEW_TABS', 'getCommunityViewTab');
    }//end init
    
  /**
   *@method getLeftLink
   * will generate a link for this module to be displayed in the main view.
   *@return ['challenge' => [ link to challenge module, module icon image path]]
  */
  public function getLeftLink()
    {
    
    if(!isset($this->userSession->Dao))
      {
      return array();
      }
    else 
      {
      $args['useSession'] = true;
      $challenges = $this->ModuleComponent->Api->competitorListChallenges($args);
      if(!empty($challenges))
        {
        $fc = Zend_Controller_Front::getInstance();
        $baseURL = $fc->getBaseUrl();
        $moduleWebroot = $baseURL . '/' . MIDAS_CHALLENGE_MODULE;
        return array(ucfirst("competitors") => array($moduleWebroot . '/competitor/init',  $baseURL . '/modules/challenge/public/images/competitors.png'));
        }
      else
        {
        return array();
        }
      }
    }
    
  /** Add a tab to the user's main page for competitors to submit results for a challenge  */
  public function getUserAction($args)
    {
    $apiargs['useSession'] = true;
    $challenges = $this->ModuleComponent->Api->competitorListChallenges($apiargs);
    if(!empty($challenges))
      {
      $fc = Zend_Controller_Front::getInstance();
      $moduleWebroot = $fc->getBaseUrl().'/'.$this->moduleName;
      $moduleFileroot =  $fc->getBaseUrl().'/modules/'.$this->moduleName;
      return array($this->t('Submit challenge results') => 
                   array("url" => $moduleWebroot.'/competitor/init', "image" => $moduleFileroot.'/public/images/competitors.png') );
      }
    else
      {
      return array();
      }
    }      
    
  /** Add a tab to the user's main page for competitors to submit results for a challenge  */
  public function getUserTab($args)
    {
    $apiargs['useSession'] = true;
    $challenges = $this->ModuleComponent->Api->competitorListChallenges($apiargs);
    if(!empty($challenges))
      {
      $fc = Zend_Controller_Front::getInstance();
      $moduleWebroot = $fc->getBaseUrl().'/'.$this->moduleName;
      return array($this->t('My challenge scores') => $moduleWebroot.'/competitor/showscore');
      }
    else
      {
      return array();
      }
    }  
    
  /**
   * Add a tab to the community's view page for show score dashboard to the community
   *
   * @return array
   */
  public function getCommunityViewTab($args)
    {
    if(!isset($this->userSession->Dao))
      {
      return array();
      }
    else
      {
      $apiargs['useSession'] = true;
      $apiargs['communityId'] = $args['community']->getKey();
      $challenges = $this->ModuleComponent->Api->checkCommunity($apiargs);
      if(!empty($challenges))
        {
        $fc = Zend_Controller_Front::getInstance();
        $moduleWebroot = $fc->getBaseUrl().'/challenge';
        return array('Score dashboard' => $moduleWebroot.'/competitor/scoredashboard');
        }
      else
        {
        return array();
        }
      }
    }
    
  } //end class
  
?>