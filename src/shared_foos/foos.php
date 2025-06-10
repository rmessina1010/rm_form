<?
	function state_o($s=null){
		$states = ['AL','AK','AZ','AR','CA','CO','CT','DE','FL','GA','HI','ID','IL','IN','IA','KS','KY','LA','ME','MD','MA','MI','MN','MS','MO','MT','NE','NV','NH','NJ','NM','NY','NC','ND','OH','OK','OR','PA','RI','SC','SD','TN','TX','UT','VT','VA','WA','WV','WI','WY'];
		$state_options ="\n";
		foreach ($states as $state){  $sel = $state == $s ? ' selected ': ''; $state_options.= "<option value=\"$state\"$sel>$state</option>\n";}
		return $state_options;
	}
?>