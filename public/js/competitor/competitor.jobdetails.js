var midas = midas || {};
midas.challenge = midas.challenge || {};
midas.challenge.competitor = midas.challenge.competitor || {};
midas.challenge.competitor.jobdetails = midas.challenge.competitor.jobdetails || {};


 

$(document).ready(function () {
    $( "#tabsJobdetails" ).tabs({
        select: function(event, ui) {
        }
    });
    var inError = $("#jobDetailsInError").text();
    if(inError == "1") {
        $('#tabsJobdetails').tabs('select', 1);
    }
    else {
        $('#tabsJobdetails').tabs('select', 2);
    }
    $("#tabsJobdetails").show();
});
