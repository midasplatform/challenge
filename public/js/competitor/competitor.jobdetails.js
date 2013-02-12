var midas = midas || {};
midas.challenge = midas.challenge || {};
midas.challenge.competitor = midas.challenge.competitor || {};
midas.challenge.competitor.jobdetails = midas.challenge.competitor.jobdetails || {};


 

$(document).ready(function () {
    $( "#tabsJobdetails" ).tabs({
        select: function(event, ui) {
            $('div.tabs-log').show();
            $('div.tabs-output').show();
            $('div.tabs-error').show();
        }
    });
    $("#tabsJobdetails").show();
});
