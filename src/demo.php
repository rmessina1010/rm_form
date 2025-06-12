<?
include('RM_form.php');
include('forms/multipage.php');
include('forms/single.php');
?><style>
<? include('styles/form_styles.css'); ?>
</style>
<?
$test_data = array ("formit"=>array("Uname"=>"ff","Pass"=>"ff","input1"=>"1","input2"=>"2","input3"=>"3","input4"=>"4","selectme"=>"val5","navform"=>"1/0","check"=>"on"),"midit"=>array("something"=>"gygfvfvfv f f f","navform"=>"2/1"),"midwest"=>array("profession"=>"addjkkafdjkklaksldkakkad","navform"=>"3/2"),"pageit"=>array("Uname"=>"ccc","Pass"=>"ccc","selectme"=>"CO","submit"=>"Log In Here","check"=>"on"));

var_dump($test_data);
$form = new myForm( 'test1', 'submit',array('mtd'=>'get', 'navs'=>array('navform')), $test_data);
$form->run();

/*
$form2 = new otherForm( 'test2', 'submit',array('mtd'=>'get' ), $test_data["formit"]);
$form2->run();
*/
?>