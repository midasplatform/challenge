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
require_once BASE_PATH . '/modules/challenge/models/base/MetricModelBase.php';


/** Challenge_MetricModel */
class Challenge_MetricModel extends Challenge_MetricModelBase {

    
  function fetchAll()
    {
    $rowset = $this->database->fetchAll();
    $return = array();
    foreach($rowset as $row)
      {
      $tmpDao = $this->initDao('Metric', $row, 'challenge');
      $return[] = $tmpDao;
      unset($tmpDao);
      }
    return $return;
    }
    
}
