<?php

require_once('PHPConsoleLog.php');

class MySqliDB {
	
	//Public/Private propertyies:
	//Connect DB data:
	public $m_servername;
	public $m_username;
	public $m_password;
	public $m_dbname;
	//Connetion DB instance;
	public $m_conn;
	//Array of the last stored procedure parameters - IN, OUT and INOUT:
	private $m_SPParmName = array();
	//Prepare statement:
	public $m_stmt;
	
	function __construct($DBServerName, $DBUserName, $DBUserPwd, $DBName)
	{
		$this->m_servername = $DBServerName;
		$this->m_username = $DBUserName;
		$this->m_password = $DBUserPwd;//$this->m_password = "root";
		$this->m_dbname = $DBName;//$this->m_dbname = "smkdb";
		
		$this::DefaultDBProp();
	}
	function DefaultDBProp()
	{
		
		//$this->m_servername = "localhost";
		//$this->m_username = "root";
		//$this->m_password = "";//$this->m_password = "root";
		//$this->m_dbname = "hsn_db";//$this->m_dbname = "smkdb";
		
		/*
		$this->m_servername = "mysql21.redehost.com.br";
		$this->m_username = "willyanfidelis";
		$this->m_password = "Will_1985";
		$this->m_dbname = "hsn_db";//$this->m_dbname = "smkdb";
		*/
	}
	
	function DefaultQueryProp()
	{
		$this->m_conn->query("SET NAMES 'utf8'");
		$this->m_conn->query('SET character_set_connection=utf8');
		$this->m_conn->query('SET character_set_client=utf8');
		$this->m_conn->query('SET character_set_results=utf8');
	}
	
	function connect()
	{
		$this->m_conn = new mysqli($this->m_servername, $this->m_username, $this->m_password, $this->m_dbname );
		$this::DefaultQueryProp();
		if (!$this->m_conn)
		{
			die('Connect to data base failed. Error number: ' . mysql_error());
		}
		else
		{
			return true; //
		}
	}
	
	function desconnect()
	{
		$this->m_conn->close();
	}
	
	function SimpleSelQuery($_SqlQuery, $_arr = array(), $_DebugMode)
	{
		$result = $this->m_conn->query($_SqlQuery);

		
		if ($result->num_rows > 0) {
			$rowArray = array();
			$retArray = array();
			// output data of each row
			while($row = $result->fetch_assoc()) {
				$rowArray = array_combine($_arr, $row);
				$retArray[] = $rowArray;
				if ($_DebugMode)
				{
					foreach ($_arr as $i => $value)
					{
						//$rowArray[$value] = $row[$value];
						echo $value . ": " . $row[$value] . " | ";
					}
					echo "<br>";
				}
			}
		} else {
			echo "0 results";
		}
		return $retArray;
	}
	
	function SimpleQuery($sql)
	{
		$result = false;
		if ($result = $this->m_conn->query($sql) === TRUE) {
			echo "New record created successfully";
		} else {
			echo "Error: " . $sql . "<br>" . $conn->error;
		}
		if ($result == null)
		{
			$result = -1;
		}
		return $result;
	}
	
	function SPBindParameter($_SPParmType = array(), $_SPParmName = array(), $_SPParmValue = array())
	{
		$this->m_SPParmName = $_SPParmName;
		foreach ($_SPParmType as $i => $value)
		{
			$this->m_stmt = $this->m_conn->prepare("SET @" . $_SPParmName[$i] . "= ?");
			$this->m_stmt->bind_param($_SPParmType[$i], $val);
			$val = $_SPParmValue[$i];
			$this->m_stmt->execute();
			$this->m_stmt->close();
		}
	}
	
	function CallSPWithDataSet ($_SPName, $_AliasFild = array(), $_DebugMode)
	{
		$m_SPParmLst = "";
		foreach ($this->m_SPParmName as $i => $value)
		{
			$m_SPParmLst = $m_SPParmLst . "@" . $value;
			if (!($i + 1 >= count($this->m_SPParmName)))
			{
				$m_SPParmLst = $m_SPParmLst . ", ";
			}
		}
		
		if ($this->m_stmt = $this->m_conn->prepare("CALL " . $_SPName . "(" . $m_SPParmLst . ")")) 
		{
			$this->m_stmt->execute();
			
			
			// --- Ignore this code if it is not a 'Select' procedure ---
			$_i = 0;
			$rowArray = array();
			$retArray = array();
			$res = $this->m_stmt->get_result();
			while ($row = $res->fetch_row())
			{
				$rowArray = array_combine($_AliasFild, $row);
				$retArray[] = $rowArray;
				
				if ($_DebugMode)
				{
					$rowData = "";
					for ($i = 0; $i < count($row); $i++)
					{
						$rowData = $rowData . $_AliasFild[$i] . " = " . $row[$i] . " | ";
						
					}
					printf($rowData);
					printf("<br>");
				}
				$_i = $_i + 1;
			}
			
			// --- Ignore this code if it is not a 'Select' procedure ---
			
			/* close statement */
			$this->m_stmt->close();
			
			return $retArray;
		}
	}
	
	function CallSP ($_SPName, $_DebugMode)
	{
		$m_SPParmLst = "";
		foreach ($this->m_SPParmName as $i => $value)
		{
			$m_SPParmLst = $m_SPParmLst . "@" . $value;
			if (!($i + 1 >= count($this->m_SPParmName)))
			{
				$m_SPParmLst = $m_SPParmLst . ", ";
			}
		}
		
		if ($this->m_stmt = $this->m_conn->prepare("CALL " . $_SPName . "(" . $m_SPParmLst . ")")) 
		{
			$this->m_stmt->execute();
			
			
			/* close statement */
			$this->m_stmt->close();
			
			return true;
		}
	}
	
	function GetSPResult ($_ResultName)
	{
		$this->m_stmt = $this->m_conn->prepare("select @" . $_ResultName);
		$this->m_stmt->execute();
		$this->m_stmt->bind_result($OutRes);
		while ($this->m_stmt->fetch()) {
			//printf("%s\n", $OutRes);
		}
		$this->m_stmt->close();
		return $OutRes;
	}
}



//$myclass = new MySqliDB();
//$myclass->connect();

//$abc = $myclass->SimpleQuery("INSERT INTO tuser (username, email, pwd) VALUES ('maria', 'maria@123', 'mypwd')");
//echo "123abc: " . $abc;
//$abc = $myclass->SimpleSelQuery("select OID, email from tuser", array("OID", "email"), true);

//$myclass->desconnect();

/*

$myclass->SPBindParameter(array("i", "i"), array("InParm", "OutParm"), array(7, 0));
var_dump($myclass->CallSPWithDataSet("SelFromCtm", array("CustomerName", "Slogan"), false));
echo "<br>" . $myclass->GetSPResult("OutParm");

$myclass->desconnect();
*/
?>