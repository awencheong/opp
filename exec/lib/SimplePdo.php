<?php
	namespace	Mob\console\lib;

	class SimplePdo {

        const DB_TYPE_MYSQL = 1;
        const DB_TYPE_PGSQL = 2;

		private $conn = null;
		private $in_transaction = false;

		public function __construct($dsn, $user, $passwd ){
			$this->conn = new \PDO($dsn, $user, $passwd);
		}

        /*
         *  sql generator , example
         *      array('age'=>1, "name"=>"awen")    maps to   (`age`, `name`) value ( 1, "awen")
         *
         *  @param  $values
         */
        public function arr2sql(array $values, $style=self::DB_TYPE_MYSQL) {
            $field_list = array();
            $value_list = array();
            foreach ($values as $field => $val) {
                if (!is_string($val) && !is_numeric($val)) {
                    return false;
                }
            }
            switch ($style) {
                case self::DB_TYPE_PGSQL:
                    $field_wrap = "'";
                    $value_wrap = "\"";
                    break;

                case self::DB_TYPE_MYSQL:
                default:
                    $field_wrap = "`";
                    $value_wrap = "'";
                    break;
            }
            foreach ($values as $field => $val) {
                $field_list[] = $field_wrap . $field . $field_wrap;
                $value_list[] = $value_wrap . $val . $value_wrap;
            }
            return "(" . implode(",", $field_list) . ") value (" . implode(",", $value_list) . ")";
        }

        /*	db insert , optional params
         *
         *	@param	sql, string; use "?" to identify params
         *	@param	params,  array
         *
         *	@return     num of rows updated succefully
         */
        public function update($sql, array $params=array()) {
            if ($result = $this->query($sql, $params)) {
                return $result[0][0];
            } else {
                return false;
            }
        }

        /*	db insert , optional params
         *
         *	@param	sql, string; use "?" to identify params
         *	@param	params,  array
         *
         *	@return     inserted id     or      num of rows inserted succefully     or      false
         */
        public function insert($sql, array $params=array()) {
            if ($result = $this->query($sql, $params)) {
                return $result[0][0];
            } else {
                return false;
            }
        }

		/*	db query , optional params
		 *	
		 *	@param	sql, string; use "?" to identify params
		 *	@param	params,  array
		 *
		 *	@return array 
		 */
		public function query($sql, array $params=array()){
			if (!$st = $this->conn->prepare($sql)) {
				$this->exception("mysql_pdo::query($sql,".json_encode($params)."),errmsg=".implode("|",$this->conn->errorInfo()).",errno=".$this->conn->errorCode());
			}

			if (!$st->execute($params)) {
				$this->exception("mysql_pdo::query($sql,".json_encode($params)."),errmsg=".implode("|", $st->errorInfo()).",errno=".$st->errorCode());
			}

			if ( $rows = $st->fetchAll(\PDO::FETCH_ASSOC)) {
				/* SELECT some rows */
				return $rows;

			}

			/* SELECT,UPDATE,DELETE,INSERT error */
			if ($rows === false) {
				$this->exception("mysql_pdo::query($sql,".json_encode($params)."),errmsg=".implode("|", $st->errInfo()).",errno=".$st->errorCode());
			}

			/* DELETE, INSERT, UPDATE some rows */
			if ($rows_count = $st->rowCount()) {

				/* rowCount by INSERT, UPDATE, DELETE */
				if ($rows_count == 1 &&
					($insertId = $this->conn->lastInsertId())
				) {
					/* INSERT one row, return id */
					$rows_count = $insertId;

				}
				return array(array($rows_count));

			} else {

				/* SELECT empty row */
				/* DELETE, INSERT, UPDATE no row */
				return array();
			}
		}

		private function exception($errmsg){
			if ($this->in_transaction) {
				$this->conn->rollBack();
				$this->in_transaction = false;
			}
			throw new \Exception($errmsg);
		}


		/*  db query,  optional params
		 *
		 *  @param	sql, string; use "?" to identify params
		 *  @param	params, array
		 *
		 *  @return string
		 */
		public function get_value($sql, array $params=array()){
			if ($result = $this->get_row($sql,$params)) {
				return array_shift($result);
			} else {

				return false;
			}
		}


        /*  db query,  optional params
         *
         *  @param	sql, string; use "?" to identify params
         *  @param	params, array
         *
         *  @return array
         */
        public function get_values($sql, array $params=array()){
            $list = array();
            foreach ($this->query($sql, $params) as $r) {
                $list[] = array_shift($r);
            }
            return $list;
        }



		/*  db query,  optional params
		 *
		 *  @param	sql, string; use "?" to identify params
		 *  @param	params, array
		 *
		 *  @return array
		 */
		public function get_row($sql, array $params=array()){
			if ($result = $this->query($sql,$params)) {
				return $result[0];
			} else {
				return false;
			}
		}


		/* start transaction
		 *
		 * @return always true
		 */
		public function start_transaction(){
			$this->in_transaction = true;
			if (!$this->conn->beginTransaction()) {
				throw new \Exception("mysql_pdo::start_transaction(".implode("|", $this->conn->errorInfo()).")");
			}
			return true;
		}


		/* commit transaction
		 *
		 * @return  always true
		 */
		public function commit(){
			if (!$this->conn->commit()) {
				throw new \Exception("mysql_pdo::commit(".implode("|", $this->conn->errorInfo()).")");
			}
			$this->in_transaction = false;
			return true;
		}


		/* rollback transaction
		 *
		 * @return	always true
		 */
		public function rollback(){
			if (!$this->conn->rollBack()) {
				throw new \Exception("mysql_pdo::commit(".implode("|", $this->conn->errorInfo()).")");
			}
			$this->in_transaction = false;
			return true;
		}
	}
