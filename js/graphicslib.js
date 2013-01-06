function gtest()
{
	console.log('graphicslib');
}

function get_quaternion(x1,y1,z1,x2,y2,z2)
{
	var dx = x2-x1;
	var dy = y2-y1;
	var dz = z2-z1;
	var r = Math.sqrt(dx*dx+dy*dy+dz*dz);
	
	orig = new THREE.Vector3(0,r,0);
	orig.normalize();
	align = new THREE.Vector3(dx,dy,dz);
	align.normalize();
	axis = new THREE.Vector3();
	axis.cross(orig,align);
	axis.normalize();
	angle = Math.acos(align.dot(orig));

	q = new THREE.Quaternion();
	q.setFromAxisAngle(axis,angle);
	return(q);
}

function _PHYSGL_draw_box(x1,y1,z1,x2,y2,z2,color)
{
	var dx = x2-x1;
	var dy = y2-y1;
	var dz = z2-z1;
	var material = new THREE.MeshLambertMaterial({ color: color});
	var box = new THREE.Mesh(new THREE.CubeGeometry(Math.abs(dx),Math.abs(dy),Math.abs(dz),1,1,1),material);
	box.position.set(x1+dx/2,y1+dy/2,z1+dz/2);
	//box.scale.set(dx,dy,dz);
	
	return(box);	

}

function _PHYSGL_draw_line(x1,y1,z1,x2,y2,z2,color,thickness)
 {
	var result = new THREE.Object3D();
	var dx = x2-x1;
	var dy = y2-y1;
	var dz = z2-z1;
	var r = Math.sqrt(dx*dx+dy*dy+dz*dz);
	
	var material = new THREE.MeshLambertMaterial({ color: color});
	var cylinder = new THREE.Mesh(new THREE.CylinderGeometry(thickness,thickness,r,10,10,false),material);
	cylinder.position.set(0,r/2,0);
	result.add(cylinder);
	
	if (dx ==0 && dz == 0 && dy < 0)
		result.rotation.z = Math.PI;
	else
		{
			orig = new THREE.Vector3(0,r,0);
			orig.normalize();
			align = new THREE.Vector3(dx,dy,dz);
			align.normalize();
			axis = new THREE.Vector3();
			axis.cross(orig,align);
			axis.normalize();
			angle = Math.acos(align.dot(orig));
			q = new THREE.Quaternion();
			q.setFromAxisAngle(axis,angle);
			result.useQuaternion = true;
			result.quaternion = q;
		}
	
	result.position.set(x1,y1,z1);
	return(result);
 }

function _PHYSGL_draw_arrow(x1,y1,z1,x2,y2,z2,color)
 {
	var result = new THREE.Object3D();
	
	var dx = x2-x1;
	var dy = y2-y1;
	var dz = z2-z1;
	var r = Math.sqrt(dx*dx+dy*dy+dz*dz);
	
	var material = new THREE.MeshLambertMaterial({ color: color});
	var cylinder = new THREE.Mesh(new THREE.CylinderGeometry(_PHYSGL_arrow_thickness,_PHYSGL_arrow_thickness,r,10,10,false),material);
	cylinder.position.set(0,r/2,0);
	result.add(cylinder);
	
	var cylinder = new THREE.Mesh(new THREE.CylinderGeometry(0,4*_PHYSGL_arrow_thickness,0.5*r,10,10,false),material);
	cylinder.position.set(0,r,0);
	result.add(cylinder);
	
	if (dx ==0 && dz == 0 && dy < 0)
		result.rotation.z = Math.PI;
	else
		{
			orig = new THREE.Vector3(0,r,0);
			orig.normalize();
			align = new THREE.Vector3(dx,dy,dz);
			align.normalize();
			axis = new THREE.Vector3();
			axis.cross(orig,align);
			axis.normalize();
			angle = Math.acos(align.dot(orig));
			q = new THREE.Quaternion();
			q.setFromAxisAngle(axis,angle);
			result.useQuaternion = true;
			result.quaternion = q;
		}
	
	result.position.set(x1,y1,z1);
	return(result);
 }
 
 function _PHYSGL_draw_vector(tx,ty,tz,vx,vy,vz,color,label)
 {
	var result = new THREE.Object3D();

	result.add(_PHYSGL_draw_arrow(tx,ty,tz,tx+vx*_PHYSGL_vector_scale,ty+vy*_PHYSGL_vector_scale,tz+vz*_PHYSGL_vector_scale,color));
	result.add(_PHYSGL_dtext(tx+1.2*_PHYSGL_vector_scale*vx,ty+1.2*_PHYSGL_vector_scale*vy,tz+vz+0.2*vz,label,5*_PHYSGL_vector_label_scale,color));
	return(result);
 }
 
	 
