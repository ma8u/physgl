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
    
    function save_code($user_hash,$share_hash,$file_name,$code,$narrative)
    {
    	$code_hash = md5($user_hash . "physgl" . time() . $code);
    	//$share_hash = md5($user_hash . "physgl" . time());
		$this->db->query("insert into code values(NULL," . $this->db->escape($user_hash) . "," .
															$this->db->escape($file_name) . "," .
															$this->db->escape($code_hash) . "," . 
															$this->db->escape($share_hash) . "," . 
															$this->db->escape($code) . "," .
															$this->db->escape($narrative) . ",now())");
		//echo "save_code";
		return($code_hash);
    }
    
    function update_code($user_hash,$file_name,$code,$narrative)
    {
		$this->db->query("update code set code=" . 
							$this->db->escape($code) . 
							",date=now(),narrative=" .
							$this->db->escape($narrative) . " " .
							"where user_hash=" .
							$this->db->escape($user_hash) . " and file_name=" .
							$this->db->escape($file_name));
		//echo "update_code";
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
    	return(Array("filename" => $row['file_name'],"code" => $row['code'],"narrative" => $row['narrative']));
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
    
    public function get_layout($code_hash)
	{	
		if (!empty($code_hash))
			{
				$q = $this->db->query("select * from layout where code_hash=" . $this->db->escape($code_hash));
				$row = $q->row_array();
			}
		else 
			{
				$row = "";
			}
		
		$minimized = "";
		$code_minimized = "false";
		
		
		if (!empty($row['code']))
			$code = explode(",",$row['code']);
		else 
			{
				$code[0] = 10; $code[1] = 300; $code[2] = 300; $code[3] = 300;
			}
		if ($code[0] != 0 && $code[1] != 0)	
			$code_dialog = "position: [$code[0],$code[1]], width: $code[2], height: $code[3]";
		else
			{
				$code[3] = 500;
				$code_dialog = "width: $code[2], height: $code[3]";
				$minimized .= "$('#code_dialog').dialogExtend('minimize');\n";
				$code_minimized = "true";
			}
		$code_mirror = "width: " . $code[2] . ", height: " . $code[3];
		$code_mirror_css = "$('.CodeMirror').css({height: " . $code[3] . ",width:" . $code[2] . "});\n";
		//$code_mirror_css = "";
		
		if (!empty($row['buttons']))
			{
				$b = explode(",",$row['buttons']);
				$buttons = "top: " . $b[1] . ", left: " . $b[0];
			}
		else $buttons = "";
		
		if (!empty($row['graphics']))
			$graphics = explode(",",$row['graphics']);
		else
			{
				$graphics[0] = 330; $graphics[1] = 300; $graphics[2] = 300; $graphics[3] = 300;
			}
		if ($graphics[0] != 0 && $graphics[1] != 0)
			$graphics_dialog = "position: [$graphics[0],$graphics[1]], width: $graphics[2], height: $graphics[3]";
		else
			{
				$graphics[3] = 500;
				$graphics_dialog = "width: $graphics[2], height: $graphics[3]";
				$minimized .= "$('#graphics_dialog').dialogExtend('minimize');\n";
			}
		$pmode_small = "$('#pmode_small').css({height: " . $graphics[3] . ",width:" . $graphics[2] . "});\n";
		
		if (!empty($row['xy']))
			$xy = explode(",",$row['xy']);
		else 
			{
				$xy[0] = 0; $xy[1] = 0; $xy[2] = 500; $xy[3] = 500;
			}
		if ($xy[0] != 0 && $xy[1] != 0)
			$xy_dialog = "position: [$xy[0],$xy[1]], width: $xy[2], height: $xy[3]";
		else
			{
				$xy[3] = 500;
				$xy_dialog = "width: $xy[2], height: $xy[3]";
				$minimized .= "$('#xy_dialog').dialogExtend('minimize');\n";
			}
			
		if (!empty($row['console']))
			$console = explode(",",$row['console']);
		else 
			{
				$console[0] = 0; $console[1] = 0; $console[2] = 500; $console[3] = 500;
			}
		if ($console[0] != 0 && $console[1] != 0)
			$console_dialog = "position: [$console[0],$console[1]], width: $console[2], height: $console[3]";
		else
			{
				$console[3] = 500;
				$console_dialog = "width: $console[2], height: $console[3]";
				$minimized .= "$('#console_dialog').dialogExtend('minimize');\n";
			}
		$console_css = "$('#console').css({height: " . $console[3] . ",width:" . $console[2] . "});\n";
			
			
			
		return(Array(
					"code_dialog" => $code_dialog,
					"buttons" => $buttons,
					"code_minimized" => $code_minimized,
					"graphics_dialog" => $graphics_dialog,
					"code_mirror" => $code_mirror,
					"code_mirror_css" => $code_mirror_css,
					"pmode_small" => $pmode_small,
					"xy_dialog" => $xy_dialog,
					"minimized" => $minimized,
					"console_dialog" => $console_dialog,
					"console_css" => $console_css));
	}
	
	public function get_file_name_from_share_hash($share_hash)
	{
		$q = $this->db->query("select * from code where share_hash=" . $this->db->escape($share_hash));
		if ($q->num_rows() == 0)
			return(false);
		$row = $q->row_array();
		return($row['file_name']);
	}
}
?>