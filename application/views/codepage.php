<?php
//$user_name, $user_hash, $filename, $code, $code_hash, $share_hash, $share and $project_type are defined
include('config_paths.php');

?>


<?php
	$guest = true;
	
	if (!empty($user_hash) && $this->Auth->authenticate_userhash($user_hash) == true)
		$guest = false;
	
		
	if ($guest == true && $share == false)
		{
			$filename="";
			$code="";
			$code_hash="";
			$share_hash="";
			$share = true;
			$project_type = "guest";
			$user_hash="";
		}
?>

<div id="status"></div>

<div id="tabs-min">
<ul>
    <li><a href="#code">Work</a></li>
    <li><a href="#graphics">600p</a></li>
</ul>
<p/>

<?php
	if ($share == false)
		{
			echo "Project name: <input type=text name=filename id=filename size=50 value=\"$filename\">";
			echo "<button onClick=\"_PHYSGL_go_to_url('" . site_url("welcome/filemanager") . "');\">File Manager</button>";
			echo "<button onClick=\"_PHYSGL_share_link('" . $share_hash . "');\">Share</button>";
			echo "<span id=\"share\"></span>";
		}
		
	
?>



<p/>

<div id="code">

<div class="work_space" id="work_space">
    <div class="col1">
<textarea id="code_editor">
</textarea>
<br/>

<div id="run_button">
<button onclick="run('small');">Run</button>
<button onclick="run('large');">Run 600p</button>

<?php
if ($share == false)
	{
		echo "<button onclick=\"save_code();\">Save</button>";
		echo "<span id=\"save_update\"></span>";
	}
?>


</div>
<span id="error_message"></span>
<div id="graph"></div>
</div>

    <div class="col2" id="col2">
    <div id="pmode_small"></div>
    <div id="console"></div>
    </div>

</div>
</div>
	
<div id="graphics">
	<div id="pmode_large"></div>
</div>


</div>


<div id="dump"></div>


<script>

var renderer3d, scene3d, camera3d, light3d, controls3d;
var o3d = [];
var width=800, height=600;
var fps_rate = 25;
var myCodeMirror = CodeMirror.fromTextArea(code_editor,{lineNumbers: true,matchBrackets: true, onKeyEvent: key_pressed});
var start_time;
var stop_request = false;
var run_count = 0;
var project_type = '<?php echo $project_type; ?>';
var _PHYSGL_vector_scale = 1.0, _PHYSGL_arrow_thickness = 1.0, _PHYSGL_vector_label_scale = 1.0;
var _PHYSGL_graphs_loaded = false;
var _PHYSGL_graph_data;
var _PHYSGL_chart;
var _PHYSGL_axes_labels;
var _PHYSGL_console_border = false;
var _PHYSGL_orbit = {x: 0, y: 0, down: false, theta: 0.0, phi:0.0, r: 100, control_key: false, reset_y: false, cur_z: 0.0};

$(document).ready(function() {
    $("#tabs-min").tabs();
    $('#tabs-min').tabs({selected: 'code'});
  });
  
google.load("visualization", "1", {packages:["corechart"]});
google.setOnLoadCallback(_PHYSGL_graphs_loaded_callback);


$('#filename').keydown(function() {run_count = 0; project_type = 'new';});
initial_code = '<?php echo addslashes($code); ?>';
myCodeMirror.setValue(unescape(decodeURIComponent(initial_code)));
myCodeMirror.on('focus',function() {controls3d = undefined; });

//note: see codemirror.css file with width: 500px commented out for scroll bar's sake
$('.CodeMirror').resizable({
  resize: function() {
    myCodeMirror.setSize($(this).width(), $(this).height());
    myCodeMirror.refresh();
  }
});

    
function _PHYSGL_graphs_loaded_callback()
{
	_PHYSGL_graphs_loaded = true; 
	console.log('google charts loaded.');
}


function run_success(data)
{
	var pos;
	var target = 'new_name:';
	
	pos = data.indexOf(target);
	if (pos != -1)
		{
			$('#filename').val(decodeURIComponent(data.substr(pos+target.length)));
		}
}

function save_code()
{
	var code= myCodeMirror.getValue();
	var filename = encodeURI($('#filename').val());

	$.post('<?php echo base_url(); ?>index.php/welcome/save_code',
					{user_hash: '<?php echo $user_hash; ?>',share_hash: '<?php if (!empty($share_hash)) echo $share_hash;?>',filename: filename,code: encodeURI(code),run_count: run_count,project_type: project_type},
					run_success
			);
	$('#save_update').html('Saved.');
}

function key_pressed()
{
	$('#save_update').html('Not saved.');
}

function run(where)
{
	init_webgl(where);
	o3d = [];
	code= myCodeMirror.getValue();
	
	console.log(escape(code));
	
	filename = encodeURI($('#filename').val());
	
	$.post('<?php echo base_url(); ?>index.php/welcome/save_code',
					{user_hash: '<?php echo $user_hash; ?>',share_hash: '<?php if (!empty($share_hash)) echo $share_hash;?>',filename: filename,code: encodeURI(code),run_count: run_count,project_type: project_type},
					run_success
			);
			
	run_count++;

	console_clear();
	$('#console').css('border','0px');
	$('#save_update').html('Saved.');
	
	ret = _PHYSGL_error_check(code);
	if (ret == 'none')
		{
			$('#error_message').html('');
			code = js_preprocess(code);
			
			//$('#code_out').val(code);
			code = '<script>' + 'try { ' + code + '} catch(err) { _PHYSGL_runtime_error(err); }'+'</sc' +'ript>';
			$('#dump').html(code);
			
			if (where == 'large')
				$('#tabs-min').tabs({selected: 'graphics'});
				
			renderer3d.render( scene3d, camera3d );
		}
	else $('#error_message').html(ret);
	
	$(document).mousedown(orbit_init);
	$(document).mouseup(function() { _PHYSGL_orbit.down = false;});
	$(document).mousemove(orbit);
	_PHYSGL_orbit.r = Math.sqrt(camera3d.position.x*camera3d.position.x + camera3d.position.y*camera3d.position.y + camera3d.position.z*camera3d.position.z);
	 $(document).bind('keyup keydown', function(e){_PHYSGL_orbit.control_key = e.shiftKey; _PHYSGL_orbit.reset_y = true;} );
}

