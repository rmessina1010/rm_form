<?php 
	class RM_form {
		protected $attr_keys	= ["char"=>"accept-charset", "act"=>"action", "auto"=>"autocomplete", "class"=>"class","enc"=>"enctype", "id"=>"id", 										"mtd"=>"method", "noval"=> "novalidate", "tgt"=>"target", "style"=>"style"];
		protected $attrs		= array();
		protected $form_name	= '';
		protected $form_data	= array();
		protected $form_idtfy	= null;
		protected $with_idtfy	= null;
		protected $is_identified= null;
		protected $pg_ct 		= 0;
		protected $on_pg 		= 0;
		protected $is_valid 	= false;
		protected $method 		= 'GET';
		protected $action 		= '';
		protected $pages 		= '';
		protected $is_sub		= false;
		protected $is_nav		= false;
		protected $is_pg_valid	= false;
		protected $navs			= array();
		protected $tg_index	= null;
		protected $is_processed	= false;
		protected $is_fresh		= true;
		protected $sub 			= 'submit';
		protected $with_sub		= null;
		protected $errs 		= array();
		protected $do_post		= true;
		protected $kp_inv_data	= false;
		protected $methodVars	= array();
		protected $sub_html		= '';
		protected $use_buffer	= true;
		
		function __construct($name, $args = array(), $fill=null){
			if (!headers_sent() && session_status() === PHP_SESSION_NONE) { session_start(); }
			$args = !is_array($args) ? array() :$args;
 				
			foreach($args  as $possible_attr_key => $att_val){
				if (isset($this->attr_keys[$possible_attr_key])){ 
					$this->attrs[$this->attr_keys[$possible_attr_key]] = $att_val; 
				}
				elseif (substr($possible_attr_key, 0, 2) === "on" ||  substr($possible_attr_key, 0, 5) === "aria-" || substr($possible_attr_key, 0, 5) === "data-"){ 
					$this->attrs[$possible_attr_key] =  $att_val; 
				}
			}
			if ( isset($args['mtd']) && $args['mtd']) { $this->method =  strtoupper($args['mtd']) ;}
			if (isset($args['idtfy'])){
				if  (is_array($args['idtfy'])){
					$idtfy = array_values($args['idtfy']);
					$this->form_idtfy =  $idtfy[0];
					if (isset($idtfy[1])){ $this->with_idtfy = $idtfy[1] === "*" ? $name : $idtfy[1]; }
				}else{ $this->form_idtfy =$args['idtfy'];}
			}
			if (isset($args['sub'])){
				if  (is_array($args['sub'])){
					$subs = array_values($args['sub']);
					$this->sub =  $subs[0];
					if (isset($subs[1])){ $this->with_sub = $subs[1]; }
				}else{ $this->sub =$args['sub'];}
			}
 			
			if (isset($args['navs']) && is_array($args['navs'])){ $this->navs = $args['navs']; }
			$this->pg_ct = is_array($this->pages) ? count($this->pages) : 1;
			$this->form_name = $this->attrs['name'] = $name;

			if (!isset($_SESSION[$this->form_name])){
				$_SESSION[$this->form_name]['data']			= is_array($fill)? $fill : array();
				$_SESSION[$this->form_name]['current_index']= is_array($this->pages) ? 0 :  null;
				$_SESSION[$this->form_name]['buffer_at']	= 0;
			}
  		}
  		
 
		function form_body_preprocess($html){
			if (method_exists($this, $html)) { return $this->{$html}();}
			if (isset($this->{$html}) && is_string($this->{$html})) {  return $this->{$html}; }
			return  $html;
		}
		
		function form_body(){
			if (is_string($this->pages)) { return $this->form_body_preprocess($this->pages);}
			if (is_array($this->pages) &&  $this->on_pg > -1 && $this->on_pg < $this->pg_ct) { return $this->form_body_preprocess($this->pages[$this->on_pg]);}
			return '';
		}

		function form_attrs(){
			$attrs ='';
			foreach ($this->attrs as $attr_key=> $attr_val){ $attrs.=" $attr_key=\"".$attr_val.'"'; }
			return $attrs;
		}
	
		protected function run_validate(){
			$this->errs = array();
 			$this->set_methodVars();
			$this->validate();
			$this->is_valid = (count($this->errs) < 1);
			// store data
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
		
		function checksub(){
 			$this->is_identified = ( !$this->form_idtfy 
 								|| ( array_key_exists($this->form_idtfy, $GLOBALS['_'.$this->method])
 									 && (!$this->with_idtfy  || $GLOBALS['_'.$this->method][$this->form_idtfy] === $this->with_idtfy)
 									)
 							);
 			$this->is_sub 	= 	$this->is_identified
 								&& array_key_exists($this->sub, $GLOBALS['_'.$this->method]) 
 								&& ( !$this->with_sub  ||  $GLOBALS['_'.$this->method][$this->sub] === $this->with_sub) ;
 			$this->is_nav 	= false;
 			$this->on_pg	= isset($_SESSION[$this->form_name]['current_index']) ?  $_SESSION[$this->form_name]['current_index'] :  0;
 			$this->tg_index = $this->on_pg;
 			
 			if(!$this->is_sub){
	  			foreach ($this->navs as  $navkey){
	  				if (array_key_exists($navkey, $GLOBALS['_'.$this->method])){
		  				$this->is_nav 	= $navkey;
		  				$nav_info		= $this->derive_on_pg();
		  				$this->tg_index = $nav_info[0];
		  				if (isset($nav_info[1])){ $this->on_pg	=   $nav_info[1];  }
	 	  				break;
	  				}
	 			}
 			} 
 			return ($this->is_sub || $this->is_nav);
   		}
	
   		private  function set_methodVars(){  
		   	$this->methodVars = $GLOBALS['_'.$this->method];
   		}
   		
   		private  function set_methodVar($field,$val=''){
 			$this->methodVars[$field] = $val;
    	}
	
   		private function cleanup(){
 	   		$this->on_pg =0;
	   		$this->tg_index=0;
	   		$this->is_processed = false;
	   		$this->is_sub = false;
	   		$this->is_nav = false;
	   		$this->methodVars=array();
   		}
   		
		function generate(){
			$form_html  = "<form".$this->form_attrs().">\n";
			$form_html .= $this->form_body();
			if (is_string($this->form_idtfy)){ $form_html.='<input type="hidden" name="'.$this->form_idtfy.'" value="'.$this->form_idtfy.'"/>'; }
			$form_html .= "\n</form>\n";
 			return $form_html;
		}
		
		function retrieve_var($field, $path='', $del='/'){
			if (!isset($this->methodVars[$field])){ return null;}
			$value = $this->methodVars[$field];
			$path = strlen(trim($path)) > 0 ? explode($del, $path) : array();
			for ($i=0, $l= count($path); $i < $l; $i++){ $value = $value[$path[$i]]; }
			return $value; 
		}
		
		function get_value($field, $path='', $del='/', $b='value="',$a='"'){
			$val = $this->retrieve_var($field,$path,$del);
			if (is_array($val)){ return $val;}
			return  $val !== null ? $b.$val.$a : '';
		}
				
		function is_checked($field){
			return $this->retrieve_var($field) !== null ?  'checked' : ''; 
		}
		
		function is_selected($field, $current, $target = false, $sel='selected'){
			if ($target === false) { $target =  $this->retrieve_var($field); }
			if ($target === null) { return '';}
			return $target === $current ?  $sel : '' ;
		}
		
		function post_process(){}
		
		function derive_on_pg(){
			if (!is_string($this->is_nav)){ return [$this->on_pg, $this->on_pg];}
			$active = (substr($this->is_nav, 0, 3 ) == "g__") ? substr($this->is_nav, 3) : $this->is_nav;
			$full =  array_key_exists($active, $GLOBALS['_'.$this->method]) ? $GLOBALS['_'.$this->method][$active] : $this->on_pg;
			return explode('/', $full);
		}

		protected function persist_data(){
			if ( is_array($this->pages)){
				$_SESSION[$this->form_name]['data'][$this->pages[$_SESSION[$this->form_name]['current_index']]] = $this->methodVars;
			}
			else{ $_SESSION[$this->form_name]['data']  = $this->methodVars; }
 		}
		
		protected function navigate($is_fwd = true){
		    if ($is_fwd) { $this->persist_data();}
		    else if(is_array($this->pages) &&  $this->use_buffer && $_SESSION[$this->form_name]['buffer_at'] == $_SESSION[$this->form_name]['current_index']){
				$_SESSION[$this->form_name]['buffer'] = array($this->pages[$_SESSION[$this->form_name]['buffer_at']] => $GLOBALS['_'.$this->method]);
			}
			if ($this->is_nav) {$this->on_pg = $this->tg_index;}
			$_SESSION[$this->form_name]['current_index'] = $this->on_pg;
			$this->load_data(is_array($this->pages) ? $this->pages[$this->on_pg] : $this->pages);
		}

		protected function load_data($pg_name){
			$this->methodVars =  array();
			if (is_array($this->pages)){
				if ($this->use_buffer && isset($_SESSION[$this->form_name]['buffer'][$pg_name])){
					$this->methodVars = $_SESSION[$this->form_name]['buffer'][$pg_name];
					return; 
				}
				if (isset($_SESSION[$this->form_name]['data'][$pg_name])){
					$this->methodVars = $_SESSION[$this->form_name]['data'][$pg_name];
				}
			}
			if(isset($_SESSION[$this->form_name]['data']) && !is_array($this->pages)){
				$this->methodVars = $_SESSION[$this->form_name]['data'];
			}
  		}
  		
  		function check_fresh(){ return $this->is_fresh; }
  		
  		function check_processed(){ return $this->is_processed; }
  		
  		function check_loaded(){
	  		if (is_array($this->pages)){
	  		 	if (isset($_SESSION[$this->form_name]['data'][$this->pages[$this->on_pg]])){ $this->load_data($this->pages[$this->on_pg]);}
	  		}
	  		else if (isset($_SESSION[$this->form_name]['data'])){ $this->load_data($this->pages);}
	  	}
	  	
		protected function setErr($field, $message, $condition_state = true, $resets=array()){
			if ($condition_state === false ){ return; }
			if (!isset($this->errs[$field])){ $this->errs[$field] = array(); }
			$this->errs[$field][] = $message;
			if (!$this->kp_inv_data) {
				$resets[] = $field;
				foreach ($resets as $reset){ 
					if (isset($this->methodVars[$reset]) && is_scalar($this->methodVars[$reset])) { $this->set_methodVar($reset, '');}
				}
			}
		}

	  	protected function report($field, $gbef="<span class=\"error\">", $gaft="</span>", $bef="", $aft=""){
			if (!isset($this->errs[$field])) { return ''; }
			$errors = $gbef.$bef.implode($aft.$bef,$this->errs[$field]).$aft.$gaft;
			return   $errors;
		}
	  	
 	  	function get_data( $flat = false ){
	 	  	return $this->form_data;
		  	if (!is_array($this->pages) || !$flat ) {return $this->form_data; }
		  	$data  = array(); 
		  	foreach ($this->form_data as $page) { $data = array_merge($data, $page); }
		  	return  $data;
	  	}
	  	
	  	function build_sub($attrs='', $inner_htm= null ){
			$attrs = preg_replace( '/name\s*\=\s*(\'|\").*\1/i', '', $attrs);
			$attrs = preg_replace( '/type\s*\=\s*(\'|\").*\1/i', '', $attrs);
 			$attrs.= 'type= "submit" name= "'.$this->sub.'"';
 			if ($this->with_sub){
				$attrs = preg_replace( '/value\s*\=\s*(\'|\").*\1/i', '', $attrs); 
				$attrs.=  ' value= "'.$this->with_sub.'"';
				$inner_htm  = $inner_htm === true ? $this->with_sub : $inner_htm;
 			}
			$the_button = is_string($inner_htm) ? '<button ' : '<input ';
			$the_button.= $attrs;
			$the_button.= is_string($inner_htm) ? $inner_htm.'</button>' : '/>' ;
 			return $the_button;
		}

   		function run($supress=false){
			if ($this->checksub()){
				if (!$this->is_processed){
					if ( $this->is_sub || ($this->is_nav && $this->tg_index >= $_SESSION[$this->form_name]['current_index'])){
						$this->run_validate();
						if ($this->is_valid){
							$this->persist_data();
							if ($this->is_sub){
								$this->form_data = $_SESSION[$this->form_name]['data'];
								echo "<b>data</b>";
								var_dump($this->form_data);
								$this->is_processed = $this->process();
								if($this->is_processed) { $this->is_fresh =  false ;}
								if ($this->is_processed && $this->do_post) {
									$this->post_process();
									$this->cleanup();
 									if ($this->do_post === "only") { return; }
								}
							}
							$this->navigate();
						}
					}
					elseif($this->is_nav && $this->tg_index < $_SESSION[$this->form_name]['current_index']){
						if( $_SESSION[$this->form_name]['current_index'] > $_SESSION[$this->form_name]['buffer_at']){
							$_SESSION[$this->form_name]['buffer_at'] = $_SESSION[$this->form_name]['current_index'];
						}
 						$this->navigate(false);
					}
 				}
			}
			else{ $this->check_loaded();}
			if (!$this->is_processed || !$supress){ echo $this->generate();}
		}
}
	
//todo:
//restrict navigation and validity to page/ -- done
//buffer  --  done
//load data -- done 
//flatten data -- done
// make name attribute mandatory -- done 
// switch submit/nav values imput method --done
// varable retrieval ( for tests) -- done
// do not set unexiting field when  err -- done
// move report -- done
// check single page functionality -- done
// generate a submit button  -- done
// add form identifier field (user entered/default null) [re check, and add logic] --done 
// multi resets  (???)
// multi-page vars(???)
// checksub on construct (???)
// clean up comments / output --done 
?>