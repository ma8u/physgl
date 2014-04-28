<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once('r.php');

class Welcome extends CI_Controller {

 public function __construct()
       {
            parent::__construct();
           $this->load->model('Files');
        	$this->load->model('Auth');
        	$this->load->model('Data');
           $this->load->helper('form');
           $this->load->model('Code');
       }
       
    public function index()
    {
    	$user_name = $this->session->userdata('username');
    	$user_hash = $this->Files->get_user_hash($user_name);
    	$share_hash = 'none';
    	$login_form = 'yes';
    	if (!empty($user_name))
    		{
    			if ($this->Auth->authenticate_userhash($user_hash))
					$login_form = 'no';
				$share_hash = md5($user_hash . "physgl" . time());
			}
				
    	$this->load->view('header',Array('login_form' => $login_form,'username' => $user_name));
		//$this->load->view('login');
		if ($login_form == 'yes')
			$this->load->view('physgl_intro');
		$this->load->view('codepage',Array(	"user_name" => $user_name,
											"user_hash" => $user_hash,
											"filename" => "untitled",
											"code" => "",
											"narrative" => "",
											"project_type" => "new",
											"share_hash" => $share_hash,
											"share" => false));
		$this->load->view('footer');
    }
    
    public function logout()
    {
    	$this->session->set_userdata(Array('username' => ''));
    	$this->session->sess_destroy();
    	$this->index();
    }
    
    public function authenticate()
    {
    	$username = $this->input->post("username");
    	$password = $this->input->post("password");
    	if ($this->Auth->authenticate_reset($username,$password))
    		{
    			$this->load->view('header',Array('username'=> '','login_form' => 'no'));
				$this->load->view('reset_authenticate',Array('username' => $username));
				$this->load->view('footer');
    			return;
    		}
    	if ($this->Auth->authenticate_user($username,$password))
    		$this->start($username);
    	else
    		{
    			$this->load->view('header',Array('username'=> $username,'login_form' => 'yes'));
				$this->load->view('cant_authenticate');
				$this->load->view('footer');
    		}
    		
    }

	public function start($username)
	{
		$this->load->view('header',Array('login_form' => 'no','username' => $username));
		$this->session->set_userdata(Array('username' => $username));
		$this->load->view('file_manager');
		$this->load->view('footer');
	}
	
	public function filemanager()
	{
		$user_name = $this->session->userdata('username');
		$user_hash = $this->Files->get_user_hash($user_name);
		if ($this->Auth->authenticate_userhash($user_hash))
			$this->load->view('header',Array('login_form' => 'no','username' => $user_name));
		else $this->load->view('header',Array('login_form' => 'yes','username' => ''));
		$this->load->view('file_manager');
		$this->load->view('footer');
	}
	
	public function save_code()
	{
		$file_name = trim(urldecode($this->input->post('filename')));
		$user_hash = $this->input->post('user_hash');
		$share_hash = $this->input->post('share_hash');
		$share = $this->input->post('share');
		$code = $this->input->post('code');
		if (strlen($code) > 10000)
			$code = substr($code,0,10000);
		$narrative = $this->input->post('narrative');
		if (strlen($narrative) > 10000)
			$narrative = substr($narrative,0,10000);
		$run_count = $this->input->post('run_count');
		$project_type = $this->input->post('project_type');
		
		if (empty($share_hash))
			{
				echo "_PHYSGL_same_name";
				return;
			}
		
		if ($this->Auth->authenticate_userhash($user_hash) == false)
			return;
			
		
		if ($share === 'true')
			return;
			
		$store_file_name = $this->Files->get_file_name_from_share_hash($share_hash);
		if ($store_file_name !== false && $store_file_name != $file_name) // they changed the file name
			{
				date_default_timezone_set('America/Los_Angeles');
				if ($this->Files->check_exists($user_hash,$file_name))
					$file_name .= " " .date('Y-m-d H:i:s');
				$share_hash = md5($user_hash . "physgl" . $file_name . time());
				$code_hash = $this->Files->save_code($user_hash,$share_hash,$file_name,$code,$narrative);
				echo "_PHYSGL_new_name___$file_name";
				echo "____PHYSGL_new_share_hash___" . site_url("welcome/load_code/$code_hash");
				return;
			}
		
		if ($this->Files->check_exists($user_hash,$file_name))
				$this->Files->update_code($user_hash,$file_name,$code,$narrative);
		else $code_hash = $this->Files->save_code($user_hash,$share_hash,$file_name,$code,$narrative);
		
		echo "___same_name";
	}
	
