// chessboard.js
/******************************************************************************
******************************************************************************/

function SetMenuButtonCaption( new_title )
{
	var e = document.getElementsByTagName('button')[0];

	if ((e.innerHTML == 'Menu') || (new_title != 'Switch Style')) {
		e.innerHTML = new_title;
	}
}


function AddEvent( element, event_name, event_function )
{
	if (element.attachEvent) {             // Internet Explorer
		element.attachEvent(
			"on" + event_name,
			function(){ event_function.call(element); }
		);
	}
	else if (element.addEventListener) {   // Rest of the world
		element.addEventListener(
			event_name,
			event_function,
			false
		);
	}
}


AddEvent( window, 'load', function(){

	// do stuff, after the page has loaded

	AddEvent(
		document.getElementById('menu'),
		'mouseover',
		function() { SetMenuButtonCaption('Switch Style'); }
	);

}); // onLoad


/* EOF */