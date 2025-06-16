<?
	include_once 'shared_foos/foos.php';
	class myForm extends RM_form{
	public 	$pages 		= ['formit', 'midit', 'midwest', 'pageit'];
	protected $with_sub = 'Log In Here-m';
	public  $sub_html	= '<input type="submit" name="submit" id="submit" value="Log In Here-m"/>';
		
	function validate(){
		$current_pg = $this->pages[$this->on_pg];
		if( $current_pg == 'formit' || $current_pg == 'pageit'){
	 		$this->setErr('Pass', 'A password is required', (!$this->methodVars['Pass']));
			$this->setErr('Uname', 'A user name is required', (!$this->methodVars['Uname'])); 
	 	  	$this->setErr('Opps', 'hacker!!!', ($this->methodVars['Pass'] != $this->methodVars['Uname']));
	 	  	if ($current_pg == 'formit'){
		 	  	for ($i=1; $i<5; $i++){
			 	   $this->setErr('input'.$i, 'you are missing data at input'.$i, (!$this->methodVars['input'.$i]));
		 		}
		 	  	$this->setErr('input2', 'must equal 2', ($this->methodVars['input2'] != '2' ),'; ');
		 	}	
		}
	}

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

?>