<?php
class  Database
{
    
    private static function connect()
    {
        $ini =  APPS_PATH . "/configs/database.ini" ;
        $parse = parse_ini_file ( $ini , true ) ;

        $driver = $parse [ "db_driver" ] ;
        $dsn = "${driver}:" ;
        $user = $parse [ "db_user" ] ;
        $password = $parse [ "db_password" ] ;
        $options = $parse [ "db_options" ] ;
        $attributes = $parse [ "db_attributes" ] ;

        foreach ( $parse [ "dsn" ] as $k => $v ) {
            $dsn .= "${k}=${v};" ;
        }
        $db = new PDO( $dsn, $user, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")  ) ;
        
        //$db->exec("SET CHARACTER SET " . $parse [ "db_character_set" ]);

        foreach ( $attributes as $k => $v ) {
            $db->setAttribute ( constant ( "PDO::{$k}" )
                , constant ( "PDO::{$v}" ) ) ;
        }     
        
        $db->exec("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))");

        return $db;
    }

    public static function prepare ( $statement ) {
        $db = Database::connect();        
        return $db->prepare( $statement );                
    }

    public static function lastInsertId($tablename, $primaryKey = 'id')
    {
        $db = Database::connect();
        $stmt = $db->prepare( "SELECT MAX($primaryKey) as MID FROM " . $tablename );
        $stmt -> execute ( ) ;
        $row = $stmt->fetchObject();

         return (int)$row->MID;
    }

    /**
     * Import SQL File
     *
     * @param $sqlFile
     * @param Logger_Logger $logerFactory
     * @param null $tablePrefix
     * @param null $InFilePath
     *
     * @return bool
     * @throws Exception
     */
    public static function importSqlFile($sqlFile,Logger_Logger $logerFactory, $tablePrefix = null, $InFilePath = null){
        $errorLogger = $logerFactory->returnLogger('error');
        $infoLogger = $logerFactory->returnLogger('info');
        try {
            
            // Enable LOAD LOCAL INFILE
            $pdo =  Database::connect();
            $pdo->setAttribute(\PDO::MYSQL_ATTR_LOCAL_INFILE, true);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $pdo->beginTransaction();
            $pdo->exec("START TRANSACTION");
            $errorDetect = false;
            
            // Temporary variable, used to store current query
            $tmpLine = '';
            
            // Read in entire file
            $lines = file($sqlFile);
            
            // Loop through each line
            foreach ($lines as $line) {
                // Skip it if it's a comment
                if (substr($line, 0, 2) == '--' || trim($line) == '') {
                    continue;
                }
                
                // Read & replace prefix
                $line = str_replace(['<<prefix>>', '<<InFilePath>>'], [$tablePrefix, $InFilePath], $line);
                
                // Add this line to the current segment
                $tmpLine .= $line;
                
                // If it has a semicolon at the end, it's the end of the query
                if (substr(trim($line), -1, 1) == ';') {
                    try {
                        // Perform the Query
                        $pdo->exec($tmpLine);
                        $infoLogger->addInfo("Execution des requêttes $tmpLine avec succées");
                    } catch (\PDOException $e) {
                        $errorLogger->addError("Error performing Query: $tmpLine : " . $e->getMessage() , $e->getTrace());
                        $errorDetect = true;
                    }
                    
                    // Reset temp variable to empty
                    $tmpLine = '';
                }
            }
            
            // Check if error is detected
            if ($errorDetect) {
                $pdo->exec("ROLLBACK");

                return false;
            } 
                
            
        } catch (\Exception $e) {
            $errorLogger->addError("Exception => " . $e->getMessage() , $e->getTrace());
            $pdo->exec("ROLLBACK");

            return false;
        }

        $infoLogger->addInfo("Execution des requêttes avec succées");
        $pdo->exec("COMMIT");

        return true;
    }
}