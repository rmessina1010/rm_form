<style>
 	form div, form p, form h2, form h3{
		padding: .5em 1em;
		margin:0;
	}
	label { 
		font-weight: bold;
	}
	form { 
		display: inline-block;
		padding: .5em;
		border: 1px solid;
	}
	.error{
		display: block;
		padding-bottom: .15em;
		color: red;
	}
	.error + br {
		display: none;
	}
</style><?
	/**
	* HTML/PHP form
	*/
	class RM_form {
		protected $attr_keys	= ["char"=>"accept-charset", "act"=>"action", "auto"=>"autocomplete", "class"=>"class","enc"=>"enctype", "id"=>"id", 										"mtd"=>"method", "name"=>"name", "noval"=> "novalidate", "tgt"=>"target", "style"=>"style"];
		protected $attrs		= array();
		protected $pg_ct 		= 0;
		protected $on_pg 		= 0;
		protected $is_valid 	= false;
		protected $method 		= '';
		protected $action 		= '';
		protected $pages 		= 'default';
		protected $form_data 	= array();
		protected $is_sub		= false;
		protected $is_nav		= false;
		protected $navs			= array();
		protected $is_processed	= false;
		protected $sub 			= '';
		protected $errs 		= array();
		protected $do_post		= true;
		protected $kp_inv_data	= false;
		protected $methodVars	= array();
		protected $sub_html		= '';
		
		
		function __construct($sub, $args = array()){
			foreach($args  as $possible_attr_key => $att_val){
				if (isset($this->attr_keys[$possible_attr_key])){ 
					$this->attrs[$this->attr_keys[$possible_attr_key]] = $att_val; 
				}
				elseif (substr($possible_attr, 0, 2) === "on" ||  substr($possible_attr, 0, 5) === "aria-" || substr($possible_attr, 0, 5) === "data-"){ 
					$this->attrs[$attr_name] = $args[$attr_key]; 
				}
			}
			$this->method = isset($args['mtd']) ? strtoupper($args['mtd']) : 'GET';
			$this->sub =$sub;
			if (isset($arg['navs']) && is_array($arg['navs'])){ $this->navs = $arg['navs'];}
			$this->pages =  is_array($this->pages) ? is_array($this->pages) : [$this->pages];
			$this->pg_ct =count($this->pages);
			$this->checksub('xx');
 		}

		protected function setErr($field, $message, $delim=' '){
			$this->errs[$field] =  isset($this->errs[$field]) ?  $this->errs[$field].$delim.$message."\n": $message;
			if (!$this->kp_inv_data){ $this->methodVars[$field] = '';}
		}

		function form_body_preprocess($html){
			if (method_exists($this, $html)) { return $this->{$html}();}
			if (isset($this->{$html}) && is_string($this->{$html})) {  return $this->{$html}; }
			return  $html;
		}
		
		function form_body(){
			if (is_string($this->pages)) { return $this->form_body_preprocess($this->pages);}
			if (is_array($this->pages) &&  $this->on_pg > -1 && $this->on_pg < $this->pg_ct) { return $this->form_body_preprocess($this->pages[$this->on_pg]);}
		}

		function form_attrs(){
			$attrs ='';
			foreach ($this->attrs as $attr_key=> $attr_val){ $attrs.=" $attr_key=\"".$attr_val.'"'; }
			return $attrs;
		}
		protected function run_validate(){
			$this->errs = array();
			$this->validate();
			$this->is_valid = (count($this->errs) < 1);
			return $this->is_valid;
		}
		
		function validate(){
			return true;
		}
		
		function process(){
			return true;
		}
		
		function tern($is_true,$a,$b){
			return $is_true ? $a : $b;
		}

		
		function checksub($with = null ){
 			$this->is_sub =array_key_exists($this->sub, $GLOBALS['_'.$this->method]) && ( !$with ||  $GLOBALS['_'.$this->method][$this->sub] === $with) ;
 			foreach ($this->navs as $navkey){
	 			$this->is_nav = false;
  				if (array_key_exists($navkey, $GLOBALS['_'.$this->method])){
	  				$this->is_nav = $navkey;
	  				break;
  				}
 			}
 			$this->methodVars =  $GLOBALS['_'.$this->method];  //careful here!!
 			return ($this->is_sub || $this->is_nav);
   		}
	
		function generate(){
			$form_html  = "<form".$this->form_attrs().">\n";
			$form_html .= $this->form_body();
			$form_html .= "\n</form>\n";
			return $form_html;
		}
		
		function get_value($field, $b='value="',$a='"'){
			return  isset($this->methodVars[$field]) ? $b.$this->methodVars[$field].$a : '';
		}
		
		function is_checked($field){
			return isset($this->methodVars[$field]) ?  'checked' : ''; 
		}
		
		function is_selected($field, $current, $sel='selected'){
			if (!isset($this->methodVars[$field])) { return '';}
			return $this->methodVars[$field] === $current ?  $sel : '' ;
		}
		
		function post_process(){}
		
		function derive_on_pg(){
			if (!is_string($this->is_nav)){ return $this->on_pg;}
			$active = (substr($this->is_nav, 0, 3 ) == "g__") ? substr($this->is_nav, 3) : $this->is_nav;
			return array_key_exists($active, $GLOBALS['_'.$this->method]) ? $GLOBALS['_'.$this->method][$active] : $this->on_pg;
		}
		
		function run($supress=false){
			if ($this->checksub()){
				if (!$this->is_processed && $this->run_validate()){
					$this->is_processed = $this->process();
					if ($this->do_post) {
						$this->post_process();
						if ($this->do_post === "only") { return;}
					}
				}
			};
			if ($this->is_nav !== false) {$this->on_pg = $this->derive_on_pg();}
			if (!$supress){ echo $this->generate();}
		}
	
	}
	
	$f = new RM_form('subbie');
	$f->run();
  
?>


