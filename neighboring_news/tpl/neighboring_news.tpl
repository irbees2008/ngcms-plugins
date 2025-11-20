<ul class="neighboring-news">
	{{ next_news|raw }}{{ previous_news|raw }}
</ul>
<style>
	.neighboring-news {
		list-style: none;
		padding-left: 0;
		margin: 8px 10px;
		font: italic 600 12px / 1.3 Arial, Helvetica, sans-serif;
		border: 1px solid #eee;
		border-left: 0;
		border-right: 0
	}
	.neighboring-news li {
		display: flex;
		justify-content: space-between;
		gap: 8px
	}
	.neighboring-news a {
		text-decoration: none;
		color: #0a58ca
	}
	.neighboring-news a:hover {
		text-decoration: underline
	}
</style>
