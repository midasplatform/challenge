var midas = midas || {};
midas.challenge = midas.challenge || {};
midas.challenge.config = midas.challenge.config || {};

midas.challenge.config.callbackCheck = function callbackCheck(node) {
    $('div.viewAction').show();
    $('div.viewInfo').show();
    $('img.infoLoading').show();
    $('div.ajaxInfoElement').html('');

    if(midas.ajaxSelectRequest != '') {
        midas.ajaxSelectRequest.abort();
    }
    var type = node.attr('type');
    var element = node.attr('element');

    $('div.viewAction ul').fadeOut('fast' ,function () {
        $('div.viewAction ul').html('');
        var html='';
        if(type=='community') {
            html+='<li><img alt="" src="'+json.global.coreWebroot+'/public/images/icons/view.png"/> <a href="'+json.global.webroot+'/community/'+element+'">'+json.browse.view+'</a></li>';
            html+='<li><img alt="" src="'+json.global.webroot+'/modules/challenge/public/images/competitors.png"/> <a href="'+json.global.webroot+'/challenge/config/enable?communityIds='+element+'">'+json.challenge.message.enableCommunity+'</a></li>';
            html+='<li><img alt="" src="'+json.global.coreWebroot+'/public/images/icons/close.png"/> <a href="'+json.global.webroot+'/challenge/config/disable?communityIds='+element+'">'+json.challenge.message.disableCommunity+'</a></li>';
        }
        $('div.viewAction ul').html(html);
        $('div.viewAction ul').fadeIn('fast');
    });

    midas.ajaxSelectRequest = $.ajax({
        type: "POST",
        url: json.global.webroot+'/browse/getelementinfo',
        data: {type: node.attr('type'), id: node.attr('element')},
        success: function (jsonContent) {
            midas.createInfo(jsonContent);
            $('img.infoLoading').hide();
        }
    });
}


midas.challenge.config.callbackCheckboxes = function callbackCheckboxes(node) {
    var arraySelected = [];
    arraySelected['communities'] = [];
    var selectedRows = [];

    var communities = '';
    node.find(".treeCheckbox:checked").each(function () {
        arraySelected['communities'].push($(this).attr('element'));
        communities+=$(this).attr('element')+'-';
        selectedRows.push($(this).closest('tr').attr('id'));
    });

    if((arraySelected['communities'].length) > 0) {
        $('div.viewSelected').show();
        var html = ' (' + arraySelected['communities'].length;
        html+=' '+json.browse.element;
        if((arraySelected['communities'].length) !== 1) {
            html+='s';
            $('div.viewAction').hide();
            $('div.viewInfo').hide();
        }
        html += ')';
        $('div.viewSelected h1 span').html(html);
        var links = '<ul>';
        links += '<li style="background-color: white;">';
        links += '  <img alt="" src="' + json.global.webroot + '/modules/challenge/public/images/competitors.png"/> ';
        links += '  <a href="'+json.global.webroot+'/challenge/config/enable?communityIds='+communities+'">'+json.challenge.message.enableCommunity+'</a>';
        links += '</li>';
        links += '<li style="background-color: white;">';
        links += '  <img alt="" src="' + json.global.coreWebroot + '/public/images/icons/close.png"/> ';
        links += '  <a href="'+json.global.webroot+'/challenge/config/disable?communityIds='+communities+'">'+json.challenge.message.disableCommunity+'</a>';
        links += '</li>';
        links += '</ul>';
        $('div.viewSelected>span').html(links);
        $('div.viewSelected li a').append(' ('+arraySelected['communities'].length+')');
    }
    else {
        $('div.viewSelected').hide();
        $('div.viewSelected h1 span').html('');
    }

}

$(document).ready(function () {
    $("#browseTable").treeTable({
      callbackSelect: midas.challenge.config.callbackCheck,
      callbackCheckboxes:  midas.challenge.config.callbackCheckboxes
    });
    $("img.tableLoading").hide();
    $("table#browseTable").show();
    midas.browser.enableSelectAll({
      callback: midas.challenge.config.callbackCheckboxes
    });
});
