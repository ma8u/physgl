<?php



class Auth extends CI_Model {

	
    function __construct()
    {
        parent::__construct();
    }
    
    function get_salt()
    {
    	return("do_graphics_555!!!091_with_physgl");
    }
    
    function authenticate_user($username,$password)
    {
    	$pw_hash = md5($this->Auth->get_salt() . $username . $password);
    	
    	$q = $this->db->query("select * from user where user_name='$username' and auth_hash='$pw_hash'");
    	if ($q->num_rows() == 0)
    		return(false);
    	return(true);
    }
    
    function authenticate_userhash($user_hash)
    {
    	$q = $this->db->query("select * from user where user_hash='$user_hash'");
    	if ($q->num_rows() == 0)
    		return(false);
    	return(true);
    }
    
    function get_username_from_userhash($user_hash)
    {
    	$q = $this->db->query("select * from user where user_hash='$user_hash'");
    	if ($q->num_rows() == 0)
    		return('unknown');
    	$row = $q->row_array();
    	return($row['user_name']);
    }
    
    
    function create_new_account($username,$password)
    {
    	$hash = md5($this->Auth->get_salt() . $username . $password);
    	$user_hash = md5(time() . $username . $this->Auth->get_salt());
    	
    	$q = $this->db->query("insert into user values(NULL," .
    				$this->db->escape($username) . "," .
    				$this->db->escape($hash) . "," .
    				$this->db->escape($user_hash) . ")");
    }
    
   
}
?>