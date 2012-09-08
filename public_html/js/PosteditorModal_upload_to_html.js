var posteditor_upload_to_html;

jQuery(document).ready(function($){
	
	posteditor_upload_to_html = new PosteditorUploadToHTML($);
	posteditor_upload_to_html.init();

});

var PosteditorUploadToHTML = function($){
	
	this.init = function(){
		$('#upload-form').validate( this.options_validate() );
		$('#results').tinymce( this.options_tinymce() );
	}
	
	/**
	 * Adds the formated html content to the main post editor.
	 *
	 * @method
	 * @public
	 * @member PostedirotModalETT
	 */
	this.insert_to_editor = function(){
		
		var content = '';
							
		//if tinymce is not loaded then get content from textarea - 		if(!jQuery("#posteditor-modal")[0].contentWindow.tinyMCE)
		if(!tinyMCE)
			content = jQuery('textarea[name=data]').val();
		//else get tinymce content
		else
			content = tinyMCE.activeEditor.getContent();
		
		//set post editor data and close
		window.parent.tinyMCE.execCommand('mceInsertContent', false, content);
		window.parent.tb_remove();
		return;
	}
	
	this.options_tinymce = function(){
		return {
			// Location of TinyMCE script
			script_url : posteditor_url + '/application/includes/tinymce/jscripts/tiny_mce/tiny_mce.js',
			// General options
			theme : "advanced",
			plugins : "table,autolink,paste",
			theme_advanced_buttons1 : "cut,copy,paste,|,link,unlink,anchor,|,tablecontrols",
			//theme_advanced_buttons2 : "",
			//width: 568,
			theme_advanced_resizing : true,
			theme_advanced_resize_horizontal : false,
			// Example content CSS (should be your site CSS)
			content_css : theme_dir + '/editor-style.css'
		}
	}
	
	this.options_validate = function(){
		return {
			submitHandler : function( form ){
				var filePath = form.upload.value;
				var ext = filePath.substring(filePath.lastIndexOf('.') + 1).toLowerCase();
				var allowed = false;
				for(var x=0; x<posteditor_accepted_files.length; x++){
					if(ext==posteditor_accepted_files[x]){
						allowed=true;
						break;
					}
				}
				if(!allowed){
					$('#upload-error').append('Only the filetype(s) *.' + posteditor_accepted_files.join(',*.') + ' are allowed');
					return false;
				}
				form.submit();
			}
		}
	}	
	
	/**
	 * Resets the modal form.
	 * 
	 * @method
	 * @publicS
	 * @member PosteditorModalETT
	 */
	this.reset_form = function(){
		tinyMCE.activeEditor.setContent('');
		jQuery('textarea[name=data]').html('');
	}
}