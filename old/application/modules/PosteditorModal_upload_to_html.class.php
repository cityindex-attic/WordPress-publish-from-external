<?php
/**
 * @package post-editor
 */
/**
 * Handles uploads to the Post Editor plugin and converts them to html for use
 * in the editor.
 * 
 * Module strictly works by file types. To add a new file type for processing:
 * - add the file extension to $this->accepted_files in $this->construct. 
 * - Then create a private method to handle the data in the format:
 *    <code>parse_$filextension( $data )</code> This must return the formated
 *    data as html for use in the wordpress post editor.
 * - add any css to the file <code>POSTEDITOR_DIR /public_html/css/PosteditorModal_upload_to_html.css</code>
 * - don't forget to comment your method to phpDocumentator standard ;)
 *
 * @todo Issues
 * - md format doesn't highlight links or link to commits.
 * @author daithi
 * @see PosteditorModal_upload_to_html::accepted_files
 * @package post-editor
 */
class PosteditorModal_upload_to_html {

	/** @var array An array of accepted file extensions */
	private $accepted_files;
	/** @var string The current action (method to be called). 
	 * Defaults to false */
	private $action;
	/** @var string Holds the html from the view file for parsing */
	private $html;
	/** @var string The formated html table result */
	private $modal_result;
	/** @var array An array of shortcode=>value pairs for the view file */
	private $shortcodes;
	/** @var string The pasted excel content */
	private $textarea_content;
	
	/**
	 * constructor
	 */
	public function __construct() {
		
		//set default params
		$this->accepted_files = array(
			'md',
			'html'
		);
		(@$_REQUEST['posteditor_action'])	//the current action
			? $action = $_REQUEST['posteditor_action']
			: $action = false;
		$this->markdown = new Markdown_Parser();
		$this->modal_result = "";
		$this->shortcodes = array();
		(@$_REQUEST['data'])	//posted data
			? $this->textarea_content = $_REQUEST['data']
			: $this->textarea_content = "";
		
		//wp hooks
		add_action('wp_head', array(&$this, 'admin_head'));
		
		//look for actions
		if($action)
			if(method_exists($this, $action))
				$this->$action();
	}
	
	/**
	 * Adds global javascript vars to the admin head.
	 * 
	 * @deprecated
	 */
	public function admin_head(){
		
		//print global vars
		?>
		<script type="text/javascript">
			var posteditor_url = '<?=POSTEDITOR_URL?>';
			var theme_dir = '<?=bloginfo('template_directory')?>';
			var posteditor_modal_nonce = '<?php echo wp_create_nonce("post editor modal"); ?>';
			var posteditor_accepted_files = ['<?php echo implode("','", $this->accepted_files) ?>'];
		</script>
		<?php
	}
	
	/**
	 * Returns the html for the view file.
	 *
	 * @return string 
	 */
	public function get_page(){
		
		$this->html = file_get_contents( POSTEDITOR_DIR . "/public_html/PosteditorModal_upload_to_html.php");
		$this->shortcodes['accepted file types'] = "*." . implode(",*.", $this->accepted_files);
		$this->shortcodes['errors'] = posteditor_get_errors();
		$this->shortcodes['messages'] = posteditor_get_messages();
		$this->shortcodes['images dir'] = POSTEDITOR_URL . "/public_html/images";
		$this->shortcodes['modal result'] = $this->modal_result;
		$this->shortcodes['textarea content'] = $this->textarea_content;
		$this->shortcodes['upload file nonce'] = wp_create_nonce("upload file nonce");
		$this->set_shortcodes();
		
		return $this->html;
	}
	
