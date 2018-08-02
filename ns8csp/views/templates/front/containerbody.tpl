{*
*  @author    NS8.com <support@ns8.com>
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  @copyright 2018 NS8.com
*}

<!--  The IFRAME that hosts the app  -->
<iframe id="ns8-app-iframe" src="{$url|escape:'htmlall':'UTF-8'}" frameborder="0" scrolling="no" style="min-height: 300px;"></iframe>

<!--  Used to display modals from within the IFRAME  -->
<div id="ns8Modal" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 id="ns8ModalTitle" class="modal-title"></h4>
            </div>
            <div id="ns8ModalBody" class="modal-body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>

    </div>
</div>

<!--  Message handlers for message from the IFRAME  -->
<script>
    var ns8BaseUrl;
    {if isset($ns8BaseUrl)}
    ns8BaseUrl = '{$ns8BaseUrl|escape:'quotes':'UTF-8'}';
    {/if}

    var myEventMethod =
        window.addEventListener ? "addEventListener" : "attachEvent";

    var myEventListener = window[myEventMethod];

    // browser compatibility: attach event uses onmessage
    var myEventMessage =
        myEventMethod == "attachEvent" ? "onmessage" : "message";

    // register callback function on incoming message
    myEventListener(myEventMessage, function (e) {
        if (e.origin != ns8BaseUrl) {
            return;
        }

        switch (e.data.type) {
            case 'frameLoad':
                window.scrollTo(0, 0);
                document.getElementById('ns8-app-iframe').height = e.data.height + "px";
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
        }
    }, false);
</script>
