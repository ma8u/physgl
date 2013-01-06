<?php

$user_name = $this->session->userdata('username');
$user_hash = $this->Files->get_user_hash($user_name);
if ($user_hash == 'none')
	{
		echo "You are not a registered user.  ";
		anchor("welcome/","Return");
		return;
	}

echo "<h2>Your files</h2>";	
echo anchor("welcome/new_project/","New project");
echo "<p/>";
echo "<div id=\"file_list\">";
echo $this->Files->get_file_list($user_hash);
echo "</div>";

?>

<div id="buttons"></div>

<script>

var delete_list = [];
Array.prototype.remove = function(from, to) {
  var rest = this.slice((to || from) + 1 || this.length);
  this.length = from < 0 ? this.length + from : from;
  return this.push.apply(this, rest);
};


function add_to_delete(code_hash)
{
	var pos;
	
	pos = delete_list.indexOf(code_hash);
	if ( pos == -1)
		delete_list.push("'" + code_hash + "'");
	else delete_list.remove(pos);
	if (delete_list.length)
		$('#buttons').html('<p/><button onclick=\'delete_code();\'>Delete</button>');
	else $('#buttons').html('');
}

function delete_code()
{
	list = delete_list.join(',');
	$.post('<?php echo base_url(); ?>index.php/welcome/delete_files/<?php echo $user_hash;?>',
					{list: list},
					function(data) {console.log(data); $('#file_list').html(data);}
			);
}

</script>