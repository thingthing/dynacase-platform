<?php

/**
 * ldap authentication provider
 *
 */
 /**
  */

include_once("WHAT/Class.Provider.php");
Class fileProvider extends Provider {

  private function readPwdFile($pwdfile) {
    $fh = fopen($pwdfile, 'r');
    if( $fh == FALSE ) {
      error_log(__CLASS__."::".__FUNCTION__." "."Error: opening file ".$pwdfile);
      return FALSE;
    }
    $passwd = array();
    while($line = fgets($fh)) {
      $el = split(':', $line);
      if( count($el) != 2 ) {
	continue;
      }
      $passwd{$el[0]} = trim($el[1]);
    }
    fclose($fh);
    return $passwd;
  }


  public function validateCredential($username, $password) {  
    
    static $pwdFile = false;
    
    if( ! array_key_exists('authfile', $this->parms) ) {
      error_log(__CLASS__."::".__FUNCTION__." "."Error: authfile parm is not defined at __construct");
      return FALSE;
    }
    
    if ($pwdFile===false) $pwdFile = $this->readPwdFile($this->parms{'authfile'} );
    if ($pwdFile===false) {
      error_log(__CLASS__."::".__FUNCTION__." "."Error: reading authfile ".$this->parms{'authfile'});
      return false;
    }
    
    if( ! array_key_exists($username, $pwdFile) ) return FALSE;
    $ret = preg_match("/^(..)/", $pwdFile[$username], $salt);
    if( $ret == 0 ) return FALSE;
    
    if( $pwdFile[$username] == crypt($password, $salt[0]) ) return true;
    
    return false;
  }

  public function validateAuthorization($opt) {
    return TRUE; 
  }


}
?>