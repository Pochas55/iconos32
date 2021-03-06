<?php
//Clase Abstracta que nos permitirá conectarnos a MySQL
abstract class Model {
	//ATRIBUTOS
	private static $db_host = 'localhost';
	private static $db_user = 'root';
	private static $db_pass = '';
	private static $db_name = 'mexflix32';
	private static $db_charset = 'utf8';
	private $mysql;
	protected $sql;
	protected $sql_transaction = array();
	protected $rows = array();
	private $result;

	//MÉTODOS
	//métodos abstractos para CRUD de clases que hereden
	abstract protected function set();
	abstract protected function get();
	abstract protected function del();

	//método privado para conectarse a la base de datos
	private function db_open() {
		//http://php.net/manual/es/book.mysqli.php
		$this->mysql = new mysqli(
			self::$db_host,
			self::$db_user,
			self::$db_pass,
			self::$db_name
		);

		try {
			if ( $this->mysql->connect_errno ) {
				throw new Exception(
					'<li>Error N°: <mark>' . $this->mysql->connect_errno . '</mark></li>' .
					'<li>Mensaje del Error: <mark>' . $this->mysql->connect_error . '</mark></li>'
				);
			} else {
				$this->mysql->set_charset( self::$db_charset );
			}
		} catch (Exception $e) {
			print '<h3>Error en la conexión a MySQL:</h3><ul>' . $e->getMessage() . '</ul>';
			die();
		}
	}

	//método privado para desconectarse de la base de datos
	private function db_close() {
		$this->mysql->close();
	}

	//establecer un query que afecte datos (INSERT, DELETE o UPDATE)
	protected function set_query() {
		$this->db_open();
		try {
			if ( !$this->mysql->query( $this->sql ) ) {
				throw new Exception(
					'<li>Error N°: <mark>' . $this->mysql->errno . '</mark></li>' .
					'<li>Mensaje del Error: <mark>' . $this->mysql->error . '</mark></li>' .
					'<li>Consulta SQL: <mark>' . $this->sql . '</mark></li>'
				);
			}
		} catch (Exception $e) {
			print '<h3>Error en la Sentencia SQL:</h3><ul>' . $e->getMessage() . '</ul>';
			die();
		}
		$this->db_close();
	}

	protected function set_transaction() {
		$this->db_open();
		$this->mysql->autocommit(false);
		try {
			for ($n=0; $n < count($this->sql_transaction); $n++) { 
				if ( !$this->mysql->query( $this->sql_transaction[$n] ) ) {
					throw new Exception(
						'<li>Error N°: <mark>' . $this->mysql->errno . '</mark></li>' .
						'<li>Mensaje del Error: <mark>' . $this->mysql->error . '</mark></li>' .
						'<li>Consulta SQL: <mark>' . $this->sql_transaction[$n] . '</mark></li>'
					);
				}
			 } 
		} catch (Exception $e) {
			$this->mysql->rollback();
			print '<h3>Error en la Sentencia SQL:</h3><ul>' . $e->getMessage() . '</ul>';
			die();
		}
		$this->mysql->commit();
		$this->db_close();
	}

	//obtener datos de un query (SELECT)
	protected function get_query() {
		$this->db_open();
		try {
			if ( !$this->result = $this->mysql->query( $this->sql ) ) {
				throw new Exception(
					'<li>Error N°: <mark>' . $this->mysql->errno . '</mark></li>' .
					'<li>Mensaje del Error: <mark>' . $this->mysql->error . '</mark></li>' .
					'<li>Consulta SQL: <mark>' . $this->sql . '</mark></li>'
				);
			}
		} catch (Exception $e) {
			print '<h3>Error en la Sentencia SQL:</h3><ul>' . $e->getMessage() . '</ul>';
			die();
		}
		while ( $this->rows[] = $this->result->fetch_assoc() );
		$this->result->free();
		$this->db_close();
		return array_pop( $this->rows );
	}
}