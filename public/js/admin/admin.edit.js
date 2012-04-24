var midas = midas || {};
midas.challenge = midas.challenge || {};
midas.challenge.admin = midas.challenge.admin || {};


midas.challenge.admin.validateChallengeChange = function (formData, jqForm, options) {
  var form = jqForm[0];
  if(form.name.value.length < 1) {
      midas.createNotice('Please, set the challenge name', 4000, 'error');
      return false;
  }
}

midas.challenge.admin.successChallengeChange = function (responseText, statusText, xhr, form) {
    try {
        var jsonResponse = $.parseJSON(responseText);
    } catch (e) {
        midas.createNotice(responseText, 4000, 'error');
        return false;
    }
    if(jsonResponse == null) {
        midas.createNotice('Error', 4000, 'error');
        return;
    }
    if(jsonResponse[0]) {
        midas.createNotice(jsonResponse[1], 4000);
        $('#tabsGeneric').tabs('load', $('#tabsGeneric').tabs('option', 'selected')); //reload tab
    }
    else {
        midas.createNotice(jsonResponse[1], 4000, 'error');
    }
}

/**
 * An ajax based form submission for form 'editChallengeForm'
*/
$(document).ready(function() {
    $('#editChallengeForm').ajaxForm({
        beforeSubmit: midas.challenge.admin.validateChallengeChange,
        success: midas.challenge.admin.successChallengeChange
    });
});
