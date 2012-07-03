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
    $this->addCallBack('CALLBACK_CORE_GET_FOOTER_HEADER', 'getHeader');
    $this->addCallBack('CALLBACK_CORE_GET_LEFT_LINKS', 'getLeftLink');
    //$this->addCallBack('CALLBACK_CORE_GET_USER_ACTIONS', 'getUserAction');
    $this->addCallBack('CALLBACK_CORE_GET_USER_TABS', 'getUserTab');
    $this->addCallBack('CALLBACK_CORE_LAYOUT_TOPBUTTONS', 'getButton');
    //$this->addCallBack('CALLBACK_CORE_GET_COMMUNITY_VIEW_TABS', 'getCommunityViewTab');
    $this->addCallBack('CALLBACK_CORE_GET_COMMUNITY_MANAGE_TABS', 'getCommunityManageTab');
    $this->addCallBack('CALLBACK_CORE_GET_COMMUNITY_VIEW_ADMIN_ACTIONS', 'getCommunityViewAction');
    $this->addCallBack('CALLBACK_CORE_GET_COMMUNITY_VIEW_JSS', 'getCommunityViewJSs');
    $this->addCallBack('CALLBACK_CORE_USER_JOINED_COMMUNITY', 'userJoinedCommunity');
    }//end init

  /** get layout header */
  public function getHeader()
    {
    return '<link type="text/css" rel="stylesheet" href="'.Zend_Registry::get('webroot').'/modules/challenge/public/css/layout/challenge.css" />';
    }

  protected function openChallengesForUser()
    {
    $args['useSession'] = true;
    $args['trainingStatus'] = MIDAS_CHALLENGE_STATUS_OPEN;
    $trainingChallenges = $this->ModuleComponent->Api->competitorListChallenges($args);
    unset($args['trainingStatus']);
    $args['testingStatus'] = MIDAS_CHALLENGE_STATUS_OPEN;
    $testingChallenges = $this->ModuleComponent->Api->competitorListChallenges($args);
    $challenges = array_merge($trainingChallenges, $testingChallenges);
    return (!empty($challenges));
    }
    
    
  /** add a process button  */ 
  public function getButton($params)
    {
    if(!isset($this->userSession->Dao) || !$this->openChallengesForUser())
      {
      return array();
      }
    else
      {
      $fc = Zend_Controller_Front::getInstance();
      $baseURL = $fc->getBaseUrl();
      $moduleWebroot = $baseURL . '/' . MIDAS_CHALLENGE_MODULE;  
      $html =  "<li class='processButton' style='margin-left:5px;' title='Score Submissions' rel='".$moduleWebroot."/competitor/init'>
              <a href='".$moduleWebroot."/competitor/init'><img id='processButtonImg' src='".Zend_Registry::get('webroot')."/modules/challenge//public/images/process-ok.png' alt='Score Submissions'/>
              <img id='processButtonLoadiing' style='margin-top:5px;display:none;' src='".Zend_Registry::get('webroot')."/core/public/images/icons/loading.gif' alt=''/>
              Score Submissions
              </a>
              </li> ";
      return $html;
      }
    }
    
    
  /**
   *@method getLeftLink
   * will generate a link for this module to be displayed in the main view.
   *@return ['challenge' => [ link to challenge module, module icon image path]]
  */
  public function getLeftLink()
    {
    $fc = Zend_Controller_Front::getInstance();
    $baseURL = $fc->getBaseUrl();
    $moduleWebroot = $baseURL . '/' . MIDAS_CHALLENGE_MODULE;
    return array(ucfirst("Troubleshooting") => array($moduleWebroot . '/competitor/troubleshooting',  $baseURL . '/modules/challenge/public/images/system-help-3.png'));
    }
    
  /** Add a tab to the user's main page for competitors to submit results for a challenge  */
  public function getUserAction($args)
    {
    if(!isset($this->userSession->Dao))
      {
      return array();
      }
    
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
    if(!isset($this->userSession->Dao))
      {
      return array();
      }

    $apiargs['useSession'] = true;
    $challenges = $this->ModuleComponent->Api->competitorListChallenges($apiargs);
    if(!empty($challenges))
      {
      $fc = Zend_Controller_Front::getInstance();
      $moduleWebroot = $fc->getBaseUrl().'/'.$this->moduleName;
      return array($this->t('My challenge scores') => $moduleWebroot.'/competitor/scorelisting');
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
    $apiargs['communityId'] = $args['community']->getKey();
    $challenges = $this->ModuleComponent->Api->anonymousGetChallenge($apiargs);
    if(!empty($challenges))
      {
      $fc = Zend_Controller_Front::getInstance();
      $moduleWebroot = $fc->getBaseUrl().'/challenge';
      return array('Challenge dashboard' => $moduleWebroot.'/competitor/dashboard?communityId='.$apiargs['communityId']);
      }
    else
      {
      return array();
      }
    }
    
  /**
   * callback function to get 'edit challenge' tab
   *
   * @return array
   */
  public function getCommunityManageTab($args)
    {
    $apiargs['useSession'] = true;
    $apiargs['communityId'] = $args['community']->getKey();
    $challenges = $this->ModuleComponent->Api->anonymousGetChallenge($apiargs);
    if(!empty($challenges))
      {
      $fc = Zend_Controller_Front::getInstance();
      $moduleWebroot = $fc->getBaseUrl().'/challenge';
      return array('Edit Challenge' => $moduleWebroot.'/admin/edit?communityId='.$apiargs['communityId']);
      }
    else
      {
      return array();
      }
    }  
 
  /**
   * callback function to get 'create a challenge' action
   *
   * @return array
   */
  public function getCommunityViewAction($args)
    {
    $apiargs['useSession'] = true;
    $apiargs['communityId'] = $args['community']->getKey();
    $challenges = $this->ModuleComponent->Api->anonymousGetChallenge($apiargs);
    if(empty($challenges))
      {
      $fc = Zend_Controller_Front::getInstance();
      $moduleWebroot = $fc->getBaseUrl().'/challenge';
      $moduleFileroot =  $fc->getBaseUrl().'/modules/'.$this->moduleName;
      return array($this->t('Create a challenge') => 
                   array("property" => 'onclick=midas.challenge.admin.createChallenge('.$apiargs['communityId'].');', "image" => $moduleFileroot.'/public/images/competitors.png') );
      }
    }
  
  /**
   * callback function to get java script
   *
   * @return array
   */
  public function getCommunityViewJSs()
    {
    $fc = Zend_Controller_Front::getInstance();
    $moduleUriroot = $fc->getBaseUrl().'/modules/challenge';
    return array($moduleUriroot.'/public/js/admin/admin.create.js');
    }
    
  public function userJoinedCommunity($args)
    {
    $userDao = $args['user'];
    $communityDao = $args['community'];
    // get the challenge from the community
    $apiargs['useSession'] = true;
    $apiargs['communityId'] = $args['community']->getKey();
    $challenges = $this->ModuleComponent->Api->anonymousGetChallenge($apiargs);
    if(!empty($challenges) && sizeof($challenges) > 0)
      {
      foreach($challenges as $challengeId => $statuses)
        {
        // create the user folders for the challenge
        $modelLoad = new MIDAS_ModelLoader();
        $challengeModel = $modelLoad->loadModel('Challenge', 'challenge');
        $challenge = $challengeModel->load($challengeId);
        $challengeModel->addUserToChallenge($userDao, $challenge);
        }
      }
    }
    
  } //end class
  
?>
