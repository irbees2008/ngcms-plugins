<style>.nt-textarea-holder{position:relative}</style>
<div class="alert alert-info">{{ lang.alert_info }}</div>

{% for entry in entries %}
	<fieldset class="border rounded p-2 mb-3">
		<legend class="w-auto px-2">{{ lang.legend }}{{ entry.ord }}</legend>
		<div class="form-row">
			<div class="form-group col-md-8">
				<label for="nt_title_{{ entry.ord }}">{{ lang.field_title }}</label>
				<input type="text" class="form-control" name="nt_title_{{ entry.ord }}" id="nt_title_{{ entry.ord }}" value="{{ entry.title|e }}" />
			</div>
			<div class="form-group col-md-4">
				<div class="form-check mt-4">
					<input class="form-check-input" type="checkbox" name="nt_active_{{ entry.ord }}" id="nt_active_{{ entry.ord }}" value="1"{% if entry.active %} checked{% endif %} />
					<label class="form-check-label" for="nt_active_{{ entry.ord }}">{{ lang.field_active }}</label>
				</div>
			</div>
		</div>
		<div class="btn-toolbar mb-2" role="toolbar">
			<div class="btn-group btn-group-sm mr-2">
				<button type="button" class="btn btn-outline-dark" title="{{ lang.btn_paragraph|e }}" onclick="insertext('[p]', '[/p]', 'nt_content_{{ entry.ord }}')"><i class="fa fa-paragraph"></i></button>
			</div>
			<div class="btn-group btn-group-sm mr-2">
				<button id="tags-font-{{ entry.ord }}" type="button" class="btn btn-outline-dark dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-font"></i></button>
				<div class="dropdown-menu" aria-labelledby="tags-font-{{ entry.ord }}">
					<a href="#" class="dropdown-item" onclick="insertext('[b]', '[/b]', 'nt_content_{{ entry.ord }}'); return false;"><i class="fa fa-bold"></i> {{ lang.btn_bold }}</a>
					<a href="#" class="dropdown-item" onclick="insertext('[i]', '[/i]', 'nt_content_{{ entry.ord }}'); return false;"><i class="fa fa-italic"></i> {{ lang.btn_italic }}</a>
					<a href="#" class="dropdown-item" onclick="insertext('[u]', '[/u]', 'nt_content_{{ entry.ord }}'); return false;"><i class="fa fa-underline"></i> {{ lang.btn_underline }}</a>
					<a href="#" class="dropdown-item" onclick="insertext('[s]', '[/s]', 'nt_content_{{ entry.ord }}'); return false;"><i class="fa fa-strikethrough"></i> {{ lang.btn_strike }}</a>
				</div>
			</div>
			<div class="btn-group btn-group-sm mr-2">
				<button id="tags-align-{{ entry.ord }}" type="button" class="btn btn-outline-dark dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-align-left"></i></button>
				<div class="dropdown-menu" aria-labelledby="tags-align-{{ entry.ord }}">
					<a href="#" class="dropdown-item" onclick="insertext('[left]', '[/left]', 'nt_content_{{ entry.ord }}'); return false;"><i class="fa fa-align-left"></i> {{ lang.align_left }}</a>
					<a href="#" class="dropdown-item" onclick="insertext('[center]', '[/center]', 'nt_content_{{ entry.ord }}'); return false;"><i class="fa fa-align-center"></i> {{ lang.align_center }}</a>
					<a href="#" class="dropdown-item" onclick="insertext('[right]', '[/right]', 'nt_content_{{ entry.ord }}'); return false;"><i class="fa fa-align-right"></i> {{ lang.align_right }}</a>
					<a href="#" class="dropdown-item" onclick="insertext('[justify]', '[/justify]', 'nt_content_{{ entry.ord }}'); return false;"><i class="fa fa-align-justify"></i> {{ lang.align_justify }}</a>
				</div>
			</div>
			<div class="btn-group btn-group-sm mr-2">
				<button id="tags-block-{{ entry.ord }}" type="button" class="btn btn-outline-dark dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-quote-left"></i></button>
				<div class="dropdown-menu" aria-labelledby="tags-block-{{ entry.ord }}">
					<a href="#" class="dropdown-item" onclick="insertext('[ul]\n[li][/li]\n[li][/li]\n[li][/li]\n[/ul]', '', 'nt_content_{{ entry.ord }}'); return false;"><i class="fa fa-list-ul"></i> {{ lang.list_ul }}</a>
					<a href="#" class="dropdown-item" onclick="insertext('[ol]\n[li][/li]\n[li][/li]\n[li][/li]\n[/ol]', '', 'nt_content_{{ entry.ord }}'); return false;"><i class="fa fa-list-ol"></i> {{ lang.list_ol }}</a>
					<div class="dropdown-divider"></div>
					<a href="#" class="dropdown-item" onclick="insertext('[code]', '[/code]', 'nt_content_{{ entry.ord }}'); return false;"><i class="fa fa-code"></i> {{ lang.btn_code }}</a>
					<a href="#" class="dropdown-item" onclick="insertext('[quote]', '[/quote]', 'nt_content_{{ entry.ord }}'); return false;"><i class="fa fa-quote-left"></i> {{ lang.btn_quote }}</a>
					<a href="#" class="dropdown-item" onclick="insertext('[spoiler]', '[/spoiler]', 'nt_content_{{ entry.ord }}'); return false;"><i class="fa fa-list-alt"></i> {{ lang.btn_spoiler }}</a>
					<a href="#" class="dropdown-item" onclick="insertext('[acronym=]', '[/acronym]', 'nt_content_{{ entry.ord }}'); return false;"><i class="fa fa-tags"></i> {{ lang.btn_acronym }}</a>
					<a href="#" class="dropdown-item" onclick="insertext('[hide]', '[/hide]', 'nt_content_{{ entry.ord }}'); return false;"><i class="fa fa-shield"></i> {{ lang.btn_hide }}</a>
				</div>
			</div>
			<div class="btn-group btn-group-sm mr-2">
				<button id="tags-link-{{ entry.ord }}" type="button" class="btn btn-outline-dark dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-link"></i></button>
				<div class="dropdown-menu" aria-labelledby="tags-link-{{ entry.ord }}">
					<a href="#" class="dropdown-item" data-toggle="modal" data-target="#modal-insert-url" onclick="prepareUrlModal('nt_content_{{ entry.ord }}'); showModalById('modal-insert-url'); return false;"><i class="fa fa-link"></i> {{ lang.btn_url }}</a>
					<a href="#" class="dropdown-item" data-toggle="modal" data-target="#modal-insert-email" onclick="prepareEmailModal('nt_content_{{ entry.ord }}'); showModalById('modal-insert-email'); return false;"><i class="fa fa-envelope-o"></i> {{ lang.btn_email }}</a>
					<a href="#" class="dropdown-item" data-toggle="modal" data-target="#modal-insert-image" onclick="prepareImgModal('nt_content_{{ entry.ord }}'); showModalById('modal-insert-image'); return false;"><i class="fa fa-file-image-o"></i> {{ lang.btn_image }}</a>
				</div>
			</div>
			<div class="btn-group btn-group-sm mr-2">
				<button id="tags-media-{{ entry.ord }}" type="button" class="btn btn-outline-dark" data-toggle="modal" data-target="#modal-insert-media" onclick="prepareMediaModal('nt_content_{{ entry.ord }}'); showModalById('modal-insert-media'); return false;" title="[media]"><i class="fa fa-play-circle"></i></button>
			</div>
		</div>
		<div class="form-group nt-textarea-holder position-relative">
			<label for="nt_content_{{ entry.ord }}">{{ lang.field_content }}</label>
			<textarea rows="4" class="form-control" name="nt_content_{{ entry.ord }}" id="nt_content_{{ entry.ord }}">{{ entry.content|e }}</textarea>
		</div>
	</fieldset>
{% endfor %}

