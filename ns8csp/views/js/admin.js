/**
 *  @author    NS8.com <support@ns8.com>
 *  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  @copyright 2018 NS8.com
 */
//  the script used for the admin container controller

function initializeAdmin() {

    $('.adminns8csp .breadcrumb-current').hide();

    $('.adminns8csp .breadcrumb.page-breadcrumb').hide();

    $('.adminns8csp h2.page-title').html('<img id="ns8HeaderIcon" src="/modules/ns8csp/views/img/csp-big.png" height=40 />NS8 Protect™');

    // delay this because if we ran synchronously here, presta 1.7 will immediately overwrite it with the plaintext module name
    window.setTimeout(function() {
        $('.adminns8csp.psv_1_7 h1.page-title').html('<img id="ns8HeaderIcon" src="/modules/ns8csp/views/img/csp-big.png" height=40 />NS8 Protect™');
    });

    psVerAr = _PS_VERSION_.split('.');

    //  1.6.0 is special!
    if (psVerAr[0] == '1' && psVerAr[1] == '6' && psVerAr[2] == '0')
        $('body').addClass('psv_1_6_0');
    else
        $('body').addClass('psv_' + psVerAr[0] + '_' + psVerAr[1]);
 
    var myEventMethod =
        window.addEventListener ? "addEventListener" : "attachEvent";

    var myEventListener = window[myEventMethod];

    // browser compatibility: attach event uses onmessage
    var myEventMessage =
        myEventMethod === "attachEvent" ? "onmessage" : "message";

    // register callback function on incoming message
    myEventListener(myEventMessage, function (e) {
        if (e.origin != ns8BaseUrl) {
            return;
        }

        switch (e.data.type) {
            case 'setPath':
                if (history.replaceState) {
                    history.replaceState({}, '', setQueryStringParam('path', e.data.path));
                }
                break;
            case 'frameLoad':
                window.scrollTo(0, 0);
                document.getElementById('ns8-app-iframe').height = e.data.height + "px";
                break;
            case 'regNav':
                $('#ns8-app-nav').html(e.data.nav);
                break;
            case 'frameResize':
                document.getElementById('ns8-app-iframe').height = e.data.height + "px";
                break;
            case 'flashNotice':
                toastr.options.closeButton = true;
                toastr.options.positionClass = "toast-top-center";
                toastr.info(e.data.message);
                break;
            case 'flashError':
                toastr.options.closeButton = true;
                toastr.options.positionClass = "toast-top-center";
                toastr.error(e.data.message);
                break;
            case 'modal':
                $("#ns8ModalBody").html(e.data.body);

                if (e.data.title)
                    $("#ns8ModalTitle").html(e.data.title);

                $("#ns8Modal").modal();
                break;
            case 'showOrder':
                window.location = orderLink + '&id_order=' + e.data.id + '&vieworder';
                break;
            case 'cancelOrder':

                var query = $.ajax({
                    type: 'GET',
                    url: moduleAdminLink,
                    data: {
                        ajax: 1,
                        action: 'cancelOrder',
                        id: e.data.id
                    },
                    dataType: 'json',
                    error: function (request, status, error) {
                        alert(error);
                    }
                });
                break;
        }
    }, false);
}

function ns8Navigate(path) {
    $('#ns8-app-iframe').attr('src', path);
}

function setQueryStringParam(key, value, query, doNotEncode) {

    query = query || window.location.search;

    query = stripQueryStringParam(key, query);
    var anchor = '';

    if (query.indexOf('#') > -1) {
        anchor = query.substr(query.indexOf('#'));
        query = query.substr(query, query.indexOf('#'));
    }

    if (query.indexOf('?') > -1 || query.indexOf('&') > -1) {
        return query + "&" + key + '=' + (doNotEncode ? value : encodeURIComponent(value)) + anchor;
    }
    else {
        return query + "?" + key + '=' + (doNotEncode ? value : encodeURIComponent(value)) + anchor;
    }
}

function stripQueryStringParam(key, query) {
    if (!query)
        query = window.location.search;

    var pos2, pos = query.indexOf('?' + key + '='), delim = '?';

    if (pos === -1) {
        delim = '&';
        pos = query.indexOf('&' + key + '=');
    }

    if (pos > -1) {
        pos2 = query.indexOf('&', pos + 1);

        if (pos2 === -1) {
            pos2 = query.indexOf('#', pos + 1);
        }

        if (pos2 === -1) {
            query = query.substr(0, pos);
        }
        else {
            query = query.substr(0, pos) + delim + query.substr(pos2 + 1);
        }
    }

    return query;
}

