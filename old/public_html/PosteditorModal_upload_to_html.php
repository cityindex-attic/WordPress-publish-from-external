<!--[--errors--]-->
<!--[--messages--]-->

<form method="post" enctype="multipart/form-data" id="upload-form">
	<input type="hidden" name="posteditor_action" value="upload_file"/>
	<input type="hidden" name="_wpnonce" value="<!--[--upload file nonce--]-->"/>
	<p>
		The following file types are accepted:<br/>
		<!--[--accepted file types--]-->
	</p>
	<div id="upload-error"></div>
	<input type="file" name="upload" class="required" id="upload"/>
	<input type="submit" value="Upload File"/>
</form>


<textarea id="results" name="results"><!--[--modal result--]--></textarea>

<input type="button" value="Insert in to Editor" class="button-primary" onclick="posteditor_upload_to_html.insert_to_editor()"/>
<input type="button" value="Reset Form" class="button-primary" onclick="posteditor_upload_to_html.reset_form()"/>
<input type="button" value="Cancel Edit" class="button-primary" onclick="window.parent.tb_remove()"/>