{if $popupwindow}
</div></div></div></body></html>
{else}
		</div> <!-- #main-content -->
	</div> <!-- .row -->

	<footer class="py-3 my-4 border-top">
		<div class="col-12 text-center text-muted">
			{$COPYRIGHT}
		</div>
	</footer>
</div> <!-- .container-fluid -->

<div class="modal hide modal-sheet position-static bg-secondary py-5" tabindex="-1" role="dialog" id="license-modal" data-bs-backdrop="static" data-bs-keyboard="false" >
	<div class="modal-dialog" role="document">
		<div class="modal-content rounded-6 shadow">
			<div class="modal-header border-bottom-0">
				<h5 class="modal-title">Licence Violation!</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body py-0">
				<p>Dear A2Billing Administrator</p>
				<p>Thank you for using A2Billing. However, we have detected that you have edited the Author’s names, Copyright or licensing information in the A2Billing Management Interface.</p>
				<p>The <a href="http://www.fsf.org/licensing/licenses/agpl-3.0.html" target="_blank">AGPL 3</a> license under which you are allowed to use A2Billing requires that the original copyright and license must be displayed and kept intact. Without this information being displayed, you do not have a right to use the software.</p>
				<p>However, if it is important to you that the Author’s names, Copyright and License information is not displayed, possibly for publicity purposes; then we can offer you additional permissions to use and convey A2Billing, with these items removed, for a fee that will be used to help sponsor the continued development of A2Billing.</p>
				<p>For more information, please go to <a target="_blank" href="http://www.asterisk2billing.org/pricing/rebranding/">http://www.asterisk2billing.org/pricing/rebranding/</a>.</p>
				<p>Yours,<br/>The A2Billing Team<br/>Star2Billing S.L</p>
			</div>
			<div class="modal-footer flex-column border-top-0">
				<button type="button" class="btn btn-lg btn-light w-100 mx-0" data-bs-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

{include file="profiler.tpl"}

</body>
</html>

{/if}
