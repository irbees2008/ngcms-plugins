{#
  Шаблон статистики рассылок
  Использование в шаблоне: {{ callPlugin("mailing.stats") }}
#}
<div class="mailing-stats">
	<h3>Статистика рассылок</h3>
	<div class="stats-grid">
		<div class="stat-item">
			<span class="stat-label">Всего кампаний:</span>
			<span class="stat-value">{{ total_campaigns }}</span>
		</div>
		<div class="stat-item">
			<span class="stat-label">Отправлено писем:</span>
			<span class="stat-value">{{ total_sent }}</span>
		</div>
		<div class="stat-item">
			<span class="stat-label">В очереди:</span>
			<span class="stat-value">{{ total_pending }}</span>
		</div>
	</div>
</div>

<style>
	.mailing-stats {
		padding: 20px;
		background: #f9f9f9;
		border-radius: 8px;
		margin: 20px 0;
	}

	.mailing-stats h3 {
		margin: 0 0 15px;
		font-size: 18px;
		color: #333;
	}

	.stats-grid {
		display: grid;
		grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
		gap: 15px;
	}

	.stat-item {
		display: flex;
		flex-direction: column;
		padding: 15px;
		background: white;
		border-radius: 6px;
		box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
	}

	.stat-label {
		font-size: 14px;
		color: #666;
		margin-bottom: 5px;
	}

	.stat-value {
		font-size: 24px;
		font-weight: bold;
		color: #2196F3;
	}
</style>
