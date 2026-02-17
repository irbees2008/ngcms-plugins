<div class="weather-block">
	<div class="weather-header">
		<h3>Погода в
			{{ weather.city }}</h3>
	</div>
	<div class="weather-content">
		<img src="{{ weather.icon }}" alt="{{ weather.description }}" class="weather-icon"/>
		<div class="weather-temp">{{ weather.temp }}{{ weather.units }}</div>
		<div class="weather-desc">{{ weather.description }}</div>
		<div class="weather-details">
			<span>Влажность:
				{{ weather.humidity }}%</span>
			<span>Ветер:
				{{ weather.wind }}
				м/с</span>
		</div>
	</div>
</div>

<style>
	.weather-block {
		border: 1px solid #e0e0e0;
		border-radius: 5px;
		padding: 15px;
		background: #f9f9f9;
		max-width: 250px;
	}
	.weather-header h3 {
		margin: 0 0 10px;
		font-size: 18px;
		color: #333;
	}
	.weather-content {
		display: flex;
		flex-direction: column;
		align-items: center;
	}
	.weather-icon {
		width: 50px;
		height: 50px;
	}
	.weather-temp {
		font-size: 24px;
		font-weight: bold;
		margin: 5px 0;
	}
	.weather-desc {
		text-transform: capitalize;
		margin-bottom: 10px;
	}
	.weather-details {
		width: 100%;
		display: flex;
		justify-content: space-between;
		font-size: 13px;
		color: #666;
	}
</style>
