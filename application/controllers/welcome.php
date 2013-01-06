<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once('r.php');

class Welcome extends CI_Controller {

 public function __construct()
       {
            parent::__construct();
           $this->load->model('Files');
        	$this->load->model('Auth');
           $this->load->helper('form');
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
		$file_name = $this->input->post('filename');
		$user_hash = $this->input->post('user_hash');
		$share_hash = $this->input->post('share_hash');
		if (empty($share_hash))
			{
				echo "same_name";
				return;
			}
		if ($this->Auth->authenticate_userhash($user_hash) == false)
			return;
			
		$code = $this->input->post('code');
		if (strlen($code) > 10000)
			$code = substr($code,0,10000);
		$run_count = $this->input->post('run_count');
		$project_type = $this->input->post('project_type');
		
		if ($this->Files->check_exists($user_hash,$file_name) && $project_type == 'new')
			{	
				date_default_timezone_set($this->Files->get_time_zone());
				$file_name .= " " . date("Y-m-d H:i:s"); 
				$this->Files->save_code($user_hash,$file_name,$code);
				echo "new_name:$file_name";
				return;
			}
		else if ($this->Files->check_exists($user_hash,$file_name) && $run_count > 1)
				$this->Files->update_code($user_hash,$file_name,$code);
		else if ($this->Files->check_exists($user_hash,$file_name) && $project_type == 'existing')
				$this->Files->update_code($user_hash,$file_name,$code);
		else $this->Files->save_code($user_hash,$file_name,$code);
		echo "same_name";
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
											"project_type" => "existing",
											"code_hash" => $code_hash,
											"share" => true));
		$this->load->view('footer');
	}
	
	
	public function create_account()
	{
		$this->load->view('header',Array('username'=>'','login_form' => 'none'));
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
		
  		$privatekey = "--recapthca private key--";
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
		
	
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */
