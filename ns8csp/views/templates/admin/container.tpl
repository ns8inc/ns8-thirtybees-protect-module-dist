{*
*  @author    NS8.com <support@ns8.com>
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  @copyright 2018 NS8.com
*}

<!--  The Shared Nav bar for the app -->
<div id="ns8-app-nav" ></div>
<!--  The IFRAME that hosts the app -->
<iframe id="ns8-app-iframe" name="ns8-app-iframe" frameborder="0" scrolling="no" src="{$url|escape:'htmlall':'UTF-8'}"></iframe>

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
    var orderLink, moduleAdminLink, ns8BaseUrl;
    {if isset($orderLink)}
    orderLink = '{$orderLink|escape:'quotes':'UTF-8'}';
    {/if}

    {if isset($ns8BaseUrl)}
    ns8BaseUrl = '{$ns8BaseUrl|escape:'quotes':'UTF-8'}';
    {/if}

	{if isset($moduleAdminLink)}
    moduleAdminLink = '{$moduleAdminLink|escape:'quotes':'UTF-8'}';
    {/if}

    initializeAdmin();
</script>

