<?php

/**
 * 所谓单例模式，即在应用程序中最多只有该类的一个实例存在，一旦创建，就会一直存在于内存中！
 * 应用场景：
 * 单例设计模式常应用于数据库类设计，采用单例模式，只连接一次数据库，防止打开多个数据库连接。
 * 一个单例类应具备以下特点：
 * 单例类不能直接实例化创建，而是只能由类本身实例化。因此，要获得这样的限制效果，构造函数必须标记为private，从而防止类被实例化。
 * 需要一个私有静态成员变量来保存类实例和公开一个能访问到实例的公开静态方法。
 * 在PHP中，为了防止他人对单例类实例克隆，通常还为其提供一个空的私有__clone()方法
 */

class Database{

	private $_link;
	private $_host;
	private $_username;
	private $_password;
	private $_port = 3306;
	private $_dbname;
	private $_charset = 'utf8';
	private $_logs = './logs';

	private static $_instance;

	private $_where = '';
	private $_table;
	private $_order;
	private $_limit;
	private $_column = '*';
	private $_params;
	private $_group;

	public static function getInstance()
	{
		if(!(self::$_instance instanceof self)){
            self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	private function __construct($config)
	{
		$this->_host = $config['host'];
		$this->_username = $config['username'];
		$this->_password = $config['password'];
		$this->_dbname = $config['dbname'];

		try{
		    $dsn = 'mysql:host='.$this->_host.';dbname'.$this->_dbname.';port='.$this->_port.';dbname='.$this->_dbname;
		    $this->_link = new PDO($dsn,$this->_username,$this->_password);
		    $this->_link->exec('SET NAMES '.$this->_charset);
        }catch (PDOException $e) {
            $this->_outputMsg($e->getMessage());
        }
        return $this->_link;
	}

    /*
     * 复杂的查询语句
     * */
	public function query($sql,$param)
    {
        if($sql && $param){
            $stmt = $this->_link->prepare($sql);
            $stmt->execute($param);
            $data = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $data[] = $row;
            }
            return $data;
        }
    }

	/*
	 * 查询多条数据
	 * */
	public function select()
    {
        $sql = "SELECT $this->_column FROM $this->_table ";
        if($this->_where){
            $sql .= "WHERE ".$this->_where;
        }
        if($this->_group){
            $sql .= $this->_group;
        }
        if($this->_order){
            $sql .= " ORDER BY ".$this->_order." ";
        }
        if($this->_limit){
            $sql .= $this->_limit;
        }
        $stmt = $this->_link->prepare($sql);
        $data = [];
        $stmt->execute($this->_params);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $data[] = $row;
        }
        echo $sql;
        return $data;
    }

    /*
     * 查询单挑数据
     * */
    public function find()
    {
        $sql = "SELECT $this->_column FROM $this->_table ";
        if($this->_where){
            $sql .= "WHERE ".$this->_where;
        }
        if($this->_group){
            $sql .= $this->_group;
        }
        if($this->_order){
            $sql .= " ORDER BY ".$this->_order." ";
        }
        $sql .= 'limit 0,1';
        $stmt = $this->_link->prepare($sql);
        $stmt->execute($this->_params);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data;
    }

    /*
     * 新增数据
     * @param array
     * 参数为需要插入的数据
     * */
    public function insert(array $data)
    {
        $sql = "INSERT INTO `$this->_table` ";
        $field = $vals = [];
        foreach($data as $key=>$value)
        {
            $field[] = "`$key`";
            $vals[] = ":$key";
            $this->_params[':key'] = $value;
        }
        $sql .= "(".implode(',',$field).") VALUES";
        $sql .= "(".implode(',',$vals).")";
        $stmt = $this->_link->prepare($sql);
        if($stmt->execute($this->_params)){
            return $stmt->rowCount();
        }else{
            $this->_outputMsg($stmt->errorInfo());
        }
    }

    /*
     * 删除记录
     * @param int
     * 参数为真时 默认删除主键ID值
     * */
    public function delete($id=null)
    {
        if($id){
            $sql = "DELETE FROM `$this->_table` WHERE id=".$id;
        }else{
            $sql = "DELETE FROM `$this->_table` WHERE $this->_where";
        }

        $stmt = $this->_link->prepare($sql);
        $stmt->execute($this->_params);
        if($stmt->execute($this->_params)){
            return $stmt->rowCount();
        }else{
            $this->_outputMsg($stmt->errorInfo());
        }
    }

    /*
     * 修改数据
     * @param array
     * 参数是需要修改的数据
     * */
    public function update(array $data)
    {
        $sql = "UPDATE `$this->_table` SET ";
        $set = '';
        if(!empty($data)) {
            foreach ($data as $key=>$value){
                $set .= " `".$key."`=:".$key.',';
                $this->_params[':'.$key] = $value;
            }
            $set = rtrim($set,',');
        }
        $sql .= $set;
        if($this->_where){
            $sql .= " WHERE".$this->_where;
        }
        $stmt = $this->_link->prepare($sql);
        $stmt->execute($this->_params);
        if($stmt->execute($this->_params)){
            return $stmt->rowCount();
        }else{
            $this->_outputMsg($stmt->errorInfo());
        }
    }

    public function table($param)
    {
        $this->_table = $param;
	    return $this;
    }

    public function colomn($param)
    {
        if(is_array($param) && !empty($param))
        {
            $this->_column = "`".implode('`,`',$param)."`";
        }else if(is_string($param)){
            $this->_column = $param;
        }

        return $this;
    }

    public function where(array $param,$characte='and')
    {
        if(!empty($param))
        {
            foreach($param as $key=>$value) {
                $this->_where .= " ".$key."=:".$key.' '.$characte;
                $this->_params[':'.$key] = $value;
            }
            $this->_where = rtrim($this->_where,"$characte");
        }

        return $this;
    }

    public function order($param)
    {
        if(is_array($param) && !empty($param))
        {
            foreach($param as $key=>$value){
                $this->_order .= "$value,";
            }
            $this->_order = trim($this->_order,',');
        }else if(is_string($param)){
            $this->_order = $param;
        }
        return $this;
    }

    public function limit($param,$offset = 0)
    {
        $param = (int)$param;
        $this->_limit = "limit $offset,$param";
        return $this;
    }

    public function group($param)
    {
        $this->_group = 'GROUP BY '.$param;
        return $this;
    }

	private function _outputMsg($msg)
    {
        // 解决中文乱码问题
        $msg = mb_convert_encoding($msg,'utf-8','gbk');
        $filename = date('Ymd');
        $filePath = $this->_logs.DIRECTORY_SEPARATOR.$filename.'.txt';

        if(!file_exists($filePath)){
            @mkdir($this->_logs,777,true);
        }
        $content = json_encode([
            'time' => date('Y-m-d H:i:s',time()),
            'line' => __LINE__,
            'file' => __FILE__,
            'content' => $msg,
        ],JSON_UNESCAPED_UNICODE);
        $content=str_replace("\r\n",'\r\n',$content);
        file_put_contents($filePath,$content."\r\n",FILE_APPEND);
    }

	private function __clone()
	{

	}
}
?>