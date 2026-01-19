{#
  Форма подписки/отписки от рассылки
  Использование: {{ callPlugin("mailing.form") }}
#}
<div class="mailing-subscription-form">
	{% if is_logged %}
		{% if is_subscribed %}
			<div class="subscription-status subscribed">
				<i class="fa fa-check-circle"></i>
				<p>Вы подписаны на email-рассылку</p>
				<form method="post" action="{{ action_url }}">
					<input type="hidden" name="action" value="unsubscribe">
					<button type="submit" class="btn btn-secondary">Отписаться</button>
				</form>
			</div>
		{% else %}
			<div class="subscription-status unsubscribed">
				<i class="fa fa-envelope-o"></i>
				<p>Вы отписаны от email-рассылки</p>
				<form method="post" action="{{ action_url }}">
					<input type="hidden" name="action" value="subscribe">
					<button type="submit" class="btn btn-primary">Подписаться</button>
				</form>
			</div>
		{% endif %}
	{% else %}
		<div class="subscription-status guest">
			<i class="fa fa-info-circle"></i>
			<p>Войдите на сайт, чтобы управлять подпиской на рассылку</p>
		</div>
	{% endif %}
</div>

<style>
	.mailing-subscription-form {
		padding: 20px;
		margin: 20px 0;
	}

	.subscription-status {
		padding: 20px;
		border-radius: 8px;
		text-align: center;
	}

	.subscription-status i {
		font-size: 48px;
		margin-bottom: 15px;
		display: block;
	}

	.subscription-status.subscribed {
		background: #e8f5e9;
		color: #2e7d32;
	}

	.subscription-status.subscribed i {
		color: #4caf50;
	}

	.subscription-status.unsubscribed {
		background: #fff3e0;
		color: #e65100;
	}

	.subscription-status.unsubscribed i {
		color: #ff9800;
	}

	.subscription-status.guest {
		background: #e3f2fd;
		color: #1565c0;
	}

	.subscription-status.guest i {
		color: #2196F3;
	}

	.subscription-status p {
		margin: 10px 0 15px;
		font-size: 16px;
	}

	.subscription-status form {
		margin-top: 15px;
	}

	.subscription-status .btn {
		padding: 10px 30px;
		border: none;
		border-radius: 4px;
		font-size: 14px;
		cursor: pointer;
		transition: all 0.3s;
	}

	.subscription-status .btn-primary {
		background: #2196F3;
		color: white;
	}

	.subscription-status .btn-primary:hover {
		background: #1976D2;
	}

	.subscription-status .btn-secondary {
		background: #757575;
		color: white;
	}

	.subscription-status .btn-secondary:hover {
		background: #616161;
	}
</style>
