<?php 
	ini_set('display_errors', 1); 
?> 
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
	function state_o(){
		$states = ['AL','AK','AZ','AR','CA','CO','CT','DE','FL','GA','HI','ID','IL','IN','IA','KS','KY','LA','ME','MD','MA','MI','MN','MS','MO','MT','NE','NV','NH','NJ','NM','NY','NC','ND','OH','OK','OR','PA','RI','SC','SD','TN','TX','UT','VT','VA','WA','WV','WI','WY'];
		$state_options ="\n";
		foreach ($states as $state){  $state_options.= "<option value=\"$state\">$state</option>\n";}
		return $state_options;
	}
	/**
	* HTML/PHP form
	*/
	class RM_form {
		protected $attr_keys	= ["char"=>"accept-charset", "act"=>"action", "auto"=>"autocomplete", "class"=>"class","enc"=>"enctype", "id"=>"id", 										"mtd"=>"method", "name"=>"name", "noval"=> "novalidate", "tgt"=>"target", "style"=>"style"];
		protected $attrs		= array();
		protected $form_name	= '';
		protected $form_data	= array();
		protected $css			= '';
		protected $is_css_url 	= false;
		protected $pg_ct 		= 0;
		protected $on_pg 		= 0;
		protected $is_valid 	= false;
		protected $method 		= '';
		protected $action 		= '';
		protected $pages 		= '';
		protected $is_sub		= false;
		protected $is_nav		= false;
		protected $is_pg_valid	= false;
		protected $navs			= array();
		protected $tg_index	= null;
		protected $is_processed	= false;
		protected $sub 			= '';
		protected $errs 		= array();
		protected $do_post		= true;
		protected $kp_inv_data	= false;
		protected $methodVars	= array();
		protected $sub_html		= '';
		
		
		function __construct($sub, $args = array()){
			if (!headers_sent() && session_status() === PHP_SESSION_NONE) { session_start(); }
		
				
			foreach($args  as $possible_attr_key => $att_val){
				if (isset($this->attr_keys[$possible_attr_key])){ 
					$this->attrs[$this->attr_keys[$possible_attr_key]] = $att_val; 
				}
				elseif (substr($possible_attr_key, 0, 2) === "on" ||  substr($possible_attr_key, 0, 5) === "aria-" || substr($possible_attr_key, 0, 5) === "data-"){ 
					$this->attrs[$possible_attr_key] =  $att_val; 
				}
			}
			$this->method = isset($args['mtd']) && $args['mtd'] ? strtoupper($args['mtd']) : 'GET';
 			$this->sub =$sub;
			if (isset($args['navs']) && is_array($args['navs'])){ $this->navs = $args['navs']; }
			$this->pg_ct =count($this->pages);
			$this->form_name = isset($this->attrs['name']) ?  $this->attrs['name'] : 'dflt';
			$_SESSION[$this->form_name]['data']			=    array();
			$_SESSION[$this->form_name]['current_index']= is_array($this->pages) ? 0 :  null;
			// $_SESSION[$this->form_name]['prev_pg']		=    null;
  		}
  		
  		protected function style(){
	  		return  $this->css;
  		}

		protected function setErr($field, $message, $delim=' '){
			$this->errs[$field] =  isset($this->errs[$field]) ?  $this->errs[$field].$delim.$message."\n": $message;
			if (!$this->kp_inv_data){ $this->set_methodVar($field, '');}
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
			$this->on_pg = is_array($this->pages) ?  $_SESSION[$this->form_name]['current_index'] : $this->on_pg;
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

		
		function checksub($with = null ){
 			$this->is_sub 	= array_key_exists($this->sub, $GLOBALS['_'.$this->method]) && ( !$with ||  $GLOBALS['_'.$this->method][$this->sub] === $with) ;
 			$this->is_nav 	= false;
 			$this->tg_index = null;
  			foreach ($this->navs as  $navkey){
  				if (array_key_exists($navkey, $GLOBALS['_'.$this->method])){
	  				$this->is_nav 		= $navkey;
	  				$this->tg_index 	= $this->derive_on_pg();
 	  				break;
  				}
 			}
 			return ($this->is_sub || $this->is_nav);
   		}
	
   		private  function set_methodVars(){  
		   	$this->methodVars = $GLOBALS['_'.$this->method];
   		}
   		
   		private  function set_methodVar($field,$val=''){
 			$this->methodVar[$field] = $val;
   		}
	
   		
		function generate(){
			$form_html  = "<form".$this->form_attrs().">\n";
			$form_html .= $this->form_body();
			$form_html .= "\n</form>\n";
			return $form_html;
		}
		
		function retrieve_var($field){
 			return isset($this->methodVars[$field]) ? $this->methodVars[$field] : null ;
		}
		
		function get_value($field, $b='value="',$a='"'){
			$val = $this->retrieve_var($field);
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
		
		protected function navigate(){
		    $this->persist_data();
			$this->on_pg = $_SESSION[$this->form_name]['current_index'];
			$this->load_data();
		}
		
		protected function persist_data(){
			if ( is_array($this->pages)){
				$_SESSION[$this->form_name]['data'][$this->pages[$this->on_pg]] = $this->methodVars;
			}
			else{ $_SESSION[$this->form_name]['data']  = $this->methodVars; }
 		}
 		
		protected function load_data(){
			$this->methodVars =  array();
			if ( is_array($this->pages) && isset($_SESSION[$this->form_name]['data'][$this->pages[$this->on_pg]])){
				$this->methodVars = $_SESSION[$this->form_name]['data'][$this->pages[$this->on_pg]];
			}
			if(isset($_SESSION[$this->form_name]['data']) && !is_array($this->pages)){
				$this->methodVars = $_SESSION[$this->form_name]['data'];
			}
  		}
		
		function run($supress=false){
			if ($this->checksub()){
				if (!$this->is_processed){
					if ( $this->is_sub || ($this->is_nav && $this->tg_index >= $_SESSION[$this->form_name]['current_index'])){
						$this->run_validate();
						if ($this->is_valid){
							if ($this->is_sub){
								$this->is_processed = $this->process();
								if ($this->do_post) {
									$this->post_process();
									if ($this->do_post === "only") { return;}
								}
							}
 							$this->navigate();
						}
					}
 				}
			}; 			
			if (!$supress){ echo $this->generate();}
		}
	
	}
	
class myForm extends RM_form{
	public 	$pages 		= ['formit', 'pageit'];
	public  $sub_html	= '<input type="submit" name="submit" id="submit" value="Log In Here"/>';
		
	function validate(){
		$current_pg = $this->pages[$this->on_pg];
 		if (!$this->methodVars['Pass']){ $this->setErr('Pass', 'A password is required'); }
		if (!$this->methodVars['Uname']){ $this->setErr('Uname', 'A user name is required'); }
 	  	if ($this->methodVars['Pass'] != $this->methodVars['Uname']){ { $this->setErr('Opps', 'hacker!!!'); }}
 	  	if ($current_pg == 'formit'){
	 	  	for ($i=1; $i<5; $i++){
		 	  if (!$this->methodVars['input'.$i]){ { $this->setErr('input'.$i, 'you are missing data at input'.$i); }}
	 		}
	 	  	if ($this->methodVars['input2'] != '2' ){ { $this->setErr('input2', 'must equal 2', '; '); }}
	 	}
	}
	protected function report($field, $tag="span", $attr="class=\"error\""){ return  isset($this->errs[$field]) ? "<$tag $attr>".$this->errs[$field]."</$tag>" :"";}

	function formit(){
	 	$resub =  $this->is_sub ? '<h2>You Have Submitted the form</h2>' : '';
	 	$valid =  $this->is_sub?  "<p>The Data is:". ($this->is_valid ? 'Valid' : 'Invalid')."</p>" :'';
	 	$processed =  $this->is_sub ? '<h3> '.($this->is_processed ? 'The form was processed successfully' : 'Attempt to process failed'.(!$this->is_valid ? ' due to invalid data':'').'!').'</h3>' : '';
	 	$b = $c = '';
		for ($i=1; $i<5; $i++){
	 		$b.="<lable>for input-$i</label><input id=\"input$i\" name=\"input$i\" {$this->get_value('input'.$i)}/>".$this->report('input'.$i)."<br>\n";
		}
		for ($i=1; $i<6; $i++){
	 		$c.="<option value=\"val$i\" {$this->is_selected('selectme', 'val'.$i)}/>value of $i</option>\n";
		}
		return <<<TEXT
		{$this->navigation_h()}
		$resub
		$valid
		$processed
        <div>
        	<label>User Name: </label>
        	<input type="text" name="Uname" id="Uname" placeholder="Username" {$this->get_value('Uname')} />{$this->report('Uname')}
        </div>     
        <div>
        	<label>Password: </label> 
        	<input type="Password" name="Pass" id="Pass" placeholder="Password" {$this->get_value('Pass')} />
        </div>
        {$this->report('Opps', 'div')}
        <div>
        $b
        </div>
        <div>
	        <label>A Dropdown</label>
	        <select name ="selectme">
	        $c
	        </select>
	    </div>
        {$this->navigation_b()}     
        <div><input type="checkbox" id="check" name="check" {$this->is_checked('check')}> <span>Remember me</span></div>
        <div>Forgot <a href="#">Password</a> </div>   
TEXT;
	}
	
	function pageit(){
		$state_o= 'state_o';
  		return <<<TEXT
		{$this->navigation_h()}
        <div><label>Address: </label>
        	<input type="text" name="Uname" id="Uname" placeholder="Username" {$this->get_value('Uname')} />{$this->report('Uname')}
        </div>     
        <div>
        	<label>City: </label> 
        	<input type="Password" name="Pass" id="Pass" placeholder="Password" {$this->get_value('Pass')} />
        </div>
        {$this->report('Opps', 'div')}
        <div>
	        <label>State: </label>
	        <select name ="selectme"> {$state_o()}</select>
	    </div>
	    {$this->navigation_b()}
        <div><input type="checkbox" id="check" name="check" {$this->is_checked('check')}> <span>Remember me</span></div>
        <div>Forgot <a href="#">Password</a> </div>   
TEXT;
	}
	
	function navigation_h($b='<div>Page ',$m=' of ',$a='</div> '){
		return ( is_array($this->pages)) ? $b.($this->on_pg+1).$m.$this->pg_ct.$a :'';
	}
	
	function navigation_b($sub=false){
		$nav = '';
		if ($this->on_pg > 0){ 
			$nav.= '<button type="submit" name="navform" id="back" value="'.($this->on_pg -1).'/'.$this->on_pg.'"><< Previous</button>';
 		}
		if ($this->on_pg+1 == $this->pg_ct){ 
			$nav.=($sub ? $sub :  $this->sub_html);
		}else if ($this->on_pg < $this->pg_ct){
			$i = $this->on_pg + 1;
			$nav.='<button type="submit" name="navform" id="next" value="'.($this->on_pg +1).'/'.$this->on_pg.'">Next >></button>';
 		}
		return $nav ? "<div>$nav</div>": "";
	}
	
	function post_process(){
		echo '<h3> ** '.($this->is_processed ? 'The form was processed successfully' : 'Attempt to process failed!').'</h3>' ;
	}
	
	function process(){
		var_dump($this->methodVars);
		return true;
	}
}
	
$form = new myForm( 'submit',array('mtd'=>'get', 'navs'=>array('navform')));
$form->run();
//todo:
//restrict navigation and validity to page/
?>