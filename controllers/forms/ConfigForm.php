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

  /** create create challenge form */
  public function createEditChallengeForm($community_id)
    {
    $form = new Zend_Form;

    $form->setAction($this->webroot.'/challenge/admin/edit?communityId='.$community_id)
          ->setMethod('post');

    $name = new Zend_Form_Element_Text('name');
    $name ->setRequired(true)
          ->addValidator('NotEmpty', true);

    $description = new Zend_Form_Element_Textarea('description');

    $status = new Zend_Form_Element_Radio('status');
    $status->addMultiOptions(array(
                 MIDAS_CHALLENGE_STATUS_OPEN => $this->t("Open, competitors can participate in"),
                 MIDAS_CHALLENGE_STATUS_CLOSED=> $this->t("Closed, competitors cannot participate in, but can view existing scores"),
                  ))
            ->setRequired(true)
            ->setValue(MIDAS_CHALLENGE_STATUS_CLOSED);

    $submit = new  Zend_Form_Element_Submit('submit');
    $submit ->setLabel($this->t("Save"));

    $form->addElements(array($name, $description, $status, $submit));
    return $form;
    }

} // end class
?>
