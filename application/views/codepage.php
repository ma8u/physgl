<?php
//$user_name, $user_hash, $filename, $code, $narrative, $code_hash, $share_hash, $share and $project_type are defined
include('config_paths.php');


if (empty($code_hash)) 
	$code_hash=""; 

$narrative = urldecode($narrative);
if (!empty($user_hash) && $user_hash != 'none')
	{
		$keys = $this->Auth->get_data_keys($user_hash);
		$private_data_key = $keys['private_key'];
		$public_data_key = $keys['public_key'];
		$data_link = site_url("welcome/input/$private_data_key/data-name/data-value");
		$get_data_link = site_url("welcome/get_data/");
		$cloud_save = site_url("welcome/cloud_save/");
		$cloud_load = site_url("welcome/cloud_load/$public_data_key/data-name");
	}
else
	{
		$data_link = $get_data_link = $data_id = $private_data_key = $public_data_key = $cloud_load = "You must create an account and be logged on for this.";
	}
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
		
		$code_prefix = php_parse($code,$this);

        function php_parse($code,$xthis)
        {
			//#get_all_data(js-time-var,js-value-var,var-name,public-key)
			$key = "//#get_all_data";
			$lines = explode("\n",urldecode($code));
			$prefix = "";
			foreach($lines as $line)
					{
						if (substr($line,0,strlen($key)) == $key)
							{
									$pos = strpos($line,"(");
									$pos1 = strpos($line,")");
									$args = substr($line,$pos+1,$pos1-$pos-1);
									$a = explode(",",$args);
									$a[0] = trim($a[0],"'");//time-var
									$a[1] = trim($a[1],"'");//value-var
									$a[2] = trim($a[2],"'");//variable-name
									$a[3] = trim($a[3],"'");//public-key
									$user_hash = $xthis->Auth->get_user_hash_from_public_key($a[3]);
									$data = $xthis->Data->get_data_all($user_hash,$a[2]);

									$prefix .= "var " . $a[0] . "= [";
									$items = explode(",",$data);
									for($i=1;$i<count($items);$i += 2)
											$prefix .= $items[$i] . ",";
									$prefix = rtrim($prefix,",");
									$prefix .= "]; ";

									$prefix .= "var " . $a[1] . "= [";
									$items = explode(",",$data);
									for($i=0;$i<count($items);$i += 2)
											$prefix .= $items[$i] . ",";
									$prefix = rtrim($prefix,",");
									$prefix .= "]; ";
							}
				}

			return($prefix);
        }

?>

<span id="top_matter">
<div id="status"></div>

<p/>
<?php
	if ($share === false)
		{
			echo "Project name: <input type=text name=filename id=filename size=50 value=\"$filename\">";
			echo "<button onClick=\"_PHYSGL_go_to_url('" . site_url("welcome/filemanager") . "');\">File Manager</button>";
			echo "<button onClick=\"_PHYSGL_go_to_url('" . site_url("welcome/make_a_copy/$share_hash") . "');\">Make a copy</button>";
			echo "<button onClick=\"_PHYSGL_share_link('" . $share_hash . "');\">Share Link</button>";
			echo "<span id=\"share\"></span>";
		}
		
	
?>




<?php
echo "<div id=\"narrative\">$narrative</div>";
if ($share == false)
	{
		echo "<div id=\"narrative_controls\"></div>";
		echo "<textarea id=\"edit_narrative\" rows=6 cols=100>$narrative</textarea>";
	}
?>

</span>
	
	
	<div id="code_dialog" title='Code'></div>
	<div id="xy_dialog" title='XY-graph'></div>
	
	<div id="graphics_dialog" title='Graphics'></div>
	<div id="console_dialog" title='Console'></div>
	
	<div id="run_button"></div>

	

<div id="dump"></div>
<div id="taskbar"></div>


<script>


