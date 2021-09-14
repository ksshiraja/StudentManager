<?php
    class DB {
        protected $pdo; 
        protected $db_hostname = '';
        protected $db_username = '';
        protected $db_password = '';
        protected $db_database = ''; 
        protected $query;
        public function __construct($db_hostname, $db_username, $db_password, $db_database) {
			try {
				$pdo = new PDO("mysql:host=$db_hostname;dbname=$db_database;charset=utf8", $db_username, $db_password);
				$pdo->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);
				$this->pdo = $pdo;
				$this->db_hostname = $db_hostname;
				$this->db_username = $db_username;
				$this->db_password = $db_password;
				$this->db_database = $db_database; 
			} catch (Exception $E) {
				 echo "500 Error: " . $E->getMessage();
			}
            return $this;
        }
        public function lastId()
        {
            return $this->pdo->lastInsertId();
        }
        public function q($query, $data = array()) {
            $this->query = $query;
            try { 
				$pdo = $this->pdo;
				if ($pdo) {
					$request = $pdo->prepare($query);
					$request->execute($data);
					return $request;
				}
				
			} catch (Exception $E) { 
                return $E->getMessage();
			}
        } 
    }

?>