function _PHYSGL_draw_sphere(x,y,z,radius,color)
{
	var material = new THREE.MeshLambertMaterial( { color: color} );
	var sphere = new THREE.Mesh( new THREE.SphereGeometry(radius, 30, 20 ), material);
	sphere.position.set(x,y,z);
	return(sphere);
}

function dc(x,y,z,L,color)
{
	var material = new THREE.MeshLambertMaterial( { color: color} );
	var cube = new THREE.Mesh( new THREE.CubeGeometry( L, L, L ), material);
	cube.position.set(x,y,z);
	return(cube);
}

function _PHYSGL_draw_plane(normal,z,color,side)
{
	var material = new THREE.MeshLambertMaterial( { color: color} );
	var plane = new THREE.Mesh( new THREE.PlaneGeometry( side, side ), material);
	var orig, align, axis, angle, q;
	
	orig = new THREE.Vector3(0,0,1);
	align = new THREE.Vector3(normal[0],normal[1],normal[2]);
	align.normalize();
	axis = new THREE.Vector3();
	axis.cross(orig,align);
	axis.normalize();
	angle = Math.acos(align.dot(orig));
	q = new THREE.Quaternion();
	q.setFromAxisAngle(axis,angle);
	plane.useQuaternion = true;
	plane.quaternion = q;
	
	align.multiplyScalar(z);
	plane.position.x = align.dot(new THREE.Vector3(1,0,0));
	plane.position.y = align.dot(new THREE.Vector3(0,1,0));
	plane.position.z = align.dot(new THREE.Vector3(0,0,1));
	return(plane);

}

function _PHYSGL_dtext(x,y,z,str,size,color)
{
		var result = new THREE.Object3D();
    	var material = new THREE.MeshLambertMaterial( { color: color } );
    	textWhy = new THREE.TextGeometry( str, { size: size,height: 0.15, curveSegments: 6, font: "helvetiker", weight: "normal", style: "normal"});
		text = new THREE.Mesh(textWhy,material);
		text.position.set(x,y,z);
		result.add(text);
		return(result);
}

function _PHYSGL_draw_hspring(x0,x1,y0,R,color,persist)
{
	var numPoints = 1000;
	var result = new THREE.Object3D();
	var points = [];
	var i,x,dx;
	
	if (x1 < x0)
		{	
			t = x1;
			x1 = x0;
			x0 = t;
		}
	
	dx = (x1-x0)/2000.0;
	if (dx < 0.01)
		dx = 0.01;
		
	f = 10.0/(x1-x0+1);
	for(x=x0-x0;x<=x1-x0;x += dx)
		points.push(new THREE.Vector3(x+x0,y0+R/2+R*Math.cos(2*Math.PI*f*x),R+R*Math.sin(2*Math.PI*f*x)));

	spline = new THREE.SplineCurve3(points);
	
	var material = new THREE.LineBasicMaterial({color: color,linewidth: 5, linejoin: 'round'});
	//var material = new THREE.MeshLambertMaterial( { color: 0xff0000} );
	//var material = new THREE.MeshBasicMaterial( { color: 0xff0000} );
	var geometry = new THREE.Geometry();
	var splinePoints = spline.getPoints(numPoints);
	
	for(var i = 0; i < splinePoints.length; i++)
	{
		geometry.vertices.push(splinePoints[i]);  
	}
	
	result = new THREE.Line(geometry, material);
	return(result);
}

