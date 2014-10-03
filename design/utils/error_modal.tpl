<div id="errorModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="errorModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">

			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3 id="errorModalLabel">Ошибка</h3>
			</div>

			<div class="modal-body" id="errorModalText">

			</div>

			<div class="modal-footer">
				<button class="btn btn-default" data-dismiss="modal">Закрыть</button>
			</div>

		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

{literal}
<script>
	function showErrorModal(error, label)
	{
		if (!label) label = 'Ошибка';
		$('#errorModalLabel').html(label);

		error = '<p>' + error.replace(/\n\n/g, '</p><p>') + '</p>';
		error = error.replace(/<h1>/g, '').replace(/<\/h1>/g, '');
		$('#errorModalText').html(error);

		$('#errorModal').modal();
	}
</script>
{/literal}
