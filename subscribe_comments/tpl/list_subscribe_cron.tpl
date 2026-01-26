<div class="card mb-3">
	<div class="card-body">
		<h5 class="card-title">События cron</h5>
		<div class="table-responsive">
			<table class="table table-striped">
				<thead>
					<tr>
						<th style="width: 25%">Время</th>
						<th style="width: 75%">Событие</th>
					</tr>
				</thead>
				<tbody>
					{{ entries_cron|raw }}
				</tbody>
			</table>
		</div>
	</div>
</div>
