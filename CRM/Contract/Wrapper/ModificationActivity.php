<?php
/*-------------------------------------------------------------+
| SYSTOPIA Contract Extension                                  |
| Copyright (C) 2017 SYSTOPIA                                  |
| Author: M. McAndrew (michaelmcandrew@thirdsectordesign.org)  |
|         B. Endres (endres -at- systopia.de)                  |
| http://www.systopia.de/                                      |
+--------------------------------------------------------------*/

/**
* This class wraps calls to the activity create API and passes them to the
* ModificationActivity handler unless they have a status of scheduled and a date in
* the future
**/

class CRM_Contract_Wrapper_ModificationActivity{

  private static $_singleton;

  public static function singleton() {
    if (!self::$_singleton) {
      self::$_singleton = new CRM_Contract_Wrapper_ModificationActivity();
    }
    return self::$_singleton;
  }

  public function pre($op, $params){

    if(isset($params['resume_date'])){
      $this->resumeDate = $params['resume_date'];
    }

    $this->skip = false;
    if(isset($params['skip_handler']) && $params['skip_handler']){
      $this->skip = true;
      $this->reset();
      return;
    }

    // ##### START OF FACT FINDING MISSION ##### //

    // If this is a create, the id will not be passed in params
    // If this is an edit or delete, the id will be passed

    // Set the operation
    $this->op = $op;

    // Get the start state and the contract id
    if($this->op == 'create'){
      $this->startState = [];
      $this->startStatus = '';
    }else{
      $this->startState = civicrm_api3('Activity', 'getsingle', ['id' => $params['id']]);
      $this->startStatus = civicrm_api3('OptionValue', 'getvalue', [ 'return' => "name", 'option_group_id' => "activity_status", 'value' => $this->startState['status_id']]);
      $this->contractId = $this->startState['source_record_id'];
      $this->checkActivityType($this->startState);
    }
  }

  public function post($id, $objectRef){

    // Return early if we can.
    if($this->skip){
      $this->reset();
      return;
    }

    // If this is a delete, the id will not be passed in params
    // If this is an edit or create, the id will be passed

    if($this->op == 'delete'){
      $this->endState = [];
      $this->endStatus = '';
    }else{
      $this->endState = civicrm_api3('Activity', 'getsingle', ['id' => $id]);
      $this->endStatus = civicrm_api3('OptionValue', 'getvalue', [ 'return' => "name", 'option_group_id' => "activity_status", 'value' => $this->endState['status_id']]);
      $this->contractId = $this->endState['source_record_id'];
      $this->checkActivityType($this->endState);
    }

    // If this is not a contract modification activity (checkActivityType), return
    // We can't do this in pre when the op is create.
    if($this->skip){
      $this->reset();
      return;
    }

    // ##### END OF FACT FINDING MISSION ##### //

    // By this point, we know that we are dealing with a modification activity,
    // and we know the type of operation; the start state, the end state and
    // a textual representation of the start and end statuses and the contract id

    // If the status was changed to needs review, presume that this was
    // done intentionally, and do not trigger any further checks for conflicts
    if($this->startStatus == 'Needs Review' && ($this->endStatus == 'Scheduled' || $this->endStatus == 'Cancelled')){
      $this->reset();
      return;
    }

    // If we still here check the contract for possible conflicts.
    $conflictHandler = new CRM_Contract_Handler_ModificationConflicts;
    $conflictHandler->checkForConflicts($this->contractId);
  }

  // This function checks to see whether the activity that has been wrapped is
  // relevant, i.e. is a modification activity
  function checkActivityType($activity){
    if(!in_array($activity['activity_type_id'], CRM_Contract_ModificationActivity::getModificationActivityTypeIds())){
      $this->skip = true;
    }
  }
  // It feels prudent to unset all values of this wrapper once we are finished
  // with it so ensure that if and when it is run multiple times in one
  // execution, it is not polluted with details from previous runs
  // information from previous
  function reset(){
    unset($this->op);
    unset($this->startState);
    unset($this->startStatus);
    unset($this->endState);
    unset($this->endStatus);
    unset($this->contractId);
  }

}
