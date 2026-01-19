{# Web Push subscription button template #}
<div class="webpush-widget" id="webpush-widget">
	<button type="button" id="webpush-subscribe-btn" class="webpush-btn webpush-btn-subscribe" data-endpoint="{{ endpoint }}" data-subscribe-text="{{ subscribe_text }}" data-unsubscribe-text="{{ unsubscribe_text }}" data-public-key="{{ public_key }}">
		<svg class="webpush-icon" width="20" height="20" viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
			<path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
			<path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
		</svg>
		<span class="webpush-btn-text">{{ subscribe_text }}</span>
	</button>

	<div class="webpush-message" id="webpush-message" style="display: none;"></div>
</div>

 <script src="{{ js_path }}" defer></script>

<style>
	.webpush-widget {
		position: fixed;
		right: 20px;
		bottom: 20px;
		z-index: 9999;
		font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
	}

	.webpush-btn {
		display: flex;
		align-items: center;
		gap: 8px;
		padding: 12px 20px;
		border: none;
		border-radius: 8px;
		background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
		color: #ffffff;
		font-size: 14px;
		font-weight: 500;
		cursor: pointer;
		box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
		transition: all 0.3s ease;
	}

	.webpush-btn:hover {
		transform: translateY(-2px);
		box-shadow: 0 6px 16px rgba(102, 126, 234, 0.5);
	}

	.webpush-btn:active {
		transform: translateY(0);
	}

	.webpush-btn.webpush-btn-subscribed {
		background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
		box-shadow: 0 4px 12px rgba(67, 233, 123, 0.4);
	}

	.webpush-btn.webpush-btn-subscribed:hover {
		box-shadow: 0 6px 16px rgba(67, 233, 123, 0.5);
	}

	.webpush-icon {
		flex-shrink: 0;
	}

	.webpush-btn-text {
		white-space: nowrap;
	}

	.webpush-message {
		margin-top: 10px;
		padding: 10px 15px;
		background: #ffffff;
		border-radius: 6px;
		box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
		font-size: 13px;
		color: #333333;
	}

	.webpush-message.webpush-error {
		background: #fee;
		color: #c00;
	}

	.webpush-message.webpush-success {
		background: #efe;
		color: #060;
	}

	@media(max-width: 768px) {
		.webpush-widget {
			right: 10px;
			bottom: 10px;
		}

		.webpush-btn {
			padding: 10px 16px;
			font-size: 13px;
		}
	}

	/* Анимация появления */
	@keyframes webpushSlideIn {
		from {
			transform: translateY(100px);
			opacity: 0;
		}
		to {
			transform: translateY(0);
			opacity: 1;
		}
	}

	.webpush-widget {
		animation: webpushSlideIn 0.5s ease-out;
	}
</style>
