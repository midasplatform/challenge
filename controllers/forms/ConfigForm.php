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
  public function createEditChallengeForm($community_id, $action, $allMetrics)
    {
    $form = new Zend_Form;

    $form->setAction($this->webroot.'/challenge/admin/'.$action.'?communityId='.$community_id)
          ->setMethod('post');

    $name = new Zend_Form_Element_Text('name');
    $name ->setRequired(true)
          ->addValidator('NotEmpty', true);

    $description = new Zend_Form_Element_Textarea('description');
    $numberScoredLabels = new Zend_Form_Element_Textarea('number_scored_labels');
    $numberScoredLabels->setRequired(true)
                       ->addValidator('Digits');

    $trainingStatus = new Zend_Form_Element_Radio('training_status');
    $trainingStatus->addMultiOptions(array(
                 MIDAS_CHALLENGE_STATUS_OPEN => $this->t("Open, competitors can score training data"),
                 MIDAS_CHALLENGE_STATUS_CLOSED => $this->t("Closed, competitors cannot score training data"),
                  ))
            ->setRequired(true)
            ->setValue(MIDAS_CHALLENGE_STATUS_CLOSED);
    $testingStatus = new Zend_Form_Element_Radio('testing_status');
    $testingStatus->addMultiOptions(array(
                 MIDAS_CHALLENGE_STATUS_OPEN => $this->t("Open, competitors can score testing data"),
                 MIDAS_CHALLENGE_STATUS_CLOSED => $this->t("Closed, competitors cannot score testing data, but can view existing scores"),
                  ))
            ->setRequired(true)
            ->setValue(MIDAS_CHALLENGE_STATUS_CLOSED);

    $formElements = array($name, $description, $numberScoredLabels, $trainingStatus, $testingStatus);
    foreach($allMetrics as $metric)
      {
      $testingStatus = new Zend_Form_Element_Radio($metric->getMetricExeName());
      $testingStatus->addMultiOptions(array(
                 '0' => $this->t("not selected"),
                 '1' => $this->t("selected"),
                  ))
            ->setRequired(true)
            ->setValue('0');
      $formElements[] = $testingStatus;
      }
    
    $submit = new  Zend_Form_Element_Submit('submit');
    $submit ->setLabel($this->t("Save"));
    $formElements[] = $submit;

    $form->addElements($formElements);
    return $form;
    }

} // end class
?>
