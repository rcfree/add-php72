<?php
namespace ClinicLE\Common\Connection;

class AbstractDatabaseService {
	protected $conn;
    
    public function __construct(AbstractConnection $conn) {
		$this->conn = $conn;
    }

    /**
     * 
     * @return AbstractConnection
     */
    public function getConnection() {
		return $this->conn;
    }
	
}
