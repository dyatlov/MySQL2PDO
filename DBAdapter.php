<?php
/**
 * @author Vitaly Dyatlov <md.xytop@gmail.com>
 *
 */

class MySQL
{
    const CON_PDO = 1;

    /**
     * @var PDO
     */
    protected static $dbh = null;

    /**
    * @var string
    */
    protected static $host = null;

    /**
    * @var string
    */
    protected static $username = null;

    /**
    * @var string
    */
    protected static $password = null;

    /**
    * @var int
    */
    protected static $rowCount = 0;

    /**
    * @param string|null $server
    * @param string|null $username
    * @param string|null $password
    * @param bool|null $new_link
    * @param string|null $client_flags
    * @return bool true for PDO or result of raw function 
    */
    public static function connect ($server = null, $username = null, $password = null, $new_link = null, $client_flags = null) {
        if( MYSQL_DB_CON_TYPE == MySQL::CON_PDO ) {
            MySQL::$host = $server;
            MySQL::$username = $username;
            MySQL::$password = $password;

            return true;
        }

        return mysql_connect ($server, $username, $password, $new_link, $client_flags);
    }

    public static function pconnect ($server = null, $username = null, $password = null, $client_flags = null) {
        if( MYSQL_DB_CON_TYPE == MySQL::CON_PDO ) {
            MySQL::$host = $server;
            MySQL::$username = $username;
            MySQL::$password = $password;

            return true;
        }
        
        return mysql_pconnect ($server, $username, $password, $client_flags);
    }

    public static function close ($link_identifier = null) {
        if( MYSQL_DB_CON_TYPE == MySQL::CON_PDO ) {
            return true;
        }

        return mysql_close ($link_identifier);
    }

    public static function select_db ($database_name, $link_identifier = null) {
        if( MYSQL_DB_CON_TYPE == MySQL::CON_PDO ) {
            try {
                MySQL::$dbh = new PDO(
                    "mysql:dbname=$database_name;host=" . MySQL::$host,
                    MySQL::$username,
                    MySQL::$password,
                    array(
                         PDO::ATTR_PERSISTENT => true,
                         PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => 1
                    )
                );
            } catch (PDOException $e) {
                return false;
            }
            
            return true;
        }
        if( $link_identifier != null)
            return mysql_select_db ($database_name, $link_identifier);
        return mysql_select_db ($database_name);
    }

    public static function query ($query, $link_identifier = null) {
        if( MYSQL_DB_CON_TYPE == MySQL::CON_PDO ) {
            $stmt = MySQL::$dbh->prepare($query, array( PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL ));

            $queryResult = $stmt->execute();

            $stmt->fetchOffset = 0;

            if($queryResult)
            {
                MySQL::$rowCount = $stmt->rowCount();
                return $stmt;
            }
            else
            {
                MySQL::$rowCount = 0;
                return false;
            }
        }
        
        if( $link_identifier != null)
            return mysql_query ($query, $link_identifier);
        return mysql_query ($query);
    }

    public static function error ($link_identifier = null) {
        if( MYSQL_DB_CON_TYPE == MySQL::CON_PDO ) {
            $error = MySQL::$dbh->errorInfo();
            return $error[2];
        }

        if( $link_identifier != null)
            return mysql_error ($link_identifier);
        return mysql_error ();
    }

    public static function errno ($link_identifier = null) {
        if( MYSQL_DB_CON_TYPE == MySQL::CON_PDO ) {
            return MySQL::$dbh->errorCode();
        }

        if( $link_identifier != null)
            return mysql_errno ($link_identifier);
        return mysql_errno ();
    }

    public static function affected_rows ($link_identifier = null) {
        if( MYSQL_DB_CON_TYPE == MySQL::CON_PDO ) {
            return MySQL::$rowCount;
        }
        
        if( $link_identifier != null)
            return mysql_affected_rows ($link_identifier);
        return mysql_affected_rows ();
    }