function orbit_init(e)
{
	_PHYSGL_orbit.x = e.pageX;
	_PHYSGL_orbit.y = e.pageY;
	
	_PHYSGL_orbit.theta = 0.0;
	_PHYSGL_orbit.down = true;
	_PHYSGL_orbit.reset_y = true;
}

function orbit(e)
{
	var dx,dy;
	
	if (_PHYSGL_orbit.down == false)
		return;
	
	dx = e.pageX - _PHYSGL_orbit.x;
	dy = e.pageY - _PHYSGL_orbit.y;
	
	if (_PHYSGL_orbit.reset_y)
		{
			_PHYSGL_orbit.cur_z = camera3d.position.z;
			_PHYSGL_orbit.reset_y = false;
			_PHYSGL_orbit.r = Math.sqrt(camera3d.position.x*camera3d.position.x + camera3d.position.y*camera3d.position.y + camera3d.position.z*camera3d.position.z);
		}

	if (_PHYSGL_orbit.control_key)
		{
			camera3d.position.z = _PHYSGL_orbit.cur_z+dy;
			renderer3d.render( scene3d, camera3d );
			return;
		}
			
	_PHYSGL_orbit.theta = dx/200.0;
	_PHYSGL_orbit.phi = dy/200.0;
	camera3d.position.x = _PHYSGL_orbit.r * Math.sin(_PHYSGL_orbit.theta);
	camera3d.position.y = _PHYSGL_orbit.r * Math.sin(_PHYSGL_orbit.phi);
	camera3d.position.z = _PHYSGL_orbit.r * Math.cos(_PHYSGL_orbit.theta) * Math.cos(_PHYSGL_orbit.phi);
	camera3d.lookAt( new THREE.Vector3(0,0,0) );
	renderer3d.render( scene3d, camera3d );


}
	

function init_webgl(where)
{
	renderer3d = new THREE.WebGLRenderer();
	scene3d = new THREE.Scene();
	
	if (where == 'small')
		{
			var container = $('#pmode_small');
			renderer3d.setSize( 500, 400 );
		}
	else
		{
			var container = $('#pmode_large');
			renderer3d.setSize( 800, 600 );
		}
		
	container.empty();
	container.append( renderer3d.domElement );
	
	
	camera3d = new THREE.PerspectiveCamera(50,width/height,0.1,10000);
	camera3d.position.set( 0, 0, 500);
	camera3d.lookAt( new THREE.Vector3(0,0,0) );
	scene3d.add( camera3d );
	_PHYSGL_orbit.r = 500;
	light3d = new THREE.DirectionalLight( 0xFFFFFF);
	light3d.position.set( 0, 1000, 1000 );
	scene3d.add( light3d );
	
	
}


function stop_run()
{
	stop_request = true;
}


function wait_for_fps()
{
	var now = new Date().getTime();
	if (now-start_time < fps_rate)
		requestAnimationFrame(wait_for_fps);
	else __animate_while();
	
}


function clear()
{
	renderer3d.render( scene3d, camera3d );
	for(i=0;i<o3d.length;i++)
		scene3d.remove(o3d[i]);
	o3d = [];
	start_time = new Date().getTime();
	wait_for_fps();
}

	
function _PHYSGL_runtime_error(err)
{
	var line = err.line;
	var msg = err.message;
	
	msg = msg.replace('variable','variable or function');
	$('#error_message').html(msg);
	console.error(msg);
}

function _PHYSGL_go_to_url(url)
{
	window.location=url;
}

function _PHYSGL_share_link(share_hash)
{
	var share_link = '<?php echo site_url() . "/welcome/share/"; ?>'+share_hash;

	
	if ($('#share').html().length == 0)
		$('#share').html("<input id='share_field' size='40' value='"+share_link+"'/>");
	else $('#share').html('');
	
}

function new_graph()
{
	var i;
	_PHYSGL_graph_data = new google.visualization.DataTable();
	for(i=0;i<arguments.length;i++)
		_PHYSGL_graph_data.addColumn('number',arguments[i]);
	_PHYSGL_chart = new google.visualization.ScatterChart(document.getElementById('graph'));
	_PHYSGL_axes_labels = { horiz: arguments[0], vert: arguments[1]};

}

function go_graph()
{
	var i;
	var xy=[];
	for(i=0;i<arguments.length;i++)
		{
			xy[i] = arguments[i];
		}
	_PHYSGL_graph_data.addRows([xy]);
	_PHYSGL_chart.draw(_PHYSGL_graph_data,{pointSize: 2,
											chartArea: {width: '80%', height: '80%'},
											legend: {position: 'in'},
											hAxis: {title: _PHYSGL_axes_labels.horiz},
											vAxis: {title: _PHYSGL_axes_labels.vert}})
	//
	console.log(_PHYSGL_graph_data);
}

</script>
