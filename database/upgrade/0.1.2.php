<?php

class Challenge_Upgrade_0_1_2 extends MIDASUpgrade
{
  public function preUpgrade()
    {
    }

  public function mysql()
    {
    $this->db->query("ALTER TABLE challenge_selected_metrics drop column label_value");
    $this->db->query("ALTER TABLE challenge_selected_metrics drop column result_key");
    }
    
  public function postUpgrade()
    {
    }
}
?>
