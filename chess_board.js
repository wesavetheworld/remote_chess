// chessboard.js
/******************************************************************************
******************************************************************************/

function AddEvent( element, event_name, event_function )
{
	if (element.attachEvent) {             // Internet Explorer
		element.attachEvent( "on" + event_name, function() {event_function.call(element);} );
	}
	else if (element.addEventListener) {   // Rest of the world
		element.addEventListener(event_name, event_function, false);
	}
}

AddEvent( window, 'load', function () {

	// do stuff, after the page has loaded

}); // onLoad


/* EOF */