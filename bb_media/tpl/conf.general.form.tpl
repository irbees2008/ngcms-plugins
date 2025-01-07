<form method="post" action="admin.php?mod=extra-config&amp;plugin=bb_media&amp;action=general_submit">
	<div class="col-sm-12 mt-2">
		<div class="card">
			<div class="card-header">{l_bb_media:general}</div>
			<div class="card-body">
				<table class="table table-sm">
					<tr>
						<td width="50%">{l_bb_media:player}<br />
							<small>{l_bb_media:desc_player}</small>
						</td>
						<td width="50%">
							<select name="player_name" class="custom-select" id="canonical">
								{player_name}
							</select>
						</td>
					</tr>
					<tr class="useCanonical">
						<td width="50%">{l_bb_media:theme_player}</td>
						<td width="50%">
							<select name="theme_player" class="custom-select" id="canonical">
								{theme_player}
							</select>
						</td>
					</tr>
				</table>
			</div>
			<div class="card-footer text-center"><input type="submit" name="submit" value="{l_bb_media:button_save}" class="btn btn-outline-success" /></div>
		</div>
	</div>
</form>

<script type="text/javascript">
	$("#canonical").on('change', toggleCanonical)
		.trigger('change');

	function toggleCanonical(event) {
		$(".useCanonical").toggle("videojs" === $("#canonical option:selected").val());
	}
</script>