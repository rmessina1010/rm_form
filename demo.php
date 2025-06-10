<?
$test_data = array ("formit"=>array("Uname"=>"ff","Pass"=>"ff","input1"=>"1","input2"=>"2","input3"=>"3","input4"=>"4","selectme"=>"val5","navform"=>"1/0","check"=>"on"),"midit"=>array("something"=>"gygfvfvfv f f f","navform"=>"2/1"),"midwest"=>array("profession"=>"addjkkafdjkklaksldkakkad","navform"=>"3/2"),"pageit"=>array("Uname"=>"ccc","Pass"=>"ccc","selectme"=>"CO","submit"=>"Log In Here","check"=>"on"));

var_dump($test_data);
$form = new myForm( 'submit',array('mtd'=>'get', 'navs'=>array('navform')), $test_data);
$form->run();
?>