	public function save_layout()
	{
		$code_hash = $this->input->post('code_hash');
		
		$code_left = $this->input->post('code_left'); 
		$code_top = $this->input->post('code_top');
		$code_height = $this->input->post('code_height');
		$code_width = $this->input->post('code_width');
		
		$graphics_left = $this->input->post('graphics_left'); 
		$graphics_top = $this->input->post('graphics_top');
		$graphics_height = $this->input->post('graphics_height');
		$graphics_width = $this->input->post('graphics_width');
		
		$console_left = $this->input->post('console_left'); 
		$console_top = $this->input->post('console_top');
		$console_height = $this->input->post('console_height');
		$console_width = $this->input->post('console_width');
		
		$xy_left = $this->input->post('xy_left'); 
		$xy_top = $this->input->post('xy_top');
		$xy_height = $this->input->post('xy_height');
		$xy_width = $this->input->post('xy_width');
		
		$button_top = $this->input->post('button_top');
		$button_left = $this->input->post('button_left');
		
		$this->db->query("delete from layout where code_hash=" . $this->db->escape($code_hash));
		$this->db->query("insert into layout values(NULL," . $this->db->escape($code_hash) . "," .
								$this->db->escape("$code_left,$code_top,$code_width,$code_height") . "," .
								$this->db->escape("$graphics_left,$graphics_top,$graphics_width,$graphics_height") . "," .
								$this->db->escape("$xy_left,$xy_top,$xy_width,$xy_height") . "," .
								$this->db->escape("$console_left,$console_top,$console_width,$console_height") . "," .
								$this->db->escape("$button_left,$button_top") . ")");
	}
	
	public function delete_files($user_hash)
	{
		$list= $this->input->post('list');
		$this->db->query("delete from code where code_hash in ($list)");
		echo $this->Files->get_file_list($user_hash);
	}
	
	public function new_project()
	{
		$user_name = $this->session->userdata('username');
		$user_hash = $this->Files->get_user_hash($user_name);
		$share_hash = md5($user_hash . "physgl" . time());
		$this->load->view('header',Array('login_form' => 'no','username' => $user_name));
		$this->load->view('codepage',Array(	"user_name" => $user_name,
											"user_hash" => $user_hash,
											"filename" => "untitled",
											"code" => "",
											"narrative" => "",
											"project_type" => "new",
											"share_hash" => $share_hash,
											"share" => false));
		$this->load->view('footer');
	}
	
	public function load_code($code_hash)
	{
		$user_name = $this->session->userdata('username');
		$share_hash = $this->Files->get_share_hash($code_hash);
		$user_hash = $this->Files->get_user_hash($user_name);
		$stuff = $this->Files->get_file_name_and_code($code_hash);
		$this->load->view('header',Array('login_form' => 'no','username' => $user_name));
		$this->load->view('codepage',Array(	"user_name" => $user_name,
											"user_hash" => $user_hash,
											"filename" => urldecode($stuff['filename']),
											"code" => $stuff['code'],
											"narrative" => $stuff['narrative'],
											"project_type" => "existing",
											"code_hash" => $code_hash,
											"share_hash" => $share_hash,
											"share" => false));
		$this->load->view('footer');
	}
	
	public function share($share_hash)
	{
		
		$user_name = $this->session->userdata('username');
		$user_hash = $this->Files->get_user_hash($user_name);
		$code_hash = $this->Files->get_code_hash($share_hash);
		if ($code_hash == 'none')
			{
				$this->load->view('header',Array('login_form' => 'none','username' => ''));
				$this->load->view('bad_share');	
				$this->load->view('footer');
				return;
			}
			
		$stuff = $this->Files->get_file_name_and_code($code_hash);
		$this->load->view('header',Array('login_form' => 'none','username' => ''));
		$this->load->view('codepage',Array(	"user_name" => $user_name,
											"user_hash" => $user_hash,
											"filename" => urldecode($stuff['filename']),
											"code" => $stuff['code'],
											"narrative" => $stuff['narrative'],
											"project_type" => "existing",
											"code_hash" => $code_hash,
											"share" => true,
											"share_hash" => $share_hash));
		$this->load->view('footer');
	}
	
	public function get_code_text($share_hash)
	{
		$code_hash = $this->Files->get_code_hash($share_hash);
		if ($code_hash == 'none')
			{
				echo "No code with that share link found.";
				return;
			}
		$stuff = $this->Files->get_file_name_and_code($code_hash);
		echo urldecode($stuff['code']);
	}
	
	
	public function create_account()
	{
		$this->load->view('header',Array('username'=>'','login_form' => 'none','home_link' => true));
		$this->load->view('create_account',Array('captcha_error' => ''));
		$this->load->view('footer');
	
	}
	
	public function incoming_account()
	{
		 $this->load->library('form_validation');	
		$this->form_validation->set_error_delimiters('<div id="error_message">', '</div>');		 
		 $this->form_validation->set_rules('email','Email','required|valid_email|min_length[5]|is_unique[user.user_name]|trim');
		 $this->form_validation->set_rules('password', 'Password', 'required|matches[password_confirm]|min_length[4]|trim');
		$this->form_validation->set_rules('password_confirm', 'Password Confirmation', 'required');
		
  		$privatekey = "6LeTGNsSAAAAAFKky10-X70ueZBJlqZvN5EfNvHx";
  		$resp = recaptcha_check_answer ($privatekey,
        									$_SERVER["REMOTE_ADDR"],
        									$_POST["recaptcha_challenge_field"],
                                			$_POST["recaptcha_response_field"]);

		 if ($this->form_validation->run() == TRUE && $resp->is_valid)
			{
				$user_name = $this->input->post("email");
				$password = $this->input->post("password");
				$this->Auth->create_new_account($user_name,$password);
				$this->start($user_name);
			}
		else
		{
			$this->load->view('header',Array('username'=>'','login_form' => 'none'));
			$this->load->view('create_account',Array('captcha_error' => 'Please redo the captcha puzzle.'));
			$this->load->view('footer');
		}
	}
	
