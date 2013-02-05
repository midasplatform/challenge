var midas = midas || {};
midas.challenge = midas.challenge || {};
midas.challenge.competitor = midas.challenge.competitor || {};
midas.challenge.competitor.errordetails = midas.challenge.competitor.errordetails || {};


 

$(document).ready(function () {
    $( "#tabsErrordetails" ).tabs({
        select: function(event, ui) {
            $('div.tabs-log').show();
            $('div.tabs-output').show();
            $('div.tabs-error').show();
        }
    });
    $("#tabsErrordetails").show();
});
