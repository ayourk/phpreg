<?php
//  require_once($_SERVER["DOCUMENT_ROOT"] . "/phpreg/lib/debug_inc.php");

  require_once($_SERVER["DOCUMENT_ROOT"] . "/phpreg/lib/common_inc.php");

  function db_connect($database = "", $access = 'r', $options = array()) {
    $db_host_read = $GLOBALS["db_host_read"];
    $db_host_write = $GLOBALS["db_host_write"];
        
    // Check for additional options
    if (is_array($options)) {
      foreach($options as $option_name => $option_value) {
        switch($option_name) {
        case "db_host_read":
          $db_host_read = $option_value;
          break;
        case "db_host_write":
          $db_host_write = $option_value;
          break;
        default:
          break;
        }
      }
    }
        
    // Change databases
    if ($database == "") {
      $database = ($GLOBALS["db_dbname"]);
    }
        
    $dsn = array (
      "phptype"  => $GLOBALS["db_type"],
      "dbsyntax" => false,
      "username" => $GLOBALS["db_user"],
      "password" => $GLOBALS["db_pass"],
      "protocol" => false,
      "hostspec" => false,
      "port"     => false,
      "socket"   => false,
      "database" => $database,
    );
    $dsnstr = $GLOBALS["db_type"] . "://" . 
              $GLOBALS["db_user"] . ":" . 
              $GLOBALS["db_pass"] . "@" . 
              $GLOBALS["db_host_write_live"] . "/" . 
              $GLOBALS["db_dbname"];
    $dsn["hostspec"] = ($access == 'w') ? $GLOBALS["db_host_write"] : $GLOBALS["db_host_read"];
        
    // See if we specified a username and/or password
    if (is_array($options)) {
      if (isset($options["username"])) {
        $dsn["username"] = $options["username"];
      }
      if (isset($options["password"])) {
        $dsn["password"] = $options["password"];
      }
    }
        
    // Connect to the database
    $dbh = DB::connect($dsnstr);
    chk4dberr($dbh);
    $dbh->setFetchMode(DB_FETCHMODE_ASSOC);
        
    return $dbh;
  } // function db_connect
    
  function mdb2_connect($database = "", $access = 'r', $options = array()) {
    $db_host_read = $GLOBALS["db_host_read"];
    $db_host_write = $GLOBALS["db_host_write"];
        
        // Check for additional options
        if(is_array($options)) {
            foreach($options as $option_name => $option_value) {
                switch($option_name) {
                    case "db_host_read":
                        $db_host_read = $option_value;
                        break;
                    case "db_host_write":
                        $db_host_write = $option_value;
                        break;
                    default:
                        break;
                }
            }
        }
    
        // Change databases
        if($database != "") {
            db_use($database);
        } else if(!$_SESSION["dsn"]["database"]) {
            db_use($GLOBALS["db_dbname"]);
        }
        $database = $_SESSION["dsn"]["database"];
        
        $dsn = $_SESSION["dsn"];
        $dsn["hostspec"] = ($access == 'w') ? $db_host_write : $db_host_read;
        
        // See if we specified a username and/or password
        if(is_array($options)) {
            if(isset($options["username"])) {
                $dsn["username"] = $options["username"];
            }
            if(isset($options["password"])) {
                $dsn["password"] = $options["password"];
            }
        }
        
        // Capitalize all database usernames
//        if(isset($dsn["username"])) {
//            $dsn["username"] = strtoupper($dsn["username"]);
//        }
        
        // Connect to the database for reading
        $mdb2 =& MDB2::singleton($dsn);
        chk4dberr($mdb2);
        $mdb2->setFetchMode(MDB2_FETCHMODE_ASSOC);
        
        return $mdb2;
    } //mdb2_connect

  // Returns TRUE, FALSE or PEAR_Error
  function table_exists(&$dbh, $curtable, $create_table=true, $force_create=false) {
    $success = false;
    // Check to make sure that the tables we need exist.
    $table_list = $dbh->getListOf("tables");
    chk4dberr($dbh, $table_list);

    if (!in_array($curtable, $table_list) || $force_create) {
      // Since the table doesn't exist, let's try and create it with some provided SQL files (if they exist)
      $sqldir = $_SERVER["DOCUMENT_ROOT"] . "/phpreg/sql";
      $sqlfile = $sqldir . "/" . $curtable . ".sql";
      if (is_dir($sqldir) && is_file($sqlfile) && $create_table) {
        $query = file_get_contents($sqlfile);
        if ($query === false) {
          if ($create_table) {
              show_error("Filesystem error", "Cannot read from file '" . $sqlfile . "'");
          } else {
            return $success;
          }
        }
        $queryar = explode(";", $query);
        unset($query);
        $success = true;
        foreach ($queryar as $query) {
          $query = trim($query);
          if (!empty($query)) {
            $res = $dbh->query($query);
            chk4dberr($dbh, $res);
          }
        }
        return $success;
      } else if ($create_table) {
          show_error("Database error", "Table '" . $curtable . "' does not exist and its Schema file was not found!");
      } else {
        return $success;
      }
    }
  }

  function chk4dberr(&$dbhandle, &$res=NULL) {
    if (PEAR::isError($dbhandle)) {
      $sqlerror = "Database connection error: SQL connect error: " . $dbhandle->getUserInfo();
      $GLOBALS["tpl"]->assign("sqlerror", $sqlerror);
      show_error("Database connection error", "SQL connect error: " . $dbhandle->getUserInfo());
      exit;
    }
    if (!is_null($res) && PEAR::isError($res)) {
      $sqlerror = "Database connection error: SQL connect error: " . $res->getUserInfo();
      $GLOBALS["tpl"]->assign("sqlerror", $sqlerror);
      $dbhandle->disconnect();  // Play nice with database connections.
      show_error("Database connection error", "SQL connect error: " . $res->getUserInfo());
      exit;
    }
  }

?>
