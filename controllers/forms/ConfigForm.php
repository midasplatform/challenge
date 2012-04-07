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

/**
 * Challenge_ChallengeForm
 */
class Challenge_ConfigForm extends AppForm
{

  /**
   * @method createSelectChallengeForm
   * create a form with a drop_down list and a submit button.
   */
  public function createSelectChallengeForm($challenges)
    {
    $form = new Zend_Form;

    $form->setAction($this->webroot.'/challenge/competitor/init')
         ->setMethod('post');

    $formElements = array();

    $challengeSelect = new Zend_Form_Element_Select('challengeList');
    $challengeSelect->setLabel('Please select a challenge to participate:');
    $challengeSelect->addMultiOptions($challenges);
    $formElements[] = $challengeSelect;

    $submitSelect = new Zend_Form_Element_Submit("submitSelect");
    $submitSelect ->setLabel($this->t("Select"));
    $formElements[] = $submitSelect;

    $form->addElements($formElements);
    return $form;
    }
} // end class
?>
