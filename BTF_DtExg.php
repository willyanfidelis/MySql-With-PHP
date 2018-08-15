<?php
header("Access-Control-Allow-Origin: *");

require_once('PHPConsoleLog.php');
require_once('MySqliDB.php');

class BackToFrontEndDtExg {
	
	//Public/Private propertyies:
	
	
	function __construct($DBServerName, $DBUserName, $DBUserPwd, $DBName)
	{
		//$this::DefaultProp();
		
		//Create a new data base connection:
		$this->m_MySqlConn = new MySqliDB($DBServerName, $DBUserName, $DBUserPwd, $DBName);
	}
	function DefaultProp()
	{
		//$this->m_servername = "localhost";
		//$this->m_username = "root";
		//$this->m_password = "root";
		//$this->m_dbname = "smkdb";
	}
	
	//(IsAjax()) -> Verify if it is Ajax data:
	function IsAjax() {
		return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
	}
	
	//(GetAjaxData()) -> Gets the actual Ajax data:
	//Returns: Ajax data recived
	function GetAjaxData()
	{
		if ($this->IsAjax())
		{
			$pst_array = array();
			//foreach($_POST as $key=>$value)
			//{
			//	$pst_array[$key] = $value;
			//}
			$pst_array = $_POST;
			$dtRecived = json_encode($pst_array);//Transform the data in JSON format.
			return $dtRecived;
		}
		else
		{
			$dtRecived = json_encode(array("Status" => "NoAjaxReq"));
			return $dtRecived;
		}
	}
	
	//(EchoAjaxData()) -> Echo the Ajax data recived:
	function EchoAjaxData()
	{
		echo $this->GetAjaxData();
	}
	
	//(EchoTestObject()) -> Test comunnication function:
	function EchoTestObject()
	{
		$testObj = json_encode(array("Status" => "TestAjaxObjectOk"));
		//$testObj = json_encode(array("Status" => "NotAjaxReq", "codes" => array("runcode" => "a", "stepcode" => "c")));
		echo $testObj;
	}
	
	//(ExecuteSP()) -> Execute a Stored Procedure on Data Base:
	//Returns: Nothing.
	//Echos: Stored procedure data and results.
	function ExecuteSPWithDataSet($_echo, $_SPName, $_AliasSPFilds = array(), $_SPParmType = array(), $_SPParmName = array(), $_SPParmValue = array(), $_SPGetParm = array())
	{
		//Notice: $_AliasSPFilds are the filds of SELECT results in the stored procedure.
		$this->m_MySqlConn->connect();
		
		$this->m_MySqlConn->SPBindParameter($_SPParmType, $_SPParmName, $_SPParmValue);
		$SPResult = $this->m_MySqlConn->CallSPWithDataSet($_SPName, $_AliasSPFilds, false);
		
		$_SPGetParmRes = array();
		foreach ($_SPGetParm as $i => $value)
		{
			$_SPGetParmRes[$value] = $this->m_MySqlConn->GetSPResult($value);
		}
		
		//$Parmeter = $this->m_MySqlConn->GetSPResult("@OutParm");
		//$SPResult = array("Parameters"=>array("OutParm"=>$Parmeter), "Results"=>$SPResult);
		$SPResult = array("Parameters"=>$_SPGetParmRes, "Results"=>$SPResult);
		$this->m_MySqlConn->desconnect();
		
		$ToReurn=$SPResult;
		$SPResult = json_encode($SPResult);
		if ($_echo)
		{
			echo $SPResult;
		}
		return $ToReurn;
	}
	
	function ExecuteSP($_SPName, $_AliasSPFilds = array(), $_SPParmType = array(), $_SPParmName = array(), $_SPParmValue = array(), $_SPGetParm = array())
	{
		$this->m_MySqlConn->connect();
		
		$this->m_MySqlConn->SPBindParameter($_SPParmType, $_SPParmName, $_SPParmValue);
		$SPResult = $this->m_MySqlConn->CallSP($_SPName, false);
		
		$_SPGetParmRes = array();
		foreach ($_SPGetParm as $i => $value)
		{
			$_SPGetParmRes[$value] = $this->m_MySqlConn->GetSPResult($value);
		}
		
		//$Parmeter = $this->m_MySqlConn->GetSPResult("@OutParm");
		//$SPResult = array("Parameters"=>array("OutParm"=>$Parmeter), "Results"=>$SPResult);
		$SPResult = array("Parameters"=>$_SPGetParmRes, "Results"=>$SPResult);
		$this->m_MySqlConn->desconnect();
		
		$SPResult = json_encode($SPResult);
		echo $SPResult;
	}
	
