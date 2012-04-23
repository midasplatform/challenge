var midas = midas || {};
midas.challenge = midas.challenge || {};
midas.challenge.admin = midas.challenge.admin || {};


midas.challenge.admin.validateChallengeChange = function (formData, jqForm, options) {

}

midas.challenge.admin.successChallengeChange = function (responseText, statusText, xhr, form) {
    var jsonResponse = $.parseJSON(responseText);
    if(jsonResponse == null) {
        //midas.createNotice('Error', 4000, 'error');
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


$(document).ready(function() {
    $('#editChallengeForm').ajaxForm({
        beforeSubmit: midas.challenge.admin.validateChallengeChange(),
        success: midas.challenge.admin.successChallengeChange()
    });
});