function _PHYSGL_hcolor(text_color)
{	
	switch($.trim(text_color.toLowerCase()))
		{
			case 'black':return(0x000000);
			case 'navy':return(0x000080);
			case 'darkblue':return(0x00008b);
			case 'mediumblue':return(0x0000cd);
			case 'blue':return(0x0000ff);
			case 'darkgreen':return(0x006400);
			case 'green':return(0x008000);
			case 'teal':return(0x008080);
			case 'darkcyan':return(0x008b8b);
			case 'deepskyblue':return(0x00bfff);
			case 'darkturquoise':return(0x00ced1);
			case 'mediumspringgreen':return(0x00fa9a);
			case 'lime':return(0x00ff00);
			case 'springgreen':return(0x00ff7f);
			case 'aqua':return(0x00ffff);
			case 'cyan':return(0x00ffff);
			case 'midnightblue':return(0x191970);
			case 'dodgerblue':return(0x1e90ff);
			case 'lightseagreen':return(0x20b2aa);
			case 'forestgreen':return(0x228b22);
			case 'seagreen':return(0x2e8b57);
			case 'darkslategray':return(0x2f4f4f);
			case 'darkslategrey':return(0x2f4f4f);
			case 'limegreen':return(0x32cd32);
			case 'mediumseagreen':return(0x3cb371);
			case 'turquoise':return(0x40e0d0);
			case 'royalblue':return(0x4169e1);
			case 'steelblue':return(0x4682b4);
			case 'darkslateblue':return(0x483d8b);
			case 'mediumturquoise':return(0x48d1cc);
			case 'indigo':return(0x4b0082);
			case 'darkolivegreen':return(0x556b2f);
			case 'cadetblue':return(0x5f9ea0);
			case 'cornflowerblue':return(0x6495ed);
			case 'mediumaquamarine':return(0x66cdaa);
			case 'dimgray':return(0x696969);
			case 'dimgrey':return(0x696969);
			case 'slateblue':return(0x6a5acd);
			case 'olivedrab':return(0x6b8e23);
			case 'slategray':return(0x708090);
			case 'slategrey':return(0x708090);
			case 'lightslategray':return(0x778899);
			case 'lightslategrey':return(0x778899);
			case 'mediumslateblue':return(0x7b68ee);
			case 'lawngreen':return(0x7cfc00);
			case 'chartreuse':return(0x7fff00);
			case 'aquamarine':return(0x7fffd4);
			case 'maroon':return(0x800000);
			case 'purple':return(0x800080);
			case 'olive':return(0x808000);
			case 'gray':return(0x808080);
			case 'grey':return(0x808080);
			case 'skyblue':return(0x87ceeb);
			case 'lightskyblue':return(0x87cefa);
			case 'blueviolet':return(0x8a2be2);
			case 'darkred':return(0x8b0000);
			case 'darkmagenta':return(0x8b008b);
			case 'saddlebrown':return(0x8b4513);
			case 'darkseagreen':return(0x8fbc8f);
			case 'lightgreen':return(0x90ee90);
			case 'mediumpurple':return(0x9370d8);
			case 'darkviolet':return(0x9400d3);
			case 'palegreen':return(0x98fb98);
			case 'darkorchid':return(0x9932cc);
			case 'yellowgreen':return(0x9acd32);
			case 'sienna':return(0xa0522d);
			case 'brown':return(0xa52a2a);
			case 'darkgray':return(0xa9a9a9);
			case 'darkgrey':return(0xa9a9a9);
			case 'lightblue':return(0xadd8e6);
			case 'greenyellow':return(0xadff2f);
			case 'paleturquoise':return(0xafeeee);
			case 'lightsteelblue':return(0xb0c4de);
			case 'powderblue':return(0xb0e0e6);
			case 'firebrick':return(0xb22222);
			case 'darkgoldenrod':return(0xb8860b);
			case 'mediumorchid':return(0xba55d3);
			case 'rosybrown':return(0xbc8f8f);
			case 'darkkhaki':return(0xbdb76b);
			case 'silver':return(0xc0c0c0);
			case 'mediumvioletred':return(0xc71585);
			case 'indianred':return(0xcd5c5c);
			case 'peru':return(0xcd853f);
			case 'chocolate':return(0xd2691e);
			case 'tan':return(0xd2b48c);
			case 'lightgray':return(0xd3d3d3);
			case 'lightgrey':return(0xd3d3d3);
			case 'palevioletred':return(0xd87093);
			case 'thistle':return(0xd8bfd8);
			case 'orchid':return(0xda70d6);
			case 'goldenrod':return(0xdaa520);
			case 'crimson':return(0xdc143c);
			case 'gainsboro':return(0xdcdcdc);
			case 'plum':return(0xdda0dd);
			case 'burlywood':return(0xdeb887);
			case 'lightcyan':return(0xe0ffff);
			case 'lavender':return(0xe6e6fa);
			case 'darksalmon':return(0xe9967a);
			case 'violet':return(0xee82ee);
			case 'palegoldenrod':return(0xeee8aa);
			case 'lightcoral':return(0xf08080);
			case 'khaki':return(0xf0e68c);
			case 'aliceblue':return(0xf0f8ff);
			case 'honeydew':return(0xf0fff0);
			case 'azure':return(0xf0ffff);
			case 'sandybrown':return(0xf4a460);
			case 'wheat':return(0xf5deb3);
			case 'beige':return(0xf5f5dc);
			case 'whitesmoke':return(0xf5f5f5);
			case 'mintcream':return(0xf5fffa);
			case 'ghostwhite':return(0xf8f8ff);
			case 'salmon':return(0xfa8072);
			case 'antiquewhite':return(0xfaebd7);
			case 'linen':return(0xfaf0e6);
			case 'lightgoldenrodyellow':return(0xfafad2);
			case 'oldlace':return(0xfdf5e6);
			case 'red':return(0xff0000);
			case 'fuchsia':return(0xff00ff);
			case 'magenta':return(0xff00ff);
			case 'deeppink':return(0xff1493);
			case 'oranger':return(0xff4500);
			case 'tomato':return(0xff6347);
			case 'hotpink':return(0xff69b4);
			case 'coral':return(0xff7f50);
			case 'darkorange':return(0xff8c00);
			case 'lightsalmon':return(0xffa07a);
			case 'orange':return(0xffa500);
			case 'lightpink':return(0xffb6c1);
			case 'pink':return(0xffc0cb);
			case 'gold':return(0xffd700);
			case 'peachpuff':return(0xffdab9);
			case 'navajowhite':return(0xffdead);
			case 'moccasin':return(0xffe4b5);
			case 'bisque':return(0xffe4c4);
			case 'mistyrose':return(0xffe4e1);
			case 'blanchedalmond':return(0xffebcd);
			case 'papayawhip':return(0xffefd5);
			case 'lavenderblush':return(0xfff0f5);
			case 'seashell':return(0xfff5ee);
			case 'cornsilk':return(0xfff8dc);
			case 'lemonchiffon':return(0xfffacd);
			case 'floralwhite':return(0xfffaf0);
			case 'snow':return(0xfffafa);
			case 'yellow':return(0xffff00);
			case 'lightyellow':return(0xffffe0);
			case 'ivory':return(0xfffff0);
			case 'white':return(0xffffff);
			default: return('0x' + $.trim(color));
		}
}
