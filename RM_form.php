<?php 
	ini_set('display_errors', 1); 
	session_start();  /// session
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
	function state_o($s=null){
		$states = ['AL','AK','AZ','AR','CA','CO','CT','DE','FL','GA','HI','ID','IL','IN','IA','KS','KY','LA','ME','MD','MA','MI','MN','MS','MO','MT','NE','NV','NH','NJ','NM','NY','NC','ND','OH','OK','OR','PA','RI','SC','SD','TN','TX','UT','VT','VA','WA','WV','WI','WY'];
		$state_options ="\n";
		foreach ($states as $state){  $sel = $state == $s ? ' selected ': ''; $state_options.= "<option value=\"$state\"$sel>$state</option>\n";}
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
		protected $use_buffer	= true;
		
		
		function __construct($sub, $args = array(), $fill=null){
			// if (!headers_sent() && session_status() === PHP_SESSION_NONE) { session_start();  echo"!!!";}
 				
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

			if (!isset($_SESSION[$this->form_name])){
				$_SESSION[$this->form_name]['data']			= is_array($fill)? $fill : array();
				$_SESSION[$this->form_name]['current_index']= is_array($this->pages) ? 0 :  null;
				$_SESSION[$this->form_name]['buffer_at']	= 0;
				// $_SESSION[$this->form_name]['prev_pg']		=    null;

			}else{ 

/*
								echo "<div>Pre exisiting</div>";
								var_dump($_SESSION[$this->form_name]);
*/

			}
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
 			echo "<h3>VALIDATING: ".$this->pages[$_SESSION[$this->form_name]['current_index']].", with </h3>";
 			// $this->on_pg = is_array($this->pages) ?  $_SESSION[$this->form_name]['current_index'] : $this->on_pg;
			$this->validate();
			$this->is_valid = (count($this->errs) < 1);
			if ($this->is_valid) { }
			var_dump($this->methodVars);
 			var_dump($this->is_valid);

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
			echo "<div> CHECKSUB!!!</div>";
 			$this->is_sub 	= array_key_exists($this->sub, $GLOBALS['_'.$this->method]) && ( !$with ||  $GLOBALS['_'.$this->method][$this->sub] === $with) ;
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
			var_dump( array($this->is_sub, $this->is_nav,  $this->tg_index, $_SESSION[$this->form_name]));
 			return ($this->is_sub || $this->is_nav);
   		}
	
   		private  function set_methodVars(){  
		   	$this->methodVars = $GLOBALS['_'.$this->method];
   		}
   		
   		private  function set_methodVar($field,$val=''){
 			$this->methodVar[$field] = $val;
   		}
	
   		private function re_ready_form(){
	   		unset($_SESSION[$this->form_name]);
	   		$this->on_pg =0;
	   		$this->tg_index=0;
	   		$this->is_processed = false;
	   		$this->is_sub = false;
	   		$this->is_nav = false;
   		}
   		
		function generate(){
			$form_html  = "<form".$this->form_attrs().">\n";
			$form_html .= $this->form_body();
			$form_html .= "\n</form>\n";
			if ($this->is_processed){ $this->re_ready_form(); }
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
		
		protected function navigate($is_fwd = true){
		    if ($is_fwd) { $this->persist_data();}
		    else if(is_array($this->pages) &&  $this->use_buffer && $_SESSION[$this->form_name]['buffer_at'] == $_SESSION[$this->form_name]['current_index']){
				$_SESSION[$this->form_name]['buffer'] = array($this->pages[$_SESSION[$this->form_name]['buffer_at']] => $GLOBALS['_'.$this->method]);
			}
			if ($this->is_nav) {$this->on_pg = $this->tg_index;}
			$_SESSION[$this->form_name]['current_index'] = $this->on_pg;
			$this->load_data(is_array($this->pages) ? $this->pages[$this->on_pg] : $this->pages);
		}
		
		protected function persist_data(){
			if ( is_array($this->pages)){
				$_SESSION[$this->form_name]['data'][$this->pages[$_SESSION[$this->form_name]['current_index']]] = $this->methodVars;
			}
			else{ $_SESSION[$this->form_name]['data']  = $this->methodVars; }
 		}
 		
		protected function load_data($pg_name){
			echo "<div>loading data for {$pg_name}</div>";
			var_dump($_SESSION[$this->form_name]['data']);
			var_dump($this->pages[$_SESSION[$this->form_name]['current_index']]);			
			var_dump($this->on_pg);			
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
  		
  		function check_loaded(){
	  		if (is_array($this->pages)){
	  		 	if (isset($_SESSION[$this->form_name]['data'][$this->pages[$this->on_pg]])){ $this->load_data($this->pages[$this->on_pg]);}
	  		}
	  		else if (isset($_SESSION[$this->form_name]['data'])){ $this->load_data($this->pages);}
	  	}
	  	
 	  	function get_data( $flat = false ){
		  	if (!is_array($this->pages) || !$flat ) {return $this->form_data; }
		  	$data  = array(); 
		  	foreach ($this->form_data as $page) { $data = array_merge($data, $page); }
		  	return  $data;
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
								$this->is_processed = $this->process();
								if ($this->do_post) {
									$this->post_process();
 									if ($this->do_post === "only") { return;}
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
			if (!$supress){ echo $this->generate();}
		}
	
}
	
class myForm extends RM_form{
	public 	$pages 		= ['formit', 'midit', 'midwest', 'pageit'];
	public  $sub_html	= '<input type="submit" name="submit" id="submit" value="Log In Here"/>';
		
	function validate(){
		$current_pg = $this->pages[$this->on_pg];
		if( $current_pg == 'formit' || $current_pg == 'pageit'){
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
        	<input type="text" name="Pass" id="Pass" placeholder="Password" {$this->get_value('Pass')} />
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

	
	function midit(){
  		return <<<TEXT
		{$this->navigation_h()}
        <div><label>Address: </label>
        	<input type="text" name="something" id="something" placeholder="something" {$this->get_value('something')} />{$this->report('something')}
        </div>     
 	    {$this->navigation_b()}
TEXT;
	}
	function midwest(){
   		return <<<TEXT
		{$this->navigation_h()}
        <div><label>profession: </label>
        	<input type="text" name="profession" id="profession" placeholder="profession" {$this->get_value('profession')} />{$this->report('profession')}
        </div>     
 	    {$this->navigation_b()}
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
        	<input type="text" name="Pass" id="Pass" placeholder="Password" {$this->get_value('Pass')} />
        </div>
        {$this->report('Opps', 'div')}
        <div>
	        <label>State: </label>
	        <select name ="selectme"> {$state_o($this->get_value('selectme','',''))}</select>
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
		echo "<div>PROCESSING</div>";
		var_dump(json_encode($this->get_data(true)));
		return true;
	}
}
$test_data = array ("formit"=>array("Uname"=>"ff","Pass"=>"ff","input1"=>"1","input2"=>"2","input3"=>"3","input4"=>"4","selectme"=>"val5","navform"=>"1/0","check"=>"on"),"midit"=>array("something"=>"gygfvfvfv f f f","navform"=>"2/1"),"midwest"=>array("profession"=>"addjkkafdjkklaksldkakkad","navform"=>"3/2"),"pageit"=>array("Uname"=>"ccc","Pass"=>"ccc","selectme"=>"CO","submit"=>"Log In Here","check"=>"on"));

var_dump($test_data);
$form = new myForm( 'submit',array('mtd'=>'get', 'navs'=>array('navform')), $test_data);
$form->run();
//todo:
//restrict navigation and validity to page/ -- done
//buffer  --  done
//load data -- done 
//flatten data -- done
// switch submit/nav values imput method
// varable retrieval ( for tests)
// multi-page vars(???)
// clean up comments / output
?>