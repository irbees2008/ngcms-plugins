<div class="helloworld-wrapper">
	<h1>{{ title }}</h1>
	<p>{{ body }}</p>
	<p>
		<small>Просмотров страницы:
			{{ hits }}</small>
	</p>
</div>
<style>
	.helloworld-wrapper {
		max-width: 600px;
		margin: 30px auto;
		font-family: Arial, sans-serif;
	}
	.helloworld-wrapper h1 {
		margin: 0 0 10px;
		font-size: 28px;
	}
	.helloworld-wrapper p {
		line-height: 1.5;
	}
</style>
