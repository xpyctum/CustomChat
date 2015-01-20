<?php

/Economy$Job API/
 if($this->economyjob && isset($this->economyjob->getPlayers()[$player->getName()])
 $job = $this->economyjob->player->get($sender->getName());
 $format = str_replace("{JOB}",$job, $format);
 }else{
 $nojob = $this->pgin->getConfig()->get("if-player-has-no-job");
 $format = str_replace("{JOB}",$nojob, $format);
 }
 /Economy$Job API END/ 
