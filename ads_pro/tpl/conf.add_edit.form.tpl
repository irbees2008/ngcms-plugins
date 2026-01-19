 <script language="javascript" type="text/javascript">
	function AddBlok() {
	        var tbl = document.getElementById('blokup');
	        var lastRow = tbl.rows.length;
	        var iteration = lastRow + 1;
	        var row = tbl.insertRow(lastRow);
	        var cellRight = row.insertCell(0);
	        cellRight.innerHTML = iteration + ': ';
	        cellRight = row.insertCell(1);
	        cellRight.setAttribute('align', 'left');
	        var el = '<select class="form-select form-select-sm d-inline-block w-auto me-2" name="location[' + iteration + '][mode]" onchange="AddSubBlok(this, ' + iteration + ');"><option value=0>{{ lang['ads_pro:around']|e('js') }}</option><option value=1>{{ lang['ads_pro:main']|e('js') }}</option><option value=2>{{ lang['ads_pro:not_main']|e('js') }}</option><option value=3>{{ lang['ads_pro:category']|e('js') }}</option><option value=4>{{ lang['ads_pro:static']|e('js') }}</option>{% if support_news %}<option value=5>{{ lang['ads_pro:news']|e('js') }}</option>{% endif %}<option value=6>{{ lang['ads_pro:plugins']|e('js') }}</option></select>';
	        cellRight.innerHTML += el;
	        el = '<select class="form-select form-select-sm d-inline-block w-auto" name="location[' + iteration + '][view]"><option value=0>{{ lang['ads_pro:view']|e('js') }}</option><option value=1>{{ lang['ads_pro:not_view']|e('js') }}</option></select>';
	        cellRight.innerHTML += el;
	    }
	    function AddSubBlok(el, iteration) {
	        var subel = null;
	        var subsubel = null;
	        switch (el.value) {
	            case '3':
	                subel = createNamedElement('select', 'location[' + iteration + '][id]');
	                subel.className = 'form-select form-select-sm d-inline-block w-auto ms-2';
	                {{ category_list|raw }}
	                break;
	            case '4':
	                subel = createNamedElement('select', 'location[' + iteration + '][id]');
	                subel.className = 'form-select form-select-sm d-inline-block w-auto ms-2';
	                {{ static_list|raw }}
	                break;{% if support_news %}
	            case '5':
	                subel = createNamedElement('select', 'location[' + iteration + '][id]');
	                subel.className = 'form-select form-select-sm d-inline-block w-auto ms-2';
	                {{ news_list|raw }}
	                break;{% endif %}
	            case '6':
	                subel = createNamedElement('select', 'location[' + iteration + '][id]');
	                subel.className = 'form-select form-select-sm d-inline-block w-auto ms-2';
	                {{ plugins_list|raw }}
	                break;
	        }
	        if (el.nextSibling.name == 'location[' + iteration + '][id]')
	            el.parentNode.removeChild(el.nextSibling);
	        if (subel)
	            el.parentNode.insertBefore(subel, el.nextSibling);
	    }
	    function RemoveBlok() {
	        var tbl = document.getElementById('blokup');
	        var lastRow = tbl.rows.length;
	        if (lastRow > 0) {
	            tbl.deleteRow(lastRow - 1);
	        }
	    }
	    function createNamedElement(type, name) {
	        var element = null;
	        try {
	            element = document.createElement('<' + type + ' name="' + name + '">');
	        } catch (e) {
	        }
	        if (!element || element.nodeName != type.toUpperCase()) {
	            element = document.createElement(type);
	            element.setAttribute("name", name);
	        }
	        return element;
	    }
	</script>