var renderer3d, scene3d, camera3d, light3d, controls3d;
var o3d = [];
var width=800, height=600;
var fps_rate = 25;
var myCodeMirror;
var start_time;
var _PHYSGL_stop_request = false;
var _PHYSGL_in_animation_loop = false;
var _PHYSGL_pause = false;
var run_count = 0;
var project_type = '<?php echo $project_type; ?>';
var _PHYSGL_vector_scale = 3.0, _PHYSGL_arrow_thickness = 1.0, _PHYSGL_vector_label_scale = 1.0;
var _PHYSGL_graphs_loaded = false;
var _PHYSGL_graph_data;
var _PHYSGL_chart;
var _PHYSGL_axes_labels;
var _PHYSGL_console_border = false;
var _PHYSGL_orbit = {x: 0, y: 0, down: false, theta: 0.0, phi:0.0, r: 100, control_key: false, reset_y: false, cur_z: 0.0, down_count: 0, theta_offset: 0, phi_offset: 0};
var _PHYSGL_spine_data = [];
var _PHYSGL_mouse = {x:0, y:0};
var _PHYSGL_data = {value: 0,time_stamp: 0, status: 'idle', access: 0, last_returned_time_stamp: 0};
var _PHYSGL_cloud = {last_access: 0};
var _PHYSGL_interact = '#interact_small';
var _PHYSGL_axes_range = {xmax: 'auto', xmin: 'auto', ymin: 'auto', ymax: 'auto'};
var _PHYSGL_single_step = false;
var _PHYSGL_textures; // initialized in init_webgl()
var _PHYSGL_rotate = {x:0, y:0, z:0};
var _PHYSGL_clear_skip;
var _PHYSGL_frame_count;
var _PHYSGL_kept_objects = [];

$(document).ready(function() {
   
   <?php $layout = $this->Files->get_layout($code_hash); ?>
  
	$('#code_dialog').dialog({resizable: true,<?php echo $layout['code_dialog']; ?>}).dialogExtend(
																									{
																										"minimizable": true,"minimizable" : true,"collapsable" : true,"closable" : false,
																										"restore": function(a,b) { myCodeMirror.refresh(); run_button_to_code();   },
																										"minimize": function() { run_button_to_top(); }
																									});	
	
    $('#code_dialog').dialog('option','resize',function() { myCodeMirror.setSize($(this).width(),$(this).height());});
   
   	$('#code_dialog').dialog('option','drag',function(event) { var offset = $('#run_button').height() + $('.ui-dialog-title').height() + $('.ui-dialog-titlebar').height() + 6; $('#run_button').offset({top: $(this).offset().top-offset,left: $(this).offset().left})});
	
	
	 
    $('#graphics_dialog').dialog({resizable: true,<?php echo $layout['graphics_dialog']; ?>}).dialogExtend({"minimizable": true,"minimizable" : true,"collapsable" : true,"closable" : false});
    $('#graphics_dialog').dialog('option','resize',function() { $('#pmode_small').height($(this).height()); $('#pmode_small').width($(this).width())});
     
    $('#xy_dialog').dialog({resizeable: true,<?php echo $layout['xy_dialog']; ?>}).dialogExtend({"minimizable": true,"minimizable" : true,"collapsable" : true,"closable" : false});
   	$('#xy_dialog').dialog('option','resize',function() { $('#graph').height($(this).height()); $('#graph').width($(this).width())});
   	
    $('#console_dialog').dialog({resizeable: true,<?php echo $layout['console_dialog']; ?>}).dialogExtend({"minimizable": true,"minimizable" : true,"collapsable" : true,"closable" : false});
    $('#console_dialog').dialog('option','resize',function() {  $('#console_dialog').height($(this).height()); $('#console_dialog').width($(this).width())});
    
	<?php echo $layout['console_css']; ?>
	<?php echo $layout['minimized']; ?>
   
    render_narrative();
  	desktop(['edit','graphics','xy','console']);
    show_narrative(false);
	programming_buttons('stopped');
	myCodeMirror = CodeMirror.fromTextArea(code_editor,{lineNumbers: true,matchBrackets: true, onKeyEvent: key_pressed_in_code,<?php echo $layout['code_mirror']; ?>});
	<?php echo $layout['code_mirror_css']; ?>
	initial_code = '<?php echo addslashes($code); ?>';
	myCodeMirror.setValue(unescape(decodeURIComponent(initial_code)));
    myCodeMirror.refresh();    
    <?php echo $layout['pmode_small']; ?>
    
    var offset = $('#run_button').height() + $('.ui-dialog-title').height() + $('.ui-dialog-titlebar').height() + 6;
    
    if (<?php echo $layout['code_minimized']; ?> == false)
    		run_button_to_code();
    else run_button_to_top();

     
   
    
   
	
	
<?php
if ($share == true && $this->Code->get_share($share_hash,'show_code') == 'no')
	echo 'myCodeMirror.getWrapperElement().style.display="none";';
?>


});//end of onLoad

  
google.load("visualization", "1", {packages:["corechart"]});
google.setOnLoadCallback(_PHYSGL_graphs_loaded_callback);





