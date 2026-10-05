<script src="{{home}}/plugins/zboard/upload/js/jquery-1.7.2.min.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="{{tpl_home}}/plugins/zboard/upload/uploadifive/uploadifive.css">
<link rel="stylesheet" href="{{tpl_home}}/plugins/zboard/tpl/config/capty/jquery.capty.css" type="text/css"/>
<script src="{{tpl_home}}/plugins/zboard/upload/uploadifive/jquery.uploadifive.min.js" type="text/javascript"></script>
<script type="text/javascript" src="{{tpl_home}}/plugins/zboard/tpl/config/capty/jquery.capty.min.js"></script>
{% if (error) %}
	<div class="feed-me">
		{{error}}
	</div>
{% endif %}
<script language="javascript" type="text/javascript">
	var currentInputAreaID = 'content_description';
</script>
<div class="comment">
	<h3>
		<span>{{ lang['zboard']['ui_edit_title'] }}</span>
	</h3>
	<form method="post" action="" class="comment-form" name="form" enctype="multipart/form-data">
		<ul class="comment-author">
			<li class="item clearfix">
				<input type="text" class="form-control" name="announce_name" value="{{announce_name}}" tabindex="1">
				<label>{{ lang['zboard']['ui_announcement_title'] }}
					<i>(*)</i>
				</label>
			</li>
			<li class="item clearfix">
				<input type="text" class="form-control" name="author" value="{{author}}" tabindex="1">
				<label>{{ lang['zboard']['ui_author'] }}
					<i>(*)</i>
				</label>
			</li>
			<li class="item clearfix">
				<select name="announce_period">
					{{list_period}}
				</select>
				<label>{{ lang['zboard']['ui_period'] }}
					<i>(*)</i>
				</label>
			</li>
			<li class="item clearfix">
				<select name="cat_id">
					{{options}}
				</select>
				<label>{{ lang['zboard']['ui_category'] }}
					<i>(*)</i>
				</label>
			</li>
		</ul>
		<span class="textarea">
			<label>{{ lang['zboard']['ui_description'] }}
				<i>(*)</i>
			</label><br/><br/>
			<textarea type="text" id="content_description" name="announce_description" tabindex="4">{{announce_description}}</textarea>
		</span>
		<span class="textarea">
			<label>{{ lang['zboard']['ui_contacts'] }}
				<i>({{ lang['zboard']['ui_phone'] }})</i>
			</label>
			<input type="text" class="form-control" id="announce_contacts" name="announce_contacts" value="{{announce_contacts}}"/>
		</span>
		<ul class="comment-author">
			<li class="item clearfix">
				<script type="text/javascript">
					$(document).ready(function () {
var count = 0;
$('#file_upload').uploadifive({
'auto': false,
'formData': {
'id': $("#txtdes").val()
},
'queueID': 'queue',
'uploadScript': '/engine/plugins/zboard/upload/libs/subirarchivo.php?id= {{ id }}',
'onUpload': function (filesToUpload) {
count = 0;
},
'onUploadComplete': function (file, data) {
// $('.uploadifive-queue-item').appendChild('<img src="../../../uploads/galerias/'+data+'" width=100 >');
// alert('../../../uploads/galerias/'+data);
// $('#uploadifive-file_upload-file-'+count).html('<img src="../../../uploads/galerias/'+data+'" width=100 >');
count++;
},
'onQueueComplete': function (uploads) {
// $("#txtdes").val();
// location.reload();
}
});
$('.fix').capty({cWrapper: 'capty-tile', height: 36, opacity: .6});
});
				</script>
				<label>{{ lang['zboard']['ui_attach_images'] }}</label><br/><br/>
				<input type="hidden" id="txtdes" name="txtdes" value="{{id}}"/>
				<div id="queue"></div>
				<input id="file_upload" name="file_upload" type="file" multiple="true">
			</li>
			<li class="item clearfix">
				<label>{{ lang['zboard']['ui_attached_images'] }}</label><br/><br/>
				<table>
					<tr>
						{% for entry in entriesImg %}
							<td style="padding-left:5px;">
								<a href='{{entry.home}}/uploads/zboard/{{entry.filepath}}' target='_blank'><img class="fix" name="#content-target-{{entry.pid}}" src='{{entry.home}}/uploads/zboard/thumb/{{entry.filepath}}' width='150' height='120'></a>
								<div id="content-target-{{entry.pid}}">
									<a href="{{entry.del}}">[x]</a>&nbsp;&nbsp;&nbsp;
								</div>
							</td>
						{% endfor %}
					</tr>
				</table>
			</li>
		</ul>
		<span class="submit">
			<button name="submit" type="submit" tabindex="5" onclick="javascript:$('#file_upload').uploadifive('upload')">{{ lang['zboard']['ui_submit'] }}</button>
		</span>
		<span class="submit">
			<button tabindex="5" type="reset">{{ lang['zboard']['ui_reset'] }}</button>
		</span>
	</form>
</div>