<script src="/lib/news_editor.js"></script>
<div class="modal fade" id="modal-insert-url" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">{{ lang.modal_url_title }}</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="{{ lang.modal_close|e }}"><span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body">
				<input type="hidden" id="urlAreaId" value="" />
				<div class="form-group"><label>{{ lang.modal_url_url_label }}</label><input type="text" class="form-control" id="urlHref" placeholder="https://example.com" /></div>
				<div class="form-group"><label>{{ lang.modal_url_text_label }}</label><input type="text" class="form-control" id="urlText" placeholder="{{ lang.modal_url_text_placeholder|e }}" /></div>
				<div class="form-row">
					<div class="form-group col-md-6"><label>{{ lang.modal_url_target_label }}</label><select id="urlTarget" class="form-control"><option value="">{{ lang.modal_url_target_default }}</option><option value="_blank">{{ lang.modal_url_target_blank }}</option></select></div>
					<div class="form-group col-md-6"><div class="form-check mt-4"><input class="form-check-input" type="checkbox" id="urlNofollow" /> <label class="form-check-label" for="urlNofollow">nofollow</label></div></div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">{{ lang.cancel }}</button>
				<button type="button" class="btn btn-primary" onclick="insertUrlFromModal(); return false;">{{ lang.insert }}</button>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="modal-insert-image" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">{{ lang.modal_image_title }}</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="{{ lang.modal_close|e }}"><span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body">
				<input type="hidden" id="imgAreaId" value="" />
				<div class="form-group"><label>{{ lang.modal_image_url_label }}</label><input type="text" class="form-control" id="imgHref" placeholder="https://.../image.jpg" /></div>
				<div class="form-row">
					<div class="form-group col-md-6"><label>{{ lang.modal_image_alt_label }}</label><input type="text" class="form-control" id="imgAlt" /></div>
					<div class="form-group col-md-3"><label>{{ lang.modal_image_width_label }}</label><input type="text" class="form-control" id="imgWidth" /></div>
					<div class="form-group col-md-3"><label>{{ lang.modal_image_height_label }}</label><input type="text" class="form-control" id="imgHeight" /></div>
				</div>
				<div class="form-group"><label>{{ lang.modal_image_align_label }}</label><select id="imgAlign" class="form-control"><option value="">{{ lang.modal_image_align_none }}</option><option value="left">{{ lang.modal_image_align_left }}</option><option value="right">{{ lang.modal_image_align_right }}</option><option value="center">{{ lang.modal_image_align_center }}</option></select></div>
				<div class="form-group">
					<label>{{ lang.modal_image_upload_label }}</label>
					<div class="input-group">
						<input type="file" class="form-control" id="uploadimage" />
						<div class="input-group-append">
							<button type="button" class="btn btn-outline-primary" onclick="uploadNewsImage(document.getElementById('imgAreaId').value); return false;">{{ lang.modal_image_upload_btn }}</button>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">{{ lang.cancel }}</button>
				<button type="button" class="btn btn-primary" onclick="insertImgFromModal(); return false;">{{ lang.insert }}</button>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="modal-insert-email" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">{{ lang.modal_email_title }}</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="{{ lang.modal_close|e }}"><span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body">
				<input type="hidden" id="emailAreaId" value="" />
				<div class="form-group"><label>{{ lang.modal_email_address_label }}</label><input type="text" class="form-control" id="emailAddress" placeholder="user@example.com" /></div>
				<div class="form-group"><label>{{ lang.modal_email_text_label }}</label><input type="text" class="form-control" id="emailText" placeholder="{{ lang.modal_email_text_placeholder|e }}" /></div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">{{ lang.cancel }}</button>
				<button type="button" class="btn btn-primary" onclick="insertEmailFromModal(); return false;">{{ lang.insert }}</button>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="modal-insert-media" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">{{ lang.modal_media_title }}</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="{{ lang.modal_close|e }}"><span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body">
				<input type="hidden" id="mediaAreaId" value="" />
				<div class="form-group"><label>{{ lang.modal_media_url_label }}</label><input type="text" class="form-control" id="mediaHref" placeholder="https://example.com/embed.mp4" /></div>
				<div class="form-row">
					<div class="form-group col-md-4"><label>{{ lang.modal_media_width_label }}</label><input type="number" min="0" class="form-control" id="mediaWidth" /></div>
					<div class="form-group col-md-4"><label>{{ lang.modal_media_height_label }}</label><input type="number" min="0" class="form-control" id="mediaHeight" /></div>
					<div class="form-group col-md-4"><label>{{ lang.modal_media_preview_label }}</label><input type="text" class="form-control" id="mediaPreview" placeholder="{{ lang.modal_media_preview_placeholder|e }}" /></div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">{{ lang.cancel }}</button>
				<button type="button" class="btn btn-primary" onclick="insertMediaFromModal(); return false;">{{ lang.insert }}</button>
			</div>
		</div>
	</div>
</div>
