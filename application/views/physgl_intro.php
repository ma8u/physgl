<?php

echo <<<EOT
<div id="intro_text">
PhysGL is an open-source, 3D graphics scripting langauge and programming environment.  It works right in your 
WebGL-enabled browser and allows you to easily experiment with 3D graphics, drawing and animation.  
<p/>
Here are some examples:
<a href="javascript:void(0);" onClick="example(0);">Red sphere</a>, 
<a href="javascript:void(0);" onClick="example(7);">Stick figure</a>,
<a href="javascript:void(0);" onClick="example(1);">Bouncing ball</a>,
<a href="javascript:void(0);" onClick="example(2);">Drawing</a>,
<a href="javascript:void(0);" onClick="example(3);">Ball on a ramp</a>,
<a href="javascript:void(0);" onClick="example(4);">Basic animation</a>,
<a href="javascript:void(0);" onClick="example(5);">Lots of balls</a>,
<a href="javascript:void(0);" onClick="example(6);">Block hits spring</a>

<p/>
PhysGL has been used to teach beginning programming, mathematics, and physics in the context of computer graphics
and animation.
</div>

EOT;
?>

<script>

function example(n)
{
	var code;
	
	switch(n)
		{
			case 0:
				code = 'draw_sphere(<0,0,0>,55,"red")';
				break;
			case 1: 
				code = 'theta=Pi/2.5%0Av0=40%0Apos=%3C-100,0,0%3E%0Avel=%3Cv0*cos(theta),v0*sin(theta),0%3E%0Ag=-9.8%0Aa=%3C0,g,0%3E%0At=0%0Adt=0.25%0Aframes_per_second(60)%0Acamera(%3C0,50,500%3E,%3C0,0,0%3E)%0Aset_vector_scale(2)%0Aset_vector_label_scale(2)%0A//draw_plane(-10,%22green%22,true)%0A%0Awhile%20t%3C%2035%20animate%0A%09draw_sphere(pos,10,%22blue%22)%0A%09draw_vector(pos,vel,%22red%22,%22v%22)%0A%09draw_vector(pos,a,%22blue%22,%22a%22)%0A%09draw_vector(pos,%3Cvel.x,0,0%3E,%22yellow%22,%22vx%22)%0A%09draw_vector(pos,%3C0,vel.y,0%3E,%22purple%22,%22vy%22)%0A%09%0A%09pos=pos+vel*dt+0.5*a*dt*dt%0A%09vel=vel+a*dt%0A%09%0A%09if%20pos.y%20%3C%200%20and%20vel.y%20%3C%200%20then%0A%20%20%20%20%20%20vel.y%20=%20-vel.y%0A%20%20%20%20end%0A%20%20%20%20if%20pos.x%20%3E%20100%20and%20vel.x%20%3E%200%20then%0A%20%20%20%20%09vel.x%20=-vel.x%0A%09end%0A%09t=t+dt%0Aend%0A';
				break;
			case 2:
				code = 'camera(%3C0,0,100%3E,%3C0,0,0%3E)%0Apos=%3C1,1,1%3E%0Avel=%3C3,3,2%3E%0Adt=.1%0Aa=%3C5,5,5%3E%0Adraw_vector(pos,a,%22red%22,%22test%22)%0Adraw_vector(pos,2*a,%22red%22,%22a%22)%0Adraw_sphere(%3C0,-20,0%3E,15,%22yellow%22)%0Apos%20=%20pos+vel*dt+0.5*a*dt*dt%0Adraw_sphere(pos,5,%22orange%22)%0Adraw_vector(pos,vel,%22red%22,%22v%22)%0Adraw_vector(pos,2*a,%22red%22,%22a%22)%0Adraw_box(%3C-35,-25,-50%3E,%3C-20,-20,50%3E,%22green%22)%0Aprintxy(-5*pos,%22hello%22,20,%22green%22)%0A';
				break;
			case 3: 
				code = 'camera(%3C0,0,35%3E,%3C0,0,0%3E)%0A%0Afunction%20f(x)%0A%20%20%20%20return(A*(1+tanh(B*x)))%0Aend%0A%0Afunction%20fp(x)%0A%20%20return(A*B*sech(B*x)%5E2)%0Aend%0A%0Afunction%20fpp(x)%0A%20%20return(-2.0*A*B%5E2*sech(B*x)%5E2*tanh(B*x))%0Aend%0A%0AA=2%0AB=.3%0Aplotxy(f,-15,15,0.5,0,%22yellow%22,0.2,true)%0Apos=%3C-10,0,0%3E%0Avel%20=%20%3C6,0,0%3E%0At%20=%200%0Adt%20=%200.05%0Ag=9.8%0Am=0.5%0Aset_vector_scale(2)%0Aset_vector_thickness(.2)%0Aset_vector_label_scale(0.5)%0Anew_graph(\'Time\',\'Energy\',\'KE\',\'PE\')%0Awhile%20t%20%3C%205%20animate%0A%20%20ax%20=%20-fp(pos.x)%20*%20(fpp(pos.x)*vel.x%5E2+g)/(1+fp(pos.x)%5E2)%0A%20%20ay%20=%20fpp(pos.x)*vel.x%5E2+fp(pos.x)*ax%0A%20%20a%20=%20%3Cax,ay,0%3E%0A%20%20Nx%20=%20m%20*ax%0A%20%20Ny%20=%20m*(g+ay)%0A%20%20N=%3Cm*ax,m*(g+ay),0%3E%0A%0A%20%20draw_sphere(pos,1,%22red%22)%0A%20%20draw_vector(pos,vel,%22blue%22,%22v%22)%0A%20%20draw_vector(pos,a,%22green%22,%22a%22)%0A%20%20draw_vector(pos,N,%22purple%22,%22N%22)%0A%0A%0A%20%20pos%20=%20pos%20+%20vel*dt%20+%200.5*a*dt%5E2%0A%20%20vel%20=%20vel%20+%20a*dt%0A%20%20%0A%20%20KE=0.5*m*vel*vel%0A%20%20PE=m*g*pos.y%0A%20%20E%20=%20KE+PE%0A%20%20go_graph(t,E,KE,PE)%0A%20%20t=t+dt%0Aend%0A%0A';
				break;
			case 4:
				code = 'x=-200%0Awhile%20x%20%3C%20200%20animate%0A%09pos=%3Cx,0,0%3E%0A%09draw_sphere(pos,25,%22red%22)%0A%09x=x+10%0Aend ';
				break;
			case 5:
				code = 'camera(%3C200,0,300%3E,%3C0,0,0%3E)%0Axx=rnd(-200,200)%0Ayy=rnd(-200,200)%0Azz=rnd(-200,200)%0Afor%20i=200,1%20do%0A%09x=rnd(-200,200)%0A%09y=rnd(-200,200)%0A%09z=rnd(-200,200)%0A%09draw_sphere(%3Cx,y,z%3E,15,%22yellow%22,true)%0A%20%20%09draw_line(%3Cx,y,z%3E,%3Cxx,yy,zz%3E,%22red%22,2,true)%0A%20%20%09xx=x%0A%20%20%09yy=y%0A%20%20%09zz=z%0Aend%0A%0Aframes_per_second(60)%0At%20=%200%0Awhile%20t%20%3C%2020%20animate%0A%09x=500*cos(2*Pi*t)%0A%09y=500*Math.tan(2*Pi*t)%0A%09z=500*sin(2*Pi*t)%0A%09camera(%3Cx,y,z%3E,%3C0,0,0%3E)%0A%09light(%3Cx,0,z%3E)%0A%09t=t+0.01%0Aend';
				break;
			case 6:
				code ='camera(%3C0,0,250%3E,%3C0,0,0%3E)%0Apos%20=%20%3C-100,0,0%3E%0Avel=%3C35,0,0%3E%0Aa=%3C0,0,0%3E%0Ak=.5%0As0=20%0Asx%20=%20s0%0At=0%0Am=1%0Adt%20=%200.1%0Anew_graph(%22time%22,%22energy%22)%0Awhile%20t%3C10%20animate%0A%09draw_cube(pos,50,\'red\')%0A%09pos%20=pos+vel*dt+0.5*a*dt%5E2%0A%09vel=vel+a*dt%0A%09a=%3C0,0,0%3E%0A%09sx=s0%0A%09if%20pos.x%20%3E%20s0%20then%0A%20%20%09%09a=%3C-k*(pos.x-s0)/m,0,0%3E%0A%20%20%09%09sx=pos.x%0A%20%09end%0A%09if%20pos.x%20%3C%20-100%20and%20vel.x%20%3C%200%20then%0A%20%20%09%09vel.x%20=%20-vel.x%0A%20%20%09end%0A%09dx%20=%20sx-s0%0A%09draw_hspring(sx,150,0,10,\'blue\')%0A%09E=0.5*m*vel*vel+0.5*k*dx%5E2%0A%09go_graph(t,E)%0A%09t=t+0.1%0Aend';
				break;
			case 7: 
				code = 'draw_sphere%28%3C0%2C0%2C0%3E%2C20%2C%22orange%22%29%0A//body%0Adraw_line%28%3C0%2C0%2C0%3E%2C%3C0%2C-100%2C0%3E%2C%22yellow%22%2C5%29%0A//arms%0Adraw_line%28%3C0%2C-25%2C0%3E%2C%3C-40%2C-65%2C0%3E%2C%22yellow%22%2C5%29%0Adraw_line%28%3C0%2C-25%2C0%3E%2C%3C40%2C-65%2C0%3E%2C%22yellow%22%2C5%29%0A//legs%0Adraw_line%28%3C0%2C-100%2C0%3E%2C%3C-50%2C-140%2C0%3E%2C%22yellow%22%2C5%29%0Adraw_line%28%3C0%2C-100%2C0%3E%2C%3C50%2C-140%2C0%3E%2C%22yellow%22%2C5%29%0A';
				break;
		}
		
	myCodeMirror.setValue(unescape(code));
}
</script>