$('#filename').keydown(function() {run_count = 0; project_type = 'new';});

function key_pressed_in_code()
{
	project_type = 'existing';
}

function run_button_to_top()
{
	var offset = 7*($('#top_matter').height() + $('#top-bar').height())/2;
	$('#run_button').offset({top: offset,left: 10});
}

function run_button_to_code()
{
	var offset = $('#run_button').height() + $('.ui-dialog-title').height() + $('.ui-dialog-titlebar').height() + 6;
	$('#run_button').offset({top:$('#code_dialog').offset().top-offset});
	$('#run_button').offset({left: $('#code_dialog').offset().left});
}

function desktop(list)
{
	var edit_text = '<div id="error_message"></div>' +
					'<textarea id="code_editor"></textarea>';
	var dt = {edit: edit_text, xy: '<div id="graph"></div>', graphics: '<div id="interact_small"></div><div id="pmode_small"></div>', console: '<div id="console"></div>'};
    
	$('#code_dialog').html(dt.edit);
	$('#graphics_dialog').html(dt.graphics);
	
	$('#xy_dialog').html(dt.xy);
	$('#console_dialog').html(dt.console);
}


function _PHYSGL_graphs_loaded_callback()
{
	_PHYSGL_graphs_loaded = true; 
}


function run_success(data)
{
	var pos;
	var a = data.split('___');
	
	for(i=0;i<a.length-1;i++)
		{
			if (a[i] == '_PHYSGL_new_name')
				$('#filename').val(decodeURIComponent(a[i+1]));
			if (a[i] == '_PHYSGL_new_share_hash')
				window.location=a[i+1];
		}
}

function save_code()
{
	var code= myCodeMirror.getValue();
	var filename = encodeURI($('#filename').val());
	var narrative = encodeURI($('#edit_narrative').val());

	$.post('<?php echo base_url(); ?>index.php/welcome/save_code',
					{	
						user_hash: '<?php echo $user_hash; ?>',
						share_hash: '<?php if (!empty($share_hash)) echo $share_hash;?>',
						filename: filename,
						narrative: narrative, 
						code: encodeURI(code),
						run_count: run_count,
						project_type: project_type,
						share: <?php echo $share ? '\'true\'' : '\'false\''; ?>
						},
					run_success
			);
	save_layout();
	$('#save_update').html('Saved.');
}

function key_pressed()
{
	$('#save_update').html('Not saved.');
	_PHYSGL_orbit.down = false;
}

