<?
/*
class queryString {
	protected $query;
	
	public function __construct($query) {
		$this->query=$query;
	}
	
	public function getQuery() {
		return $this->query;
	}
	
	public function select($columns) {
	$query="SELECT ";
	foreach($columns as $key=>$value) {
		$query=$query."'$value', ";
	}
	$this->query=substr($query,0,-2);
			
	return new queryString($this->query);
	}
	
	public function from($tablename) {
		$query=" FROM $tablename";
		return $query;
	}
	
}
*/

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
		
		//try {
			if ($this->type=='mysql') {
				$link=mysql_connect($config['host'],$config['username'],$config['password']) or die('Нет соединения');//throw new NotConnectException('Нет соединения с Сервером');
				//mysql_query ("CREATE DATABASE IF NOT EXISTS ".$database) or die ("Не могу создать базу данных!");
				//echo $host;
				mysql_select_db($config['database']) or die ('Не удалось подключиться к БД');
				//$MySqlQuery=new queryString("");
				//return $MySqlQuery;
			}
		//	catch (NotConnectException $e) {
		//		echo $e->getMessage();
		//	}
		//}
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
		if ($this->type=='mysql') {
			$query=$query.mysql_real_escape_string($value).", ";
		}
		echo mysql_real_escape_string($value)."<br/>";
	}
	
	$query=substr($query,0,-2);
	$this->query.=$query;
	return $this;
	}
	
	public function from($tablename) {
		$query=" FROM $tablename";
		$this->query.=$query;
		return $this;
	}
	
	public function where($conditions) {
		$query=" WHERE ";
		foreach ($conditions as $key=>$array) {
			foreach ($array as $value) {
				$query=$query."$value ";
			}
		}
		//$query=substr($query,0,-2);
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
		$query="INSERT INTO $table (";
		foreach ($columns as $key=>$value) {
			$query.="$value,";
		}
		$query=substr($query,0,-1);
		$query.=")";
		$this->query.=$query;
		return $this;
	}
	
	public function values($values) {
		$query=" VALUES (";
		foreach ($values as $key=>$value) {
			$query.="'$value',";
		}
		$query=substr($query,0,-1);
		$query.=")";
		$this->query.=$query;
		return $this;
	}
	
	public function update($table) {
		$query="UPDATE $table ";
		$this->query.=$query;
		return $this;
	}
	
	public function set($setvalues) {
		$query="SET ";
		foreach ($setvalues as $key=>$value) {
			$query.="$key='$value', ";
		}
		$query=substr($query,0,-2);
		$this->query.=$query;
		return $this;
	}
	
	public function delete($tablename) {
		$query="DELETE FROM $tablename";
		$this->query.=$query;
		return $this;
	}
	
	public function save() {
		if ($this->type=='mysql') {
			$this->result=mysql_query($this->query);
			$this->query="";
		}
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
	
//var_dump($conf);
//echo $conf["type"];
$db=new queryClass($conf);
//print_r($db);

$cols=array("name", "country");
$table="teams";
$conditions=array(array("id",">",3,"OR"),array("country","<>","'ukraine'"));
$values=array("real","spain");
$setvalues=array(
"name"=>"chelsea",
"city"=>"london");
//$query=$db->select($cols).$db->from($table).$db->where($conditions);
//$db->select($cols);
//$db->from($table);
//$db->insert($table, $cols);
//$db->values($values);
//$db->save();
//echo $db->from($table);
//$db->update($table);
//$db->set($setvalues);
//$db->where(array(array("name","=","'manchester united'")));
//$db->delete("teams");
//$db->where(array(array("id","=",2)));
//$db->save();

$db->select(array("*"))->from("teams")
->where(array(array("name","LIKE","'%csk%'","AND"),array("country","<>","'ukraine'")))
//->limit(5)
->orderby("country","DESC");


echo $db->getQuery();

$db->save();
$result=$db->getResult();

if (!$result) {
    echo "Could not successfully run query ($query) from DB: " . mysql_error();
    exit;
}

if (mysql_num_rows($result) == 0) {
    echo "No rows found, nothing to print so am exiting";
    exit;
}

// До тех пор, пока в результате содержатся ряды, помещаем их в ассоциативный массив.
// Замечание: если запрос возвращает только один ряд - нет нужды в цикле.
// Замечание: если вы добавите extract($row); в начало цикла, вы сделаете
//            доступными переменные $userid, $fullname и $userstatus
while ($row = mysql_fetch_assoc($result)) {
    echo $row["name"];
    echo $row["country"];
}

mysql_free_result($result);

//$conn=mysql_connect("localhost","root","");
//$database = "testdb";


?>
