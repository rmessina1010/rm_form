<?
	ini_set('display_errors', 1); 
	session_start();  /// session

include('RM_form.php');
include('forms/multipage.php');
include('forms/single.php');
?><style>
<? include('styles/form_styles.css'); ?>
</style>
<?
$test_data = array ("formit"=>array("Uname"=>"ff","Pass"=>"ff","input1"=>"1","input2"=>"2","input3"=>"3","input4"=>"4","selectme"=>"val5","navform"=>"1/0","check"=>"on"),"midit"=>array("something"=>"gygfvfvfv f f f","navform"=>"2/1"),"midwest"=>array("profession"=>"addjkkafdjkklaksldkakkad","navform"=>"3/2"),"pageit"=>array("Uname"=>"ccc","Pass"=>"ccc","selectme"=>"CO","submit"=>"Log In Here","check"=>"on"));

$test_data2 = array("Uname"=>"new","Pass"=>"new","input1"=>"1","input2"=>"2","input3"=>"3","input4"=>"4","selectme"=>"val5","navform"=>"1/0","check"=>"on") ;

$form = new myForm( 'test1',  array('mtd'=>'get', 'navs'=>array('navform'), 'idtfy'=>'form1'), $test_data);
$form->run();

$form2 = new otherForm( 'test2', array('mtd'=>'get', 'idtfy'=>'form2' ), $test_data2);
$form2->run();

$form3 = new otherForm( 'test3', array('mtd'=>'get' ,'idtfy'=>'form3' ));
$form3->run();

$form4 = new otherForm( 'test4', array('mtd'=>'get' ,'idtfy'=>'form4' ));
$form4->do_array = true;
$form4->run();
if (!$form4->check_fresh()){ echo "data stored in object:"; var_dump($form4->get_data());  }
echo "process status [always false]:";
var_dump($form4->check_processed());
echo "fresh status:";
var_dump($form4->check_fresh());
echo "session status:";
var_dump($_SESSION);
?>