function run(where,single_step)
{
	init_webgl(where);
	o3d = [];
	code = myCodeMirror.getValue();
	var dest, width, height;
	
	_PHYSGL_pause = false;
	_PHYSGL_single_step = false;
	if (single_step == true)
		{
			_PHYSGL_single_step == true;
			_PHYSGL_pause = true;
		}
			
	_PHYSGL_stop_request = false;
	if (_PHYSGL_in_animation_loop == true)
		_PHYSGL_stop_request = true;
	_PHYSGL_orbit.down_count = 0;
	_PHYSGL_spline_data = [];
	_PHYSGL_data.access = 0;
    _PHYSGL_data.status = 'idle';
    _PHYSGL_vector_scale = 3.0, 
    _PHYSGL_arrow_thickness = 1.0, 
    _PHYSGL_vector_label_scale = 1.0;
    _PHYSGL_frame_count = 0;
    _PHYSGL_kept_objects = [];
    _PHYSGL_clear_skip = 0;
    _PHYSGL_rotate = {origin: [0,0,0], axis: new THREE.Vector3(), angle: 0};
	console.log(escape(code));
	
	var filename = encodeURI($('#filename').val());
	var narrative = encodeURI($('#edit_narrative').val());
	
	save_code();
			
	run_count++;
	project_type = 'existing';

	clear_graph();
	clear_console();
	clear_sliders();
	programming_buttons('running');
	
	$('#console').css('border','0px');
	$('#save_update').html('Saved.');
	
	dest = '#pmode_small';
	width = 500;
	height = 400;
	ret = _PHYSGL_error_check(code);
	if (ret == 'none')
		{
			if (where == 'small')
				_PHYSGL_interact = '#interact_small';
			else _PHYSGL_interact = '#interact_large';
			$('#error_message').html('');
			code = js_preprocess(code);
			console.log(code);
			//return;
			
			//$('#code_out').val(code);
 			code = '<script>' + '<?php echo $code_prefix . "  "; ?>' + 'try { ' + code + '} catch(err) { _PHYSGL_runtime_error(err); }'+'</sc' +'ript>';			
 			$('#dump').html(code);
			
			if (where == 'large')
				{
					dest = '#pmode_large';
					$('#tabs-min').tabs({selected: 'graphics'});
					width = 800;
					height = 600;
				}
				
			renderer3d.render( scene3d, camera3d );
		}
	else $('#error_message').html(ret);
	
	$(dest).mousedown(orbit_init);
	$(dest).mouseup(function() { _PHYSGL_orbit.down = false;});
	$(dest).mousemove(orbit);
	$(document).bind('keyup keydown', function(e){_PHYSGL_orbit.control_key = e.shiftKey; _PHYSGL_orbit.reset_y = true;} );
	$(dest).mousemove(function (e) { _PHYSGL_mouse.x=-(width/2-(e.pageX-$(this).offset().left)); _PHYSGL_mouse.y=(height/2-(e.pageY-$(this).offset().top));});
	
	_PHYSGL_orbit.r = Math.sqrt(camera3d.position.x*camera3d.position.x + camera3d.position.y*camera3d.position.y + camera3d.position.z*camera3d.position.z);
}

function save_layout(share_hash)
{
	var code_left, code_top, code_width, code_height;
	var graphics_left, graphics_top, graphics_width, graphics_height;
	var console_left, console_top, console_width, console_height;
	var xy_left, xy_top, xy_width, xy_height;
	var button_left, button_top;
	
	code_left = $('#code_dialog').offset().left;
	code_top = $('#code_dialog').offset().top;
	code_width = $('#code_dialog').width();
	code_height = $('#code_dialog').height();
	
	graphics_left = $('#graphics_dialog').offset().left;
	graphics_top = $('#graphics_dialog').offset().top;
	graphics_width = $('#graphics_dialog').width();
	graphics_height = $('#graphics_dialog').height();
	
	console_left = $('#console_dialog').offset().left;
	console_top = $('#console_dialog').offset().top;
	console_width = $('#console_dialog').width();
	console_height = $('#console_dialog').height();
	
	xy_left = $('#xy_dialog').offset().left;
	xy_top = $('#xy_dialog').offset().top;
	xy_width = $('#xy_dialog').width();
	xy_height = $('#xy_dialog').height();
	
	button_left = $('#run_button').offset().left;
	button_top = $('#run_button').offset().top;
	
	$.post('<?php echo base_url(); ?>index.php/welcome/save_layout',
					{
						code_hash: '<?php echo $code_hash;?>',
						code_left: code_left, code_top: code_top, code_width: code_width,code_height: code_height,
						graphics_left: graphics_left, graphics_top: graphics_top, graphics_width: graphics_width,graphics_height: graphics_height,
						console_left: console_left, console_top: console_top, console_width: console_width,console_height: console_height,
						xy_left: xy_left, xy_top: xy_top, xy_width: xy_width,xy_height: xy_height,
						button_left: button_left, button_top: button_top 
					}
			);	
}

