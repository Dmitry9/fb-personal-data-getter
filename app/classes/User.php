<?php
namespace App;

use \PDO;
use \Exception;

/**
 * Class User supports users in DB
 * @package App
 */
class User {

	private $db = null;
	private $userTbl = '';

	/**
	 * Throw exception if $result === false
	 * @param $result
	 *
	 * @throws Exception
	 */
	protected function checkDBError($result) {
		if ( false===$result ) {
			throw new Exception("SQL error", $this->db->errorCode());
		}
	}

	/**
	 * User constructor.
	 * Create user table if its not exists
	 *
	 * @param Config|null $oConfig
	 *
	 * @throws Exception
	 */
	function __construct(Config $oConfig = null){
		if(is_null($this->db)){
			if(is_null($oConfig)){
				throw new Exception("Configuration object is null.");
			}
			
			$config = $oConfig->get('db');
			//Config validation is here

			// Connect to the database
			$dsn = "mysql:host={$config['Host']};dbname={$config['DbName']}";
			$conn = new PDO($dsn, $config['UserName'], $config['Password'], $config['Options']);
			$this->userTbl = $config['Table'];

			if(!$conn){
				die("Failed to connect DB");
			}else{
				$this->db = $conn;
			}

			$createSql = "
			CREATE TABLE IF NOT EXISTS `users` (
			 `id` BIGINT NOT NULL AUTO_INCREMENT,
			 `oauth_provider` enum('','facebook') COLLATE utf8_unicode_ci NOT NULL,
			 `oauth_uid` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
			 `first_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
			 `last_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
			 `email` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
			 `created` datetime NOT NULL,
			 `modified` datetime NOT NULL,
			 PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
			";
			$result = $this->db->exec($createSql);
			$this->checkDBError($result);
		}
	}

	/**
	 * Get user data if its exists. Insert a new user if it's not exists
	 *
	 * @param array $userData
	 *
	 * @return array
	 */
	public function checkUser($userData = array()) {
		if (!empty($userData)) {
			// Check whether user data already exists in database
			$prevQuery = "SELECT * FROM ".$this->userTbl." 
			WHERE oauth_provider = :oauth_provider AND oauth_uid = :oauth_uid
			";
			$request = $this->db->prepare($prevQuery);
			$request->bindValue(':oauth_provider', $userData['oauth_provider'], PDO::PARAM_STR);
			$request->bindValue(':oauth_uid', $userData['oauth_uid'], PDO::PARAM_STR);
			$prevResult = $request->execute();
			$this->checkDBError($prevResult);

			$now = date("Y-m-d H:i:s");
			if ($request->rowCount() > 0) {
				// Update user data if already exists
				$query = "UPDATE ".$this->userTbl." SET 
						   first_name = :first_name
				         , last_name = :last_name
				         , email = :email
				         , modified = '".$now
				         ."' WHERE oauth_provider = :oauth_provider AND oauth_uid = :oauth_uid
				         ";
			} else {
				// Insert user data
				$query = "INSERT INTO ".$this->userTbl." SET 
						   oauth_provider = :oauth_provider
				         , oauth_uid = :oauth_uid
				         , first_name = :first_name
				         , last_name = :last_name
				         , email = :email
				         , modified = '".$now."'
				         , created = '".$now."'
				         ";
			}
			$request = $this->db->prepare($query);
			$request->bindValue(':first_name', $userData['first_name'], PDO::PARAM_STR);
			$request->bindValue(':last_name', $userData['last_name'], PDO::PARAM_STR);
			$request->bindValue(':email', $userData['email'], PDO::PARAM_STR);
			$request->bindValue(':oauth_provider', $userData['oauth_provider'], PDO::PARAM_STR);
			$request->bindValue(':oauth_uid', $userData['oauth_uid'], PDO::PARAM_STR);
			$update = $request->execute();
			$this->checkDBError($update);

		}

		return $userData;
	}
}