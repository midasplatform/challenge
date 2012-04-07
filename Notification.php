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
    }//end init
    
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
    return array(ucfirst("competitors") => array($moduleWebroot . '/index',  $baseURL . '/modules/challenge/public/images/competitors.png'));
    }
    
  } //end class
  
?>