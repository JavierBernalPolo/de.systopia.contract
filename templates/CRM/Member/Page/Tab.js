/*-------------------------------------------------------------+
| SYSTOPIA Contract Extension                                  |
| Copyright (C) 2017 SYSTOPIA                                  |
| Author: M. McAndrew (michaelmcandrew@thirdsectordesign.org)  |
|         B. Endres (endres -at- systopia.de)                  |
| http://www.systopia.de/                                      |
+--------------------------------------------------------------*/


CRM.$(function($) {

  var contractStatuses = CRM.vars['de.systopia.contract'].contractStatuses;

  // # Link to membership create should be replaced with contract create #
  $('a[href="/civicrm/contact/view/membership?reset=1&action=add&cid=' + CRM.vars['de.systopia.contract'].cid + '&context=membership"]')
  .attr("href", "/civicrm/contract/create?cid=" + CRM.vars['de.systopia.contract'].cid);

  // Add a membership-review tr with appropriate colspan under each row to
  // receive the contract history
  $( "#memberships tr.crm-membership, #inactive-memberships tr.crm-membership" ).each( function(){
    var elementId = $(this).attr('id');
    var membershipId = elementId.substr(elementId.indexOf("_") + 1);
    var link = " <a class='toggle-review' id='crm-membership-review-link_" + membershipId + "' href='/civicrm/contract/review?reset=&snippet=1&id=" + membershipId + "'>" + getReviewLinkText(membershipId) + "</a>";
    $(this).after("<tr class='crm-membership crm-membership-review odd odd-row' id='crm-membership-review_" + membershipId + "'><td colspan='" + $(this).find('td').length + "'></td></tr>");
    $(this).find('td.crm-membership-status').append(link);
    decorateRow(membershipId);
  });

  // hide the .membership-review tr by default
  $( "tr.crm-membership-review" ).hide();

  function getReviewLinkText(membershipId){
    if(contractStatuses[membershipId].needs_review > 0){
      return 'needs review';
    }else if(contractStatuses[membershipId].scheduled > 0) {
      return 'scheduled modifications';
    }
    return 'review';
  }

  function decorateRow(membershipId){
    var contractStatus = contractStatuses[membershipId];
    var membershipRow = $(document).find('#crm-membership_' + membershipId);

    if(contractStatus.needs_review > 0){
      membershipRow.addClass('needs-review');
    }else{
      membershipRow.removeClass('needs-review');
    }

    if(contractStatus.scheduled > 0){
      membershipRow.addClass('scheduled');
    }else{
      membershipRow.removeClass('scheduled');
    }
  }

  function toggleReviewPane(membershipId){
    console.log('toggle time');
    var reviewRow = $(document).find('#crm-membership-review_' + membershipId);
    var reviewLink = $(document).find('#crm-membership-review-link_' + membershipId);
    if(reviewRow.is(":visible")){
      reviewRow.hide();
      reviewLink.html(getReviewLinkText(membershipId));
    }else{
      reviewRow.find('td').load('/civicrm/contract/review?reset=&snippet=1&id=' + membershipId, function(){
        reviewRow.show();
        reviewLink.html('hide');
      });
    }
  }

  function updateReviewPane(membershipId){
    var reviewRow = $(document).find('#crm-membership-review_' + membershipId);
    reviewRow.find('td').load('/civicrm/contract/review?reset=&snippet=1&id=' + membershipId);
  }

  function updateMembershipRow(membershipId){
    CRM.api3('Contract', 'get_open_modification_counts', {
      "id": membershipId
    }).done(function(result) {
      contractStatuses[membershipId] = result;
      decorateRow(membershipId);
    });
  }

  // We have to ensure the listeners are only added once (this seems like a
  // fairly mad way to do that but anyhow...)

  if(CRM.vars['de.systopia.contract'].listenersLoaded !== true){

    // Clicking on the toggle-review link toggles the membership-review tr
    $(document).on( 'click', '.toggle-review', function(e){
      e.preventDefault();
      var elementId = $(this).attr('id');
      var membershipId = elementId.substr(elementId.indexOf("_") + 1);
      toggleReviewPane(membershipId);
    });

    // Modification edit activities open in a pop up
    $(document).on( 'click', '.edit-activity', CRM.popup);

    // When modification edit activity pop ups are closed, update the appropriate
    // membership review pane and membership row
    $(document).on( 'crmPopupFormSuccess', '.edit-activity', function(){
      var elementId = $(this).parents('.crm-membership-review').attr('id');
      var membershipId = elementId.substr(elementId.indexOf("_") + 1);
      updateReviewPane(membershipId);
      updateMembershipRow(membershipId);
    });

    $(document).on( 'crmPopupFormSuccess', '.action-item', function(){
      var elementId = $(this).parents('.crm-membership').attr('id');
      var membershipId = elementId.substr(elementId.indexOf("_") + 1);
      console.log(membershipId);
    });

    CRM.vars['de.systopia.contract'].listenersLoaded = true;
  }
});
