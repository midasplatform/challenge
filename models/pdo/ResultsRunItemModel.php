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
require_once BASE_PATH . '/modules/challenge/models/base/ResultsRunItemModelBase.php';


/** Challenge_ResultsRunItemModel */
class Challenge_ResultsRunItemModel extends Challenge_ResultsRunItemModelBase {


  function loadResultsItemsValues($challengeResultsRunId)
    {
    $sql = $this->database->select()->setIntegrityCheck(false);
    $sql->from(array('crri' => 'challenge_results_run_item'), array('test_item_id', 'results_item_id', 'output_item_id'));
    $sql->joinInner(array('i1' => 'item'), 'crri.test_item_id=i1.item_id', array('test_item_name' => 'i1.name'));
    $sql->joinInner(array('i2' => 'item'), 'crri.results_item_id=i2.item_id', array('results_item_name' => 'i2.name'));
    $sql->joinInner(array('i3' => 'item'), 'crri.output_item_id=i3.item_id', array('output_item_name' => 'i3.name'));
    $sql->join(array('vs' => 'validation_scalarresult'), 'vs.scalarresult_id = crri.validation_scalarresult_id');
    $sql->where('crri.challenge_results_run_id=?', $challengeResultsRunId);
    $rowset = $this->database->fetchAll($sql);

    // this is what the sql looks like:
      /*
      select test_item_id,
             results_item_id,
             output_item_id,
             i1.name as test_item_name,
             i2.name as results_item_name,
             i3.name as output_item_name,
             value from validation_scalarresult as vs,
             challenge_results_run_item as crri
             INNER JOIN item as i1 on crri.test_item_id=i1.item_id
             INNER JOIN item as i2 on crri.results_item_id=i2.item_id
             INNER JOIN item as i3 on crri.output_item_id=i3.item_id
             where challenge_results_run_id = 19 and
             crri.validation_scalarresult_id=vs.scalarresult_id;
      */

    $returnVals = array();
    foreach($rowset as $row)
      {
      $returnRow = array();
      $returnRow['result_item_id'] = $row['results_item_id'];
      $returnRow['result_item_name'] = $row['results_item_name'];
      $returnRow['output_item_id'] = $row['output_item_id'];
      $returnRow['output_item_name'] = $row['output_item_name'];
      $returnRow['test_item_id'] = $row['test_item_id'];
      $returnRow['test_item_name'] = $row['test_item_name'];
      $returnRow['score'] = $row['value'];
      $returnVals[] = $returnRow;
      }
    return $returnVals;
    }


}
