<?php
/**
	@File Name 	:	DBConnect.php
	@Author 		:	Ramanathan M <rammrkv@gmail.com>
    @Created Date	:	2015-08-20 10:10 PM
	@Description	:	Database connection configuration class
*/
 
class DBConnect
{
	private $_Oconnection;
	
	public function __construct()
	{
		$dsn = "{$GLOBALS['CONF']['db']['type']}://{$GLOBALS['CONF']['db']['userName']}:{$GLOBALS['CONF']['db']['password']}@{$GLOBALS['CONF']['db']['host']}/{$GLOBALS['CONF']['db']['databaseName']}";

   		$this->_Oconnection = DB::connect($dsn);

   		if (DB::isError($this->_Oconnection)){
			return FALSE;
		}
	}

	public static function &singleton()
    {
        static $instance;

        // If the instance is not there, create one
        if (!isset($instance)) {
            $instance = new DBConnect();
        }
        return $instance;
    }

    public function executeQuery($query='')
    {
    	if(!empty($query))
    	{

			$action = strtoupper(substr($query, 0,6));
			$result = FALSE;

			switch ($action) {
				case 'INSERT':
					//$nextId = $this->_Oconnection->nextId($this->_StableName);
					if (DB::isError($resource = $this->_Oconnection->query($query))){
						logWrite($query,'QueryError');
						$result = FALSE;
    				}
					else{
						$result =mysql_insert_id();
						if($result == 0)
							$result = TRUE;
					}
				break;
				case 'SELECT':
					if(DB::isError($resource = $this->_Oconnection->query($query)) ){
						logWrite($query,'QueryError');
						$result = FALSE;
					}
					else{
						$result = array();
						while($row = $resource->fetchRow(DB_FETCHMODE_ASSOC)){
							$result[] = $row;
						}
						if(count($result)==1){
							$result = $result[0];
						}
					}
				break;
				case 'UPDATE':
				case 'DELETE':
					if(DB::isError($result = $this->_Oconnection->query($query))){
						logWrite($query,'QueryError');
						$result = FALSE;
					} 
					else{
						$result = TRUE;
					}					
				break;
			}
			return $result;
    	}
    }
	
	function _nextId($name)
    {
        // try to get the 'sequence_lock' lock
        $ok = $this->_Oconnection->getOne("SELECT GET_LOCK('sequence_lock', 10)");
		
        if (DB::isError($ok)) {
            return $this->_Oconnection->raiseError($ok);
        }
        if (empty($ok)) {
            // failed to get the lock, bail with a DB_ERROR_NOT_LOCKED error
            return $this->_Oconnection->mysqlRaiseError(DB_ERROR_NOT_LOCKED);
        }

        // get current value of sequence
        $query = "
            SELECT id
            FROM   {$GLOBALS['CONF']['table']['sequenceTable']}
            WHERE  name = '$name'
        ";
        $id = $this->_Oconnection->getOne($query);
        if (DB::isError($id)) {
            return $this->_Oconnection->raiseError($id);
        } else {
            $id += 1;
        }

        // increment sequence value
        $query = "
            REPLACE
            INTO    {$GLOBALS['CONF']['table']['sequenceTable']}
            VALUES  ('$name', '$id')
        ";
        $ok = $this->_Oconnection->query($query);
        if (DB::isError($ok)) {
            return $this->_Oconnection->raiseError($ok);
        }

        // release the lock
        $ok = $this->_Oconnection->getOne("SELECT RELEASE_LOCK('sequence_lock')");
        if (DB::isError($ok)) {
            return $this->_Oconnection->raiseError($ok);
        }

        return $id;
    }
	
	function getAll($query)
	{
		$params = array();
		if(DB::isError($result = $this->_Oconnection->getAll($query,$params,DB_FETCHMODE_ASSOC)) ){
			logWrite($query,'QueryError');
			$result = array();
		}
		return $result;
	}
	
	function getOne($query)
	{
		if(DB::isError($result = $this->_Oconnection->getOne($query)) ){
			logWrite($query,'QueryError');
			$result = '';
		}
		
		return $result;
	}
	
	function getCol($query)
	{
		if(DB::isError($result = $this->_Oconnection->getCol($query)) ){
			logWrite($query,'QueryError');
			$result = array();
		}
		return $result;
	}
	
	function query($query)
	{
		if(DB::isError($result = $this->_Oconnection->query($query)) ){
			logWrite($query,'QueryError');
			$result = false;
		}
		return $result;
	}
	
	function numRows()
	{
		return $this->_Oconnection->numRows();
	}
	
	function getAssoc($query)
	{
		$params = array();
		if(DB::isError($result = $this->_Oconnection->getAssoc($query,false,$params,DB_FETCHMODE_ASSOC,true)) ){
			logWrite($query,'QueryError');
			$result = array();
		}
		return $result;
	}
}