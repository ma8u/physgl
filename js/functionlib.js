function writeln()
{
	var current;
	var i;
	current = $('#console').html();
	
	if (_PHYSGL_console_border == false)
		$('#console').css('border','1px solid #aaaaaa');
	
	for(i=0;i<arguments.length;i++)
		current = current + arguments[i];
	current = current + '<br/>';
	$('#console').html(current);
	$('#console').scrollTop($('#console')[0].scrollHeight);
}

function write()
{
	var current;
	current = $('#console').html();
	
	if (_PHYSGL_console_border == false)
		$('#console').css('border','1px solid #aaaaaa');
	
	
	for(i=0;i<arguments.length;i++)
		current = current + arguments[i];
	$('#console').html(current);
	$('#console').scrollTop($('#console')[0].scrollHeight);
}

function console_clear()
{
	$('#console').html('');
}



function frames_per_second(n)
{
	fps_rate = 1000/n;
}

function camera(pos,look_at)
{
	camera3d.position.set( pos[0], pos[1], pos[2]);
	camera3d.lookAt( new THREE.Vector3(look_at[0],look_at[1],look_at[2]) );
	renderer3d.render( scene3d, camera3d );
}

function light(pos)
{
	light3d.position.set( pos[0], pos[1], pos[2] );
}
