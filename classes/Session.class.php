<?php
  class Session extends __Session{
		public static $sessionTimeout=18000;  //seconds  5*60*60

    protected function setProperties($data){
      if($data && $data["user"]){
        $users=User::select(array(
          "filters"=>array(
            array(
              "field"=>"rowid",
              "value"=>$data["user"]
            )
          )
        ),1);
        
        if(count($users)!=1) throw new NomoException("Érvénytelen userid a sessionben'".$data["user"]."'!",21);
        $user=$users[0];

        $groupid=User::ACTIVE;//regisztrált user
        if($user["adminisztrator"]) $groupid=$groupid+User::ADMIN;
        if($user["technikus"]) $groupid=$groupid+User::TECHNIKUS; 
        if($user["gepjarmu_vezeto"]) $groupid=$groupid+User::SOFOR;
        if($user["szervizes"]) $groupid=$groupid+User::SZERVIZES;
        if($user["ugykezelo"]) $groupid=$groupid+User::UGYKEZELO; 
        if($user["ugyvezeto"]) $groupid=$groupid+User::VEZETO; 
        if($user["superadmin"]) $groupid=User::SUPERADMIN; 
        if($user["muszaki_vezeto"]) $groupid=User::MUSZAKI_VEZETO; 
        if($user["tulajdonos"]) $groupid=User::TULAJDONOS; 
        if($user["kezelest_megtekintheti"]) $groupid=User::ACTIVE+User::KEZELEST_MEGTEKINTHETI;

        $this->groupid=$groupid;
        $this->userid=(int)$user["rowid"];
      }else{
        $this->groupid=0;
        $this->userid=null; 
      }
    } 
	}
?>