	public function about()
	{
		$this->load->view('header',Array('username'=>'','login_form' => 'none','home_link' => true));
		$this->load->view('about');
		$this->load->view('footer');
	}
	
	public function input($private_key,$data_name,$data_value)
	{
		$user_hash = $this->Auth->get_user_hash_from_private_key($private_key);
		if ($user_hash == 'none')
			return('Invalid private key.');
		if (!$this->Auth->authenticate_userhash($user_hash))
			{
				echo "Can't authenticate user.";
				return;
			}
		if (strlen($data_name) > 50)
			{
				echo "Data name is too long.";
				return;
			}
		if (strlen($data_value) > 50)
			{
				echo "Data value is too long.";
				return;
			}
		$this->Data->insert_data($user_hash,$data_name,$data_value);
		echo "ok";		
	}
	
	
	public function get_data($public_data_key,$how,$data_name)
	{
		$user_hash = $this->Auth->get_user_hash_from_public_key($public_data_key);
		if ($user_hash == 'none')
			return('Invalid public key');
	
		if (!$this->Auth->authenticate_userhash($user_hash))
			{
				echo "Can't authenticate user.";
				return;
			}
		if (strlen($data_name) > 50)
			{
				echo "Data name is too long.";
				return;
			}
		if ($how == "last")
			echo $this->Data->get_data_last($user_hash,$data_name);
		if ($how == "all")
			echo $this->Data->get_data_all($user_hash,$data_name);
	}
	
	public function reset_account()
	{
		 $this->load->library('form_validation');	
		$this->form_validation->set_error_delimiters('<div id="error_message">', '</div>');		 
		 $this->form_validation->set_rules('email','Email','required|valid_email|min_length[5]|!is_unique[user.user_name]|trim');
		 $this->form_validation->set_rules('password', 'Password', 'required|matches[password_confirm]|min_length[4]|trim');
		$this->form_validation->set_rules('password_confirm', 'Password Confirmation', 'required');
		

		 if ($this->form_validation->run() == TRUE)
			{
				$user_name = $this->input->post("email");
				$password = $this->input->post("password");
				$this->Auth->reset_account($user_name,$password);
				$this->start($user_name);
			}
		else
		{
			$this->load->view('header',Array('username'=>'','login_form' => 'none'));
			$this->load->view('create_account',Array('captcha_error' => 'Please redo the captcha puzzle.'));
			$this->load->view('footer');
		}
	}
	
	public function cloud_save($name,$value)
	{
		$user_name = $this->session->userdata('username');
		$user_hash = $this->Files->get_user_hash($user_name);
		if ($this->Auth->authenticate_userhash($user_hash))
			{
				$this->Data->cloud_save($user_hash,$name,$value);
			}
	}
	
	public function cloud_load($public_key,$name)
	{
		echo $this->Data->cloud_load($public_key,$name);
	}
	
	public function share_prefs($share_hash)
	{
		$this->load->view('header',Array('username'=>'','login_form' => 'none'));
		$this->load->view('share_prefs_form',Array('share_hash' => $share_hash));
		$this->load->view('footer');
	}
	
	public function share_prefs_incoming($share_hash)
	{
		$user_name = $this->session->userdata('username');
		$user_hash = $this->Files->get_user_hash($user_name);
		if (!$this->Auth->user_owns_share($user_hash,$share_hash))
			{
				$this->load->view('header',Array('username'=>'','login_form' => 'none'));
				$this->load->view('file_manager',Array('msg' => 'You are not the owner of this project.'));
				$this->load->view('footer');
				return;
			}
		$show_code = trim($this->input->post('show_code'));
		$this->Code->set_share($share_hash,'show_code',$show_code);
		$code_hash = $this->Code->get_code_hash_from_share_hash($share_hash);
		if (!empty($code_hash))
			$this->load_code($code_hash);
		else
			{
				$this->load->view('header',Array('username'=>'','login_form' => 'none'));
				$this->load->view('file_manager',Array('msg' => 'There is no code to share yet.'));
				$this->load->view('footer');
			}
	}
	
	public function make_a_copy($share_hash)
	{
		$q = $this->db->query("select * from code where share_hash=" . $this->db->escape($share_hash));
		$row = $q->row_array();
		$share_hash = md5($row['user_hash'] . "physgl" . time());
		$code_hash = md5($row['user_hash'] . "physgl" . $row['code'] . time());
		$this->db->query("insert into code values(NULL," . 	$this->db->escape($row['user_hash']) . "," .
															$this->db->escape($row['file_name'] . " Copy") . "," .
															$this->db->escape($code_hash) . "," .
															$this->db->escape($share_hash) . "," .
															$this->db->escape($row['code']) . "," .
															$this->db->escape($row['narrative']) . ",now())");
		$this->load_code($code_hash);
	
	}
}