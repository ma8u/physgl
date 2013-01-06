function _PHYSGL_add_object(obj,persist)
{
	if (persist == undefined || persist == false)
		o3d.push(obj);
	scene3d.add(obj);
}

function draw_sphere()//(pos,radius,color)
{
	var pos = arguments[0], radius = arguments[1], color = arguments[2], persist = arguments[3];
	
	var ret = _PHYSGL_draw_sphere(pos[0],pos[1],pos[2],radius,_PHYSGL_hcolor(color));
	_PHYSGL_add_object(ret,persist);
}

function draw_box()
{
	var corner1 = arguments[0], corner2 = arguments[1], color = arguments[2], persist = arguments[3];
	var ret = _PHYSGL_draw_box(corner1[0],corner1[1],corner1[2],corner2[0],corner2[1],corner2[2],_PHYSGL_hcolor(color));
	_PHYSGL_add_object(ret,persist);
}

function draw_cube()
{
	var center = arguments[0], size = arguments[1], color = arguments[2], persist = arguments[3];
	var ret = _PHYSGL_draw_box(center[0] - size/2,center[1] - size/2,center[2] - size/2,center[0] + size/2,center[1] + size/2,center[2] + size/2,_PHYSGL_hcolor(color));
	_PHYSGL_add_object(ret,persist);
}

function draw_vector()
{
	var tail = arguments[0], vec = arguments[1], color = arguments[2], label = arguments[3], persist = arguments[4];
	var ret = _PHYSGL_draw_vector(tail[0],tail[1],tail[2],vec[0],vec[1],vec[2],_PHYSGL_hcolor(color),label)
	_PHYSGL_add_object(ret,persist);
}


function draw_line()
{
	var tail = arguments[0];
	var head = arguments[1];
	var color = arguments[2];
	var thickness = arguments[3];
	var persist = arguments[4];
	
	var ret = _PHYSGL_draw_line(tail[0],tail[1],tail[2],head[0],head[1],head[2],_PHYSGL_hcolor(color),thickness);
	_PHYSGL_add_object(ret,persist);
}

function draw_hspring()
{
	var x0=arguments[0];
	var x1=arguments[1];
	var y0=arguments[2];
	var R=arguments[3];
	var color=arguments[4];
	var persist=arguments[5];
	
	var ret = _PHYSGL_draw_hspring(x0,x1,y0,R,_PHYSGL_hcolor(color),persist);
	_PHYSGL_add_object(ret,persist);
}

function draw_plane()
{
	var normal= arguments[0];
	var z = arguments[1];
	var color = arguments[2];
	var size = arguments[3];
	var persist = arguments[4];
	
	var ret = _PHYSGL_draw_plane(normal,z,_PHYSGL_hcolor(color),size);
	_PHYSGL_add_object(ret,persist);
}

function printxy()
{
	var pos = arguments[0], str = arguments[1], size = arguments[2], color = arguments[3], persist = arguments[4];
	console.log(str);
	var ret = _PHYSGL_dtext(pos[0],pos[1],pos[2],str,size,_PHYSGL_hcolor(color));
	_PHYSGL_add_object(ret,persist);
}

function set_vector_scale(n)
{
	_PHYSGL_vector_scale = n;
}


function set_vector_thickness(n)
{
	_PHYSGL_arrow_thickness = n;
}

function set_vector_label_scale(n)
{
	_PHYSGL_vector_label_scale = n;
}


function plotxy()
{
	var f = arguments[0];
	var xmin = arguments[1], xmax=arguments[2];
	var dx = arguments[3];
	var z = arguments[4];
	var color = arguments[5];
	var thickness = arguments[6];
	var persist = arguments[7];
	
	if (xmin > xmax)
		{
			var t = xmin;
			xmin = xmax;
			xmax = t;
		}
	dx = Math.abs(dx);
	
	var x = xmin;
	var oldx, oldy;
	
	oldx = x;
	oldy = f(x);
	
	x += dx;
	
	while(x <= xmax)
	{
		y = f(x);
		draw_line([oldx,oldy,z],[x,y,z],color,thickness,persist);
		oldx = x;
		oldy = y;
		x += dx;
	}
}


function rnd()
{
	if (arguments.count == 0)
		return(Math.random());
	var low = arguments[0];
	var high = arguments[1];
	var makeint = arguments[2];
	var n = low + (high-low)*Math.random();
	if (makeint == false)
		return(n);
	return(Math.floor(n));
}

function draw_axes()
{
	var scale=arguments[0], persist = arguments[1];
	var a = _PHYSGL_vector_label_scale;
	var b = _PHYSGL_arrow_thickness;
	set_vector_label_scale(scale*5);
	set_vector_thickness(scale*3);
	draw_vector([0,0,0],[scale*100,0,0],"red","x",persist);
	draw_vector([0,0,0],[0,scale*100,0],"green","y",persist);
	draw_vector([0,0,0],[0,0,scale*100],"blue","z",persist);
	_PHYSGL_vector_label_scale = a;
	_PHYSGL_arrow_thickness = b;
}

function frame_delta(n)
{
	return(_PHYSGL_frame_count % n == 0);
}
		
	
	