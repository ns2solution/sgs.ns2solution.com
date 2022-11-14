<div class="modal fade" id="modalYesNo" tabindex="-1" data-backdrop="static" data-keyboard="false">
	<div class="modal-dialog modal-dialog-centered" role="document" style="max-width:425px;">
		<div class="modal-content" style="border:none;">
			<form method="get" id="modalYesNo-form">
				<div class="modal-header" style="background:rgb(42,143,204);background:linear-gradient(41deg, rgba(42,143,204,1) 0%, rgba(142,205,243,1) 50%);">
					<h5 class="modal-title" style="color:#fff;" id="modalYesNo-ttl"></h5>
				</div>
				<div class="modal-body">
					<center>
						<img src="{{ asset('assets/main/img/icon_warning.png') }}" style="width:85px;" draggable="false">
						<p class="mt-3 mb-2" id="modalYesNo-msg"></p>
						<p type="hidden" id="modalYesNo-data"></p>
					</center>
				</div>
				<div class="modal-footer" style="display:block;">
					<center>
						<button type="button" class="btn btn-light" data-dismiss="modal"> Tidak </button>
						<button type="submit" class="btn btn-danger ml-2"> Ya, Hapus </button>
					</center>
				</div>
			</form>
		</div>
	</div>
</div>