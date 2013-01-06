<?php

class Files extends CI_Model {

    function __construct()
    {
        parent::__construct();
    }
    
    function get_user_hash($user_name)
    {
    	$q = $this->db->query("select * from user where user_name='$user_name'");
    	if ($q->num_rows() == 0)
    		return("none");
    	$row = $q->row_array();
    	return($row['user_hash']); 
    }
    
    function get_share_hash($code_hash)
	{
		$q = $this->db->query("select share_hash from code where code_hash='$code_hash'");
		if ($q->num_rows() == 0)
			return("none");
		$row = $q->row_array();
		return($row['share_hash']); 
	}
	
	
	function get_code_hash($share_hash)
	{
		$q = $this->db->query("select code_hash from code where share_hash='$share_hash'");
		if ($q->num_rows() == 0)
			return("none");
		$row = $q->row_array();
		return($row['code_hash']); 
	}
    
    function save_code($user_hash,$file_name,$code)
    {
    	$code_hash = md5($user_hash . "physgl" . time() . $code);
    	$share_hash = md5($user_hash . "physgl" . time());
		$this->db->query("insert into code values(NULL," . $this->db->escape($user_hash) . "," .
															$this->db->escape($file_name) . "," .
															$this->db->escape($code_hash) . "," . 
															$this->db->escape($share_hash) . "," . 
															$this->db->escape($code) . ",now())");
		echo "save_code";
    }
    
    function update_code($user_hash,$file_name,$code)
    {
		$this->db->query("update code set code=" . 
							$this->db->escape($code) . 
							",date=now() where user_hash=" .
							$this->db->escape($user_hash) . " and file_name=" .
							$this->db->escape($file_name));
		echo "update_code";
    }
    
    function check_exists($user_hash,$file_name)
    {
    	$q = $this->db->query("select * from code where user_hash=" . $this->db->escape($user_hash) . 
    								" and file_name= " . $this->db->escape($file_name));
    	if ($q->num_rows() != 0)
    		return(true);
    	return(false);
    }
    
    function get_file_name_and_code($code_hash)
    {
    	$q = $this->db->query("select * from code where code_hash='$code_hash'");
    	if ($q->num_rows() == 0)
    		return(Array("filename" => "untitled","code" => ""));
    	$row = $q->row_array();
    	return(Array("filename" => $row['file_name'],"code" => $row['code']));
    }
    
    function get_file_list($user_hash)
    {
    	$fl = "";
    	$q = $this->db->query("select * from code where user_hash=" . $this->db->escape($user_hash));
		foreach($q->result_array() as $row)
		{
			$file_name = urldecode($row['file_name']);
			$code_hash = $row['code_hash'];
			$fl .= "<input type=\"checkbox\" value=\"$code_hash\" onClick=\"add_to_delete('$code_hash');\"> ";
			$fl .= anchor("welcome/load_code/$code_hash",$file_name) . "<br/>";
		
		}
		return($fl);
    
    }
    
    function get_time_zone()
    {
    	return('America/Los_Angeles');
    }
}
?>