function orbit_init(e)
{
	_PHYSGL_orbit.x = e.pageX;
	_PHYSGL_orbit.y = e.pageY;
	_PHYSGL_orbit.down = true;
	
	if (_PHYSGL_orbit.down_count == 0)
		{
			_PHYSGL_orbit.theta = 0.0;
			_PHYSGL_orbit.reset_y = true;
			_PHYSGL_orbit.theta_offset = 0;
			_PHYSGL_orbit.phi_offset = 0;
			_PHYSGL_orbit.lx = 0;
			_PHYSGL_orbit.ly = 0;
			_PHYSGL_orbit.lz = 0;
		}
		
	if (_PHYSGL_orbit.down_count)
		{
			_PHYSGL_orbit.theta_offset = _PHYSGL_orbit.theta;
			_PHYSGL_orbit.phi_offset = _PHYSGL_orbit.phi;
		}
	_PHYSGL_orbit.down_count++;
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
			var normal = new THREE.Vector3(camera3d.position.x,camera3d.position.y,camera3d.position.z);
			normal.normalize();
			var movex = dy*normal.dot(new THREE.Vector3(1,0,0));
			var movey = dy*normal.dot(new THREE.Vector3(0,1,0));
			var movez = dy*normal.dot(new THREE.Vector3(0,0,1));
			
			camera3d.position.x += movex;
			camera3d.position.y += movey; 
			camera3d.position.z += movez;
			
			_PHYSGL_orbit.lx += movex;
			_PHYSGL_orbit.ly += movey;
			_PHYSGL_orbit.lz += movez;
	
			
			_PHYSGL_orbit.x = e.pageX;
			_PHYSGL_orbit.y = e.pageY;
			
			renderer3d.render( scene3d, camera3d );
			return;
		}
			
	_PHYSGL_orbit.theta = -dx/200.0 + _PHYSGL_orbit.theta_offset;
	_PHYSGL_orbit.phi = dy/200.0 + _PHYSGL_orbit.phi_offset;
	camera3d.position.x = _PHYSGL_orbit.r * Math.sin(_PHYSGL_orbit.theta);
	camera3d.position.y = _PHYSGL_orbit.r * Math.sin(_PHYSGL_orbit.phi);
	camera3d.position.z = _PHYSGL_orbit.r * Math.cos(_PHYSGL_orbit.theta) * Math.cos(_PHYSGL_orbit.phi);
	/*
	light3d.position.x = 100 * camera3d.position.x;
	light3d.position.y = 100 * camera3d.position.y;
	light3d.position.z = 100 * camera3d.position.z;
	*/
	camera3d.lookAt( new THREE.Vector3(0,0,0) );
	
	renderer3d.render( scene3d, camera3d );


}
	

function init_webgl(where)
{
	renderer3d = new THREE.WebGLRenderer();
	scene3d = new THREE.Scene();
	var width = $('#pmode_small').width();
	var height= $('#pmode_small').height();
	
	if (where == 'small')
		{
			var container = $('#pmode_small');
			renderer3d.setSize( width, height );
		}
	else
		{
			var container = $('#pmode_large');
			renderer3d.setSize( 800, 600 );
		}
	container.empty();
	container.append( renderer3d.domElement );
	
	
	camera3d = new THREE.PerspectiveCamera(45,width/height,0.1,1000);
	camera3d.position.set( 0, 50, 150);
	camera3d.lookAt( new THREE.Vector3(0,0,0) );
	scene3d.add( camera3d );
	_PHYSGL_orbit.r = 500;
	
	light3d = new THREE.HemisphereLight(0xffffff,0x000000,1);
	scene3d.add(light3d);
	
	//light3d = new THREE.DirectionalLight( 0xFFFFFF,1.0);
	//light3d.position.set( 0, 1000, 1000 )
	//scene3d.add( light3d );

	
	renderer3d.setClearColor( 0x000000, 1 );
	
	_PHYSGL_textures = {brick: THREE.ImageUtils.loadTexture("<?php echo base_url(); ?>textures/bricks.jpg"),
						metal: THREE.ImageUtils.loadTexture("<?php echo base_url(); ?>textures/metal.jpg"),
						rope: THREE.ImageUtils.loadTexture("<?php echo base_url(); ?>textures/rope.jpg"),
						crate: THREE.ImageUtils.loadTexture("<?php echo base_url(); ?>textures/crate.jpg"),
						water: THREE.ImageUtils.loadTexture("<?php echo base_url(); ?>textures/water.jpg"),
						grass: THREE.ImageUtils.loadTexture("<?php echo base_url(); ?>textures/grass.jpg"),
						checker01: THREE.ImageUtils.loadTexture("<?php echo base_url(); ?>textures/checker01.jpg")
						};
	
	
}