	/**
	 * Loads javascript files
	 * 
	 * @return void 
	 */
	public function load_scripts() {
		wp_register_script('posteditormodal_upload_to_html-tinymce', POSTEDITOR_URL . "/application/includes/tinymce/jscripts/tiny_mce/jquery.tinymce.js");
		wp_register_script('posteditormodal_upload_to_html', POSTEDITOR_URL . "/public_html/js/PosteditorModal_upload_to_html.js", array(
			'jquery',
			'posteditormodal_upload_to_html-tinymce',
			'jquery-validate'
		));
		
		wp_enqueue_script('posteditormodal_upload_to_html');
	}

	/**
	 * Loads css files
	 * 
	 * @return void 
	 */
	public function load_styles() {
		wp_register_style("posteditormodal_upload_to_html", POSTEDITOR_URL . "/public_html/css/PosteditorModal_upload_to_html.css");
		wp_enqueue_style("posteditormodal_upload_to_html");
	}

	/**
	 * Parses html data for the editor.
	 * 
	 * Looks for <style> tags in the head, and adds them to the body. Sets
	 * $this->modal_result with the style tags and body content.
	 * 
	 * @param string $data The html data to parse.
	 * @return string
	 */
	private function parse_html( $data ){
		
		//load html to dom parser
		$simple = new simple_html_dom();
		$simple->load( $data );
		
		//vars
		$body = $simple->find('body',0);
		$css = array();
		$result = "";
		$styles = $simple->find('style');
		
		//if style tags, get css
		if(@count($styles))
			foreach($styles as $style)
				$css[] = $style->innertext;
		
		//build result
		if(count($style))
			$result .= "<style type=\"text/css\">
					" . implode("\n", $css) . "
					</style>
				";
		
		if($body){
			$result .= $body->innertext;
		}
		else
			$result .= $data;
		
		return $result;
	}
	
	/**
	 * Parses GitHub Markdown files. User Markdown_Parser.
	 * 
	 * Sets result in $this->modal_result
	 * 
	 * @link http://michelf.com/projects/php-markdown
	 * @return string
	 */
	private function parse_md( $data ){
		
		/** @var Markdown_Parser The markdown parser class */
		$parser = new Markdown_Parser();
		
		//return parsed result
		return "<div class=\"cis-markdown\">\n"
			. $parser->transform($data)
			. "</div>\n";
	}
	
	/**
	 * Sets values for the shortcodes in the view file.
	 * 
	 * Replaces the codes with values in @see PosteditorModal::$html . To add
	 * shortcodes to the view file use the syntax:
	 * <code> <!--[--identifying string--]--> </code>. In the construct of this
	 * class add the value to the array @see PosteditorModal::$shortcodes.
	 * eg: $this->shortcodes['identifying string'] = $this->method_returns_html()
	 * 
	 * @return void
	 */
	private function set_shortcodes() {
		foreach ($this->shortcodes as $code => $val)
			$this->html = str_replace("<!--[--{$code}--]-->", $val, $this->html);
	}

	/**
	 * Parses an uploaded file.
	 * 
	 * Result is stored in $this->modal_result
	 * 
	 * @global type $posteditor_error
	 * @return boolean 
	 */
	private function upload_file(){
		
		//security check
		if(!wp_verify_nonce($_REQUEST['_wpnonce'], "upload file nonce")){
			posteditor_error("Invalid nonce");
			return false;
		}
		
		//vars
		global $posteditor_error;
		$data = file_get_contents($_FILES['upload']['tmp_name']);
		$ext = get_file_extension($_FILES['upload']['name']);
		$parse_method = "parse_{$ext}";
		
		//Check for upload | filetype errors
		$errors = array();
		if($_FILES['upload']['error'])
			$posteditor_error[] = "Upload error: {$_FILES['upload']['error']}";
		if(!in_array( $ext, $this->accepted_files ))
			$posteditor_error[] = "Invalid file extension";
		if(!method_exists($this, $parse_method))
			$posteditor_error[] = "No parse method found. Please documentation for this class.";
		if(count($posteditor_error))
			return false;
		
		//call parse method
		$this->modal_result = $this->$parse_method( $data );
		return true;
	}
}

?>
