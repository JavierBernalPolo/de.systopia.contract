<?php
/*-------------------------------------------------------------+
| SYSTOPIA Contract Extension                                  |
| Copyright (C) 2017 SYSTOPIA                                  |
| Author: M. McAndrew (michaelmcandrew@thirdsectordesign.org)  |
|         B. Endres (endres -at- systopia.de)                  |
| http://www.systopia.de/                                      |
+--------------------------------------------------------------*/


// Helper class for working with changes that are stored in modification
// activities
class CRM_Contract_Handler_ModificationActivityHelper{

  static function getContractParams($activity){
    $params['id'] = $activity['source_record_id'];
    $modificationClass = CRM_Contract_ModificationActivity::findById($activity['activity_type_id']);
    $params['status_id'] = $modificationClass->getEndStatus();
    switch($modificationClass->getAction()){
      case 'update':
      case 'revive':
        if(isset($activity[CRM_Contract_Utils::contractToActivityFieldId('membership_type_id')])){
          $params['membership_type_id'] = $activity[CRM_Contract_Utils::contractToActivityFieldId('membership_type_id')];
        }
        if(isset($activity[CRM_Contract_Utils::contractToActivityFieldId('membership_payment.membership_recurring_contribution')])){
          $params[CRM_Contract_Utils::getCustomFieldId('membership_payment.membership_recurring_contribution')] = $activity[CRM_Contract_Utils::contractToActivityFieldId('membership_payment.membership_recurring_contribution')];
        }
        break;
      case 'cancel':
        if(isset($activity[CRM_Contract_Utils::contractToActivityFieldId('membership_cancellation.membership_cancel_reason')])){
          $params[CRM_Contract_Utils::getCustomFieldId('membership_cancellation.membership_cancel_reason')] = $activity[CRM_Contract_Utils::contractToActivityFieldId('membership_cancellation.membership_cancel_reason')];
        }
        break;
    }
    return $params;
  }
}