    public static function insert_id ($link_identifier = null) {
        if( MYSQL_DB_CON_TYPE == MySQL::CON_PDO ) {
            return MySQL::$dbh->lastInsertId();
        }

        if( $link_identifier != null)
            return mysql_insert_id ($link_identifier);
        return mysql_insert_id ();
    }

    /**
     * @static
     * @param PDOStatement $result
     * @return int
     */
    public static function num_rows ($result) {
        if( MYSQL_DB_CON_TYPE == MySQL::CON_PDO ) {
            if( $result instanceof PDOStatement )
                return $result->rowCount();
            return 0;
        }
        
        return mysql_num_rows ($result);
    }

    public static function fetch_row ($result) {
        if( MYSQL_DB_CON_TYPE == MySQL::CON_PDO ) {
            if( $result instanceof PDOStatement )
                return $result->fetch( PDO::FETCH_NUM, PDO::FETCH_ORI_ABS, $result->fetchOffset++ );
            return null;
        }
        
        return mysql_fetch_row ($result);
    }

    public static function fetch_array ($result, $result_type = null) {
        if( MYSQL_DB_CON_TYPE == MySQL::CON_PDO ) {
            if( $result instanceof PDOStatement )
                return $result->fetch( PDO::FETCH_BOTH, PDO::FETCH_ORI_ABS, $result->fetchOffset++ );
            return null;
        }

        return mysql_fetch_array ($result, $result_type);
    }

    /**
     * @static
     * @param PDOStatement $result
     * @return array
     */
    public static function fetch_assoc ($result) {
        if( MYSQL_DB_CON_TYPE == MySQL::CON_PDO ) {
            if( $result instanceof PDOStatement ) {
                return $result->fetch( PDO::FETCH_ASSOC, PDO::FETCH_ORI_ABS, $result->fetchOffset++ );
            }
            return null;
        }

        return mysql_fetch_assoc ($result);
    }

    /**
     * @static
     * @param PDOStatement $result
     * @param null $class_name
     * @param array|null $params
     * @return object|stdClass
     */
    public static function fetch_object ($result, $class_name = null, array $params = null ) {
        if( MYSQL_DB_CON_TYPE == MySQL::CON_PDO ) {
            if( $result instanceof PDOStatement )
                return $result->fetch( PDO::FETCH_OBJ, PDO::FETCH_ORI_ABS, $result->fetchOffset++ );
            return null;
        }

        if( $class_name != null )
            return mysql_fetch_object ($result, $class_name, $params);
        return mysql_fetch_object ($result);
    }

    /**
     * @static
     * @param PDOStatement $result
     * @param $row_number
     * @return bool
     */
    public static function data_seek ($result, $row_number) {
        if( MYSQL_DB_CON_TYPE == MySQL::CON_PDO ) { //actually it doesn't work in pdo, we can't seek on data :(

            if( $row_number >= $result->rowCount() ) //but it supports behavior generally
                return false;

            $result->fetchOffset = $row_number;

            return true;
        }

        return mysql_data_seek ($result, $row_number);
    }

    /**
     * @static
     * @param PDOStatement $result
     * @return bool
     */
    public static function free_result ($result) {
        if( MYSQL_DB_CON_TYPE == MySQL::CON_PDO ) {
            return true;
        }
        
        return mysql_free_result ($result);
    }

    /**
    * @deprecated
    */
    public static function escape_string ($unescaped_string) {
        return mysql_escape_string ($unescaped_string);
    }

    public static function real_escape_string ($unescaped_string, $link_identifier = null) {
        if( MYSQL_DB_CON_TYPE == MySQL::CON_PDO ) { //to avoid crashes (we dont have normal mysql connection)
            return mysql_escape_string ($unescaped_string);
        }

        if( $link_identifier != null)
            return mysql_real_escape_string ($unescaped_string, $link_identifier);
        return mysql_real_escape_string ($unescaped_string);
    }
}

# vim: tabstop=4 expandtab ai