<form method="post" action="?mod=extra-config&amp;plugin=ads_pro&amp;action={% if mode == 'add' %}add_submit{% else %}edit_submit{% endif %}">
	<input type="hidden" name="id" value="{% if mode == 'add' %}0{% else %}{{ id }}{% endif %}"/>
	<div class="card mb-4">
		<div class="card-body">
			<div class="mb-3 row">
				<label class="col-sm-3 col-form-label">{{ lang['ads_pro:name'] }}<br/><small class="text-muted">{{ lang['ads_pro:name_d'] }}</small>
				</label>
				<div class="col-sm-9">
					<input type="text" class="form-control" name="name" {% if mode == 'edit' %} value="{{ name }}" {% endif %}/>
					<div class="alert alert-info mt-2 mb-0 py-2">
						<small>
							<strong>{{ lang['ads_pro:twig_usage_hint'] }}</strong><br>
							•
							<code>&#123;&#123; name|raw &#125;&#125;</code>
							-
							{{ lang['ads_pro:twig_usage_text'] }}<br>
							•
							<code>&#123;&#123; block_123|raw &#125;&#125;</code>
							-
							{{ lang['ads_pro:twig_usage_numeric'] }}
						</small>
					</div>
				</div>
			</div>
			<div class="mb-3 row">
				<label class="col-sm-3 col-form-label">{{ lang['ads_pro:description'] }}<br/><small class="text-muted">{{ lang['ads_pro:description_d'] }}</small>
				</label>
				<div class="col-sm-9">
					<input type="text" class="form-control" name="description" {% if mode == 'edit' %} value="{{ description }}" {% endif %}/>
				</div>
			</div>
			<div class="mb-3 row">
				<label class="col-sm-3 col-form-label">{{ lang['ads_pro:type'] }}<br/><small class="text-muted">{{ lang['ads_pro:type_d'] }}</small>
				</label>
				<div class="col-sm-9">
					{{ type_list|raw }}
				</div>
			</div>
			<div class="mb-3 row">
				<label class="col-sm-3 col-form-label">{{ lang['ads_pro:location'] }}<br/><small class="text-muted">{{ lang['ads_pro:location_d'] }}</small>
				</label>
				<div class="col-sm-9">
					<button type="button" class="btn btn-outline-danger btn-sm me-2" onclick="RemoveBlok();return false;">{{ lang['ads_pro:location_dell'] }}</button>
					<button type="button" class="btn btn-outline-primary btn-sm" onclick="AddBlok();return false;">{{ lang['ads_pro:location_add'] }}</button>
					<div class="mt-2">
						<table id="blokup" class="table table-sm table-borderless">
							{% if mode == 'edit' %}
								{{ location_list|raw }}
							{% endif %}
						</table>
					</div>
				</div>
			</div>
			<div class="mb-3 row">
				<label class="col-sm-3 col-form-label">{{ lang['ads_pro:state'] }}<br/><small class="text-muted">{{ lang['ads_pro:state_d'] }}</small>
				</label>
				<div class="col-sm-9">
					{{ state_list|raw }}
				</div>
			</div>
		</div>
	</div>
	<div class="card mb-4">
		<div class="card-header">
			<b>{{ lang['ads_pro:sched_legend'] }}</b>
		</div>
		<div class="card-body">
			<div class="mb-3 row">
				<label class="col-sm-3 col-form-label">{{ lang['ads_pro:start_view'] }}<br/><small class="text-muted">{{ lang['ads_pro:start_view_d'] }}</small>
				</label>
				<div class="col-sm-9">
					<input type="text" class="form-control" name="start_view" {% if mode == 'edit' %} value="{{ start_view }}" {% endif %}/>
				</div>
			</div>
			<div class="mb-3 row">
				<label class="col-sm-3 col-form-label">{{ lang['ads_pro:end_view'] }}<br/><small class="text-muted">{{ lang['ads_pro:end_view_d'] }}</small>
				</label>
				<div class="col-sm-9">
					<input type="text" class="form-control" name="end_view" {% if mode == 'edit' %} value="{{ end_view }}" {% endif %}/>
				</div>
			</div>
		</div>
	</div>
	<div class="card mb-4">
		<div class="card-header">
			<b>{{ lang['ads_pro:ads_blok_legend'] }}</b>
		</div>
		<div class="card-body">
			<p class="text-muted mb-3">{{ lang['ads_pro:ads_blok_info'] }}</p>
			<div class="alert alert-info mb-3">
				<h6 class="alert-heading">
					<i class="fa fa-info-circle"></i>
					{{ lang['ads_pro:examples_header'] }}</h6>
				<hr>
				<div class="mb-3">
					<strong>1.
						{{ lang['ads_pro:example_html_title'] }}</strong>
					<pre class="bg-light p-2 mt-1 mb-0"><code>{{ lang['ads_pro:example_html_code']|e }}</code></pre>
				</div>
				<div class="mb-3">
					<strong>2.
						{{ lang['ads_pro:example_php_title'] }}</strong>
					<pre class="bg-light p-2 mt-1 mb-0"><code>{{ lang['ads_pro:example_php_code']|e }}</code></pre>
				</div>
				<div class="mb-3">
					<strong>3.
						{{ lang['ads_pro:example_text_title'] }}</strong>
					<pre class="bg-light p-2 mt-1 mb-0"><code>{{ lang['ads_pro:example_text_code']|e }}</code></pre>
				</div>
			</div>
			<textarea class="form-control font-monospace" name="ads_blok" rows="30">
				{% if mode == 'edit' %}
					{{ ads_blok }}
				{% endif %}
			</textarea>
		</div>
	</div>
	<div class="text-center mb-4">
		<button type="submit" class="btn btn-success">
			{% if mode == 'add' %}
				{{ lang['ads_pro:add_submit'] }}
			{% else %}
				{{ lang['ads_pro:edit_submit'] }}
			{% endif %}
		</button>
	</div>
</form>