function _PHYSGL_stop_run()
{
	_PHYSGL_stop_request = true;
	programming_buttons('stopped');
}

function _PHYSGL_pause_run()
{
	if (_PHYSGL_pause)
		clear();
	_PHYSGL_pause = !_PHYSGL_pause;
	_PHYSGL_single_step = false;
	pause_button_toggle();
}


function wait_for_fps()
{
	var now = new Date().getTime();

	if  (_PHYSGL_stop_request == true)
		{
			_PHYSGL_stop_request == false;
			_PHYSGL_in_animation_loop = false;
			programming_buttons('stopped');
			return;
		}
		
	if (now-start_time < fps_rate)
		requestAnimationFrame(wait_for_fps);
	else __animate_while();
	
}


function clear()
{
	renderer3d.render( scene3d, camera3d );
	if (_PHYSGL_clear_skip && _PHYSGL_frame_count % _PHYSGL_clear_skip == 0)
		{
			for(i=0;i<o3d.length;i++)
				_PHYSGL_kept_objects.push(o3d[i]);
		}
	for(i=0;i<o3d.length;i++)
		scene3d.remove(o3d[i]);
	o3d = [];
	
	for(i=0;i<_PHYSGL_kept_objects.length;i++)
		scene3d.add(_PHYSGL_kept_objects[i]);
	
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
	var share_pref = '<?php echo site_url() . "/welcome/share_prefs/"; ?>'+share_hash;

	
	if ($('#share').html().length == 0)
		$('#share').html("<input id='share_field' size='50' value='"+share_link+"'/>");
	else $('#share').html('');
	
}

function new_graph()
{
	var i;
	var width, height;
	
	$('#xy_dialog').html("<div id=\"graph\"></div>");
	width = $('#xy_dialog').width();
	height = $('#xy_dialog').height();
	$('#graph').css('width',width+'px');
	$('#graph').css('height',height+'px');
	

	_PHYSGL_graph_data = new google.visualization.DataTable();
	for(i=0;i<arguments.length;i++)
		_PHYSGL_graph_data.addColumn('number',arguments[i]);
	_PHYSGL_chart = new google.visualization.ScatterChart(document.getElementById('graph'));
	_PHYSGL_axes_labels = { horiz: arguments[0], vert: arguments[1]};
	
	show_graph();
}

function go_graph()
{
	var i;
	var xy=[];
	for(i=0;i<arguments.length;i++)
		xy[i] = arguments[i];
	_PHYSGL_graph_data.addRows([xy]);
	_PHYSGL_chart.draw(_PHYSGL_graph_data,{pointSize: 2,
											chartArea: {width: '80%', height: '80%'},
											legend: {position: 'in'},
											fontSize: 12,
											hAxis: {title: _PHYSGL_axes_labels.horiz,viewWindow: {max: _PHYSGL_axes_range.xmax,min: _PHYSGL_axes_range.xmin} },
											vAxis: {title: _PHYSGL_axes_labels.vert, viewWindow: {max: _PHYSGL_axes_range.ymax,min: _PHYSGL_axes_range.ymin}} });
}

