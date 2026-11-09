 <script type="text/javascript">
$(document).ready(function() {
	function rating(rating, post_id) {
		$.ajax({
			url: '{ajax_url}',
			method: "POST",
			dataType: "json",
			data: {
				rating: rating,
				post_id: post_id
			},
			success: function(response) {
				if (response.status === 'success') {
					$('#ratingdiv_' + post_id).html(response.html);
					if (response.notify && typeof showToast !== 'undefined') {
						showToast(response.notify.message, { type: response.notify.type });
					}
				} else if (response.status === 'error') {
					if (response.notify && typeof showToast !== 'undefined') {
						showToast(response.notify.message, { type: response.notify.type });
					}
				}
			},
			error: function() {
				if (typeof showToast !== 'undefined') {
					showToast('Ошибка при отправке оценки', { type: 'error' });
				}
			}
		});
	}
	// Привязываем обработчики к ссылкам
	$(document).on('click', '.rating a[onclick]', function(e) {
		e.preventDefault();
		var onclickAttr = $(this).attr('onclick');
		var matches = onclickAttr.match(/rating\('(\d+)',\s*'(\d+)'\)/);
		if (matches) {
			rating(matches[1], matches[2]);
		}
	});
});
</script>

<div id="ratingdiv_{post_id}">
	<div class="rating" data-post-id="{post_id}" style="float:left;">
		<ul class="uRating">
			<li class="r{rating}">{rating}</li>
			<li>
				<a href="#" title="1" class="r1u" data-rating="1" onclick="rating('1', '{post_id}'); return false;">1</a>
			</li>
			<li>
				<a href="#" title="2" class="r2u" data-rating="2" onclick="rating('2', '{post_id}'); return false;">2</a>
			</li>
			<li>
				<a href="#" title="3" class="r3u" data-rating="3" onclick="rating('3', '{post_id}'); return false;">3</a>
			</li>
			<li>
				<a href="#" title="4" class="r4u" data-rating="4" onclick="rating('4', '{post_id}'); return false;">4</a>
			</li>
			<li>
				<a href="#" title="5" class="r5u" data-rating="5" onclick="rating('5', '{post_id}'); return false;">5</a>
			</li>
		</ul>
	</div>
	<div class="rating" style="float:left; padding-top:2px;">&nbsp;({l_rating_votes} {votes})</div>
</div>