	//(ExecuteBasicSP()) -> Execute a Stored Procedure on Data Base:
	//Returns: Nothing.
	//Echos: Stored procedure data and results.
	function ExecuteBasicSP($_SPName, $_AliasSPFilds = array())
	{
		$this->ExecuteSPWithDataSet(true, $_SPName, $_AliasSPFilds, array("i", "i"), array("InCode", "OutResult"), array(7, 0), array("OutResult"));
	}
	
	//(ExeBasicSPFromAjax()) -> This is the main comunication function - Listen the Ajax, get the ajax data and execute a basic stored procedure:
	//Returns: Nothing.
	//Echos: Stored procedure data and results.
	function ExeBasicSPFromAjax()
	{
		if ($this->IsAjax())
		{
			$i = 0;
			$SPNamePost = "";
			$SPAliasFildsAryPost = array();
			foreach($_POST as $key=>$value)
			{
				if ($i == 0)
				{
					$SPNamePost = $_POST[$key];			
				}
				else
				{
					$SPAliasFildsAryPost[$i] = $value;
				}
				$i = $i + 1;
			}
			$this->ExecuteBasicSP($SPNamePost, $SPAliasFildsAryPost);
		}
	}
}

///////////////////////////////////////$DataExchange = new BackToFrontEndDtExg("localhost", "root", "", "hsn_db"); //$DBServerName, $DBUserName, $DBUserPwd, $DBName
//$DataExchange->GetAjaxData();
//$DataExchange->EchoTestObject();
//$DataExchange->EchoAjaxData();

//$DataExchange->ExecuteSP("SelFromCtm", array("CustomerName", "Slogan"), array("i", "i"), array("InParm", "OutParm"), array(7, 0), array("OutParm"));
//$DataExchange->ExecuteBasicSP("SelFromCtm", array("CustomerName", "Slogan"));
//$DataExchange->ExeBasicSPFromAjax();

//------------ Create all function needed here ------------

//User Queries:
//$DataExchange->ExecuteSPWithDataSet("SpUserSelectAll", array("Name", "Email"), array("s", "i"), array("InParm", "Result"), array("555", 0), array("Result"));
///////////////////////////////////////$DataExchange->ExecuteSPWithDataSet("SpUserSelect", array("Name", "Email"), array("s", "i"), array("InParm", "Result"), array("wsf@hotmail", 0), array("Result"));
//$DataExchange->ExecuteSP("SpUserAdd", array("name", "email", "pwd", "result"), array("s", "s", "s", "i"), array("nm", "eml", "pwd", "result"), array($_GET["name"], $_GET["email"], $_GET["pwd"], 0), array("result"));

//$resultArray = array();
//$DataExchange->m_MySqlConn->connect();
//$DataExchange->m_MySqlConn->SimpleSelQuery("SELECT * FROM tuser;", $resultArray, false);
//------------ Create all function needed here ------------
?>

<?php
/*
echo "\r\n";
echo "<br>";
echo 'My name is: ' . htmlspecialchars($_GET["name"]) . '!';
echo 'My pwd is: ' . htmlspecialchars($_GET["pwd"]) . '!';


$path_parts = pathinfo($_SERVER['REQUEST_URI']);
echo "<br>";echo "<br>";echo "ok: "; echo "<br>";
echo $path_parts['dirname'], "\n"; echo "<br>";
echo $path_parts['basename'], "\n"; echo "<br>";
echo $path_parts['extension'], "\n"; echo "<br>";
echo $path_parts['filename'], "\n"; echo "<br>"; // desde o PHP 5.2.0
echo "<br>";echo $_SERVER['REQUEST_URI'];
*/
?>