function new_multi_graph()
{
	var i;
	var width, height, h;
	var d;
	
	d = "";
	for(i=0;i<arguments.length-1;i++)
		d = d + "<div id=\"graph"+i+"\"></div>";
	
	$('#xy_dialog').html(d);
	width = $('#xy_dialog').width();
	height = $('#xy_dialog').height();
	
	_PHYSGL_graph_data = [];
	_PHYSGL_chart = [];
	_PHYSGL_axes_labels = [];
	for(i=0;i<arguments.length-1;i++)
		{
			_PHYSGL_graph_data[i] = new google.visualization.DataTable();
			_PHYSGL_graph_data[i].addColumn('number',arguments[0]);
			_PHYSGL_graph_data[i].addColumn('number',arguments[i+1]);
			_PHYSGL_chart[i] = new google.visualization.ScatterChart(document.getElementById('graph'+i));
			_PHYSGL_axes_labels[i] = { horiz: arguments[0], vert: arguments[i+1]};
		}
	
	show_graph();
}

function go_multi_graph()
{
	var i;
	var xy=[];

	for(i=0;i<arguments.length-1;i++)
		{
			xy[0] = arguments[0]
			xy[1] = arguments[i+1]
			_PHYSGL_graph_data[i].addRows([xy]);
			
			_PHYSGL_chart[i].draw(_PHYSGL_graph_data[i],{pointSize: 2,
											chartArea: {width: '80%', height: '65%'},
											legend: {position: 'none'},
											fontSize: 14,
											hAxis: {title: _PHYSGL_axes_labels[i].horiz,viewWindow: {max: _PHYSGL_axes_range.xmax,min: _PHYSGL_axes_range.xmin} },
											vAxis: {title: _PHYSGL_axes_labels[i].vert, viewWindow: {max: _PHYSGL_axes_range.ymax,min: _PHYSGL_axes_range.ymin}} });
			
		}
	show_graph();
}


function bar_graph()
{
	var i;
	var width, height;
	var arr = [];
	
	width = $('#xy_dialog').width();
	height = $('#xy_dialog').height();
	$('#graph').css('width',width+'px');
	$('#graph').css('height',height+'px');
	
	for(i=0;i<arguments.length;i += 2)
		arr.push([arguments[i],arguments[i+1]]);
			
	_PHYSGL_graph_data = google.visualization.arrayToDataTable(arr);
	_PHYSGL_chart = new google.visualization.ColumnChart(document.getElementById('graph'));
	_PHYSGL_chart.draw(_PHYSGL_graph_data,{
					pointSize: 2,
					legend: {position: 'none'},
					hAxis: {title: arguments[0],viewWindow: {max: _PHYSGL_axes_range.xmax,min: _PHYSGL_axes_range.xmin} },
					vAxis: {title: arguments[1],viewWindow: {max: _PHYSGL_axes_range.ymax,min: _PHYSGL_axes_range.ymin}}
					
					
					});
		
    show_graph();
}




function go_graph_array(x_axis,y_axis)
{
	var i;
	var xy=[];
	for(i=0;i<x_axis.length;i++)
		{
			xy[i] = [x_axis[i],y_axis[i]];
		}
	_PHYSGL_graph_data.addRows(xy);
	_PHYSGL_chart.draw(_PHYSGL_graph_data,{pointSize: 2,
											chartArea: {width: '80%', height: '80%'},
											legend: {position: 'in'},
											hAxis: {title: _PHYSGL_axes_labels.horiz,viewWindow: {max: _PHYSGL_axes_range.xmax,min: _PHYSGL_axes_range.xmin} },
											vAxis: {title: _PHYSGL_axes_labels.vert, viewWindow: {max: _PHYSGL_axes_range.ymax,min: _PHYSGL_axes_range.ymin}} });
}

function set_xrange(xmin,xmax)
{
	_PHYSGL_axes_range.xmin = xmin;
	_PHYSGL_axes_range.xmax = xmax;
}

function set_yrange(ymin,ymax)
{
	_PHYSGL_axes_range.ymin = ymin;
	_PHYSGL_axes_range.ymax = ymax;
}

