<?
class queryClass {
	protected $type;
	protected $host;
	protected $username;
	protected $password;
	protected $database;
	protected $query;
	protected $result;
	
	public function __construct($config) {
		$this->type=$config["type"];
		$this->host=$config["host"];
		$this->username=$config["username"];
		$this->password=$config["password"];
		$this->database=$config["database"];
		
			if ($this->type=='mysql') {
				$link=mysql_connect($config['host'],$config['username'],$config['password']) or die('Нет соединения с сервером');//throw new NotConnectException('Нет соединения с Сервером');
				mysql_select_db($config['database']) or die ('Не удалось подключиться к БД');
			}
			if ($this->type=='mssql') {
				$link=mssql_connect($config['host'],$config['username'],$config['password']) or die('Нет соединения с сервером');
				mssql_select_db($config['database']) or die ('Не удалось подключиться к БД');
			}
	}
	
	public function getConfig() {
		$config["type"]=$this->type;
		$config["host"]=$this->host;
		$config["username"]=$this->username;
		$config["password"]=$this->password;
		$config["database"]=$this->database;
		
		return $config;
	}
	
	public function getQuery() {
		return $this->query;
	}
	
	public function select($columns) {
		
		$query="SELECT ";
		foreach($columns as $key=>$value) {
			if ($value!='*') {
				$query=$query."`$value`, ";
			}
			else {
				$query=$query."$value, ";
			}
		}
		$query=substr($query,0,-2);
		$this->query.=$query;
		return $this;
	}
	
	public function from($tablename) {
		$query=" FROM `$tablename`";
		$this->query.=$query;
		return $this;
	}
	
	public function where($conditions) {
		
		$query=" WHERE ";
		foreach($conditions as $key=>$array) {
			$i=0;
			foreach($array as $value) {
				if(get_magic_quotes_gpc()==1) {
					$value=stripslashes(trim($value));
				}
				else
				{
					$value=trim($value);
				}

				switch($i) {
					case 0:  {	$query=$query."`$value` "; $i++; break;}
					case 1:  {	$query=$query."$value "; $i++; break;}
					case 2:  { 	
						$value=mysql_real_escape_string($value);
						$query=$query."'$value' "; 
						$i++; 
						break;
					}
					default: {	$query=$query."$value "; }
				}
				if (sizeOf($array)==3) {
					$query=substr($query,0,-1);
				}
			}
		}
		
		$this->query.=$query;
		return $this;
	}
	
	public function limit($num1,$num2=0) {
		$query=" LIMIT $num1";
		if ($num2) {
		$query.=", $num2";
		}
		$this->query.=$query;
		return $this;
	}
	
	public function orderby($column, $ord) {
		$query=" ORDER BY $column $ord";
		$this->query.=$query;
		return $this;
	}
	
	public function insert($table, $columns) {
		$query="INSERT INTO `$table` (";
		foreach ($columns as $key=>$value) {
			$query.="`$value`,";
		}
		$query=substr($query,0,-1);
		$query.=")";
		$this->query.=$query;
		return $this;
	}
	
	public function values($values) {
		$query=" VALUES (";
		foreach ($values as $key=>$value) {
			if(get_magic_quotes_gpc()==1) {
				$value=stripslashes(trim($value));
			}
			else
			{
				$value=trim($value);
			}

			$value=mysql_real_escape_string($value);
			$query=$query."'$value', "; 
		}
		$query=substr($query,0,-2);
		$query.=")";
		$this->query.=$query;
		return $this;
	}
	
	public function update($table) {
		$query="UPDATE `$table` ";
		$this->query.=$query;
		return $this;
	}
	
	public function set($setvalues) {
		$query="SET ";
		foreach ($setvalues as $key=>$value) {
			if(get_magic_quotes_gpc()==1) {
				$value=stripslashes(trim($value));
			}
			else
			{
				$value=trim($value);
			}
			$value=mysql_real_escape_string($value);
			$query.="`$key`='$value', ";
		}
		$query=substr($query,0,-2);
		$this->query.=$query;
		return $this;
	}
	
	public function deletefrom($tablename) {
		$query="DELETE FROM `$tablename`";
		$this->query.=$query;
		return $this;
	}
	
	public function save() {
		if ($this->type=='mysql') {
			$this->result=mysql_query($this->query);
		}
		if ($this->type=='mssql') {
			$this->result=mssql_query($this->query);
		}
		$this->query="";
	}
	public function getResult() {
		return $this->result;
	}
}

$conf=array(
	"type"=>"mysql", 
	"host"=>"localhost", 
	"username"=>"root", 
	"password"=>"", 
	"database"=>"testdb"
	);
	
$db=new queryClass($conf);

$cols=array("name", "country");
$table="teams";
$conditions=array(array("id","<>",3,'AND'),array('country','<>','ukraine'));
$values=array("real","spain");
$setvalues=array(
"name"=>"chelsea",
"city"=>"london");

$db->select(array("*"))->from("teams")
//->where($conditions);
//->where(array(array("name","=","-1' UNION SELECT `name`, `lastname` FROM coaches WHERE id='1","AND"),array("name","<>","ukraine")));
//->where(array(array("name","=","-1';DELETE FROM `teams` WHERE city='london")));
->where($conditions);
//->limit(5)
//->orderby("name","DESC")
//->limit(1);
echo $db->getQuery()."<br>";
$db->save();

$db->insert($table,array("name","country","city"))
->values(array("zenit","russia","St. Petersburg"));
echo $db->getQuery()."<br>";
$db->save();

$db->update($table)
->set(array("name"=>"arsenal"))
->where(array(array("city","=","london")));
echo $db->getQuery()."<br>";
$db->save();

$db->deletefrom("teams")
->where(array(array("id",'>',6)));
echo $db->getQuery();
$db->save();

$result=$db->getResult();

/*
if (!$result) {
    echo "Could not successfully run query ($query) from DB: " . mysql_error();
    exit;
}

if (mysql_num_rows($result) == 0) {
    echo "No rows found, nothing to print so am exiting";
    exit;
}


while ($row = mysql_fetch_assoc($result)) {
    echo $row["name"];
    echo $row["city"];
	echo $row["country"]."<br>";
}
*/
//mysql_free_result($result);