function show_narrative(flag)
{
	if (flag == true)
		{
			$('#narrative_controls').html('<a href="javascript:void(0);" id="narrative_controls" onClick="show_narrative(false);">Hide</a> | <a href="javascript:void(0);" onClick="render_narrative();">Render</a>');
			$('#edit_narrative').css('visibility','visible');
			$('#edit_narrative').css('display','inline');
		}
	else
		{
			$('#narrative_controls').html('<a href="javascript:void(0);" id="narrative_controls" onClick="show_narrative(true);">Edit narrative</a>');
			$('#edit_narrative').css('visibility','hidden');
			$('#edit_narrative').css('display','none');
		}

}

function render_narrative()
{
	var n;
	
	n = $('#edit_narrative').val();
	$('#narrative').html(n);
	MathJax.Hub.Queue(["Typeset",MathJax.Hub,"narrative"]);
}

//state is running or stopped
function programming_buttons(state)
{
	var b;
	
	if (state == 'stopped')
		{
			b = '<button id="button_style" onclick="run(\'small\',false);">Run</button>' +
				'<button id="button_style" onclick="run(\'small\',true);">Step</button>';
<?php 
			if ($share == false)
				{
					echo "b = b + '<button id=\"button_style\" onclick=\"save_code();\">Save</button>';";
				}
?>
		}
	else
		{
			b = '<button id="button_style" onclick="_PHYSGL_stop_run();">Stop</button>';
			b = b + '<button id="button_style" onclick="_PHYSGL_pause_run();">Pause</button>';
			b = b + '<button id="button_style" onclick="take_step();">Step</button>';
		}		
	$('#run_button').html(b);
}

function take_step()
{
	_PHYSGL_single_step = true;
	_PHYSGL_pause = false;
	clear();
}

function pause_button_toggle()
{
	var b;
	if (_PHYSGL_pause)
		{
			b = '<button id="button_style" onclick="_PHYSGL_stop_run();">Stop</button>';
			b = b + '<button id="button_style" onclick="_PHYSGL_pause_run();">Resume</button>';
			b = b + '<button id="button_style" onclick="take_step();">Step</button>';
		}
	else
		{
			b = '<button id="button_style" onclick="_PHYSGL_stop_run();">Stop</button>';
			b = b + '<button id="button_style" onclick="_PHYSGL_pause_run();">Pause</button>';
			b = b + '<button id="button_style" onclick="take_step();">Step</button>';
		}
	$('#run_button').html(b);
}


</script>

<?php

echo<<<EOT
<script>



function get_private_data_key()
{
	return('$private_data_key');
}

function get_public_data_key()
{
	return('$public_data_key');
}

function get_private_data_link()
{
	return('$data_link');
}

function get_cloud_load_link()
{
	return('$cloud_load');
}

function get_data_value(name,data_id)
{
	var d = new Date();
	var cur = d.getTime();
	
	if (_PHYSGL_data.status == 'idle' & cur > _PHYSGL_data.access + 300000)
		{
			$.get('$get_data_link/'+data_id+'/last/' + name,returned_last_data);
			_PHYSGL_data.status = 'waiting';	
			_PHYSGL_data.access = d.getTime();
		}
	if (_PHYSGL_data.status == 'ready')
		{
			_PHYSGL_data.status = 'idle';
			_PHYSGL_data.last_returned_time_stamp = _PHYSGL_data.time_stamp;
			return(new Array(_PHYSGL_data.value,_PHYSGL_data.time_stamp));
		}
	return(false);
}

function returned_last_data(data)
{
	var n = data.split(',');
	var i;

	_PHYSGL_data.value = parseInt(n[0]);
	_PHYSGL_data.time_stamp = parseInt(n[1]);

	_PHYSGL_data.status = 'ready';
}

function cloud_save(name,value)
{

EOT;

if ($guest == false)
echo<<<EOT
	var d = new Date();
	var cur = d.getTime();
	
	if (cur > _PHYSGL_cloud.last_access + 300000)
		{
			$.get('$cloud_save/'+name+'/' + value);
			_PHYSGL_cloud.last_access  = cur;
		}
EOT;
else echo<<<EOT
	writeln('cloud_save is only allowed for users logged on to their own account.')
EOT;
echo<<<EOT

}
</script>
EOT;
?>
