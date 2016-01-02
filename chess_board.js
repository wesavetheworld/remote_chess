// chessboard.js
/******************************************************************************
* Compatibility Helpers
******************************************************************************/

/**
 * AddEvent()
 */
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


/******************************************************************************
* UI Functions
******************************************************************************/

/**
 * setMenuButtonCaption() - Change the text on the menu button
 * When switching styles, the name of the new style is shown as caption of the
 * menu button. If no parameter is given, the button is reset to it's original
 * state, showing "Menu".
 */
var activeTimeout = false;   // Global variable holding timer handle
function setMenuButtonCaption( new_title )
{
	if (new_title == undefined) {
		new_title = 'Menu';
	}

	var e = document.getElementsByTagName('button');

	if (e[0] == undefined) {
		document.title = 'SNAFU';
		return;
	}
	// Prevent the mouse over event of the menu from setting the caption to
	// "Switch Style", when the button was NOT showing "Menu" before.
	// (I.e. the current style name was displayed on the button)
	if ((e[0].innerHTML == 'Menu') || (new_title != 'Switch Style')) {
		e[0].innerHTML = new_title;
	}

	// (Re)set a timer to revert the caption back to "Menu" after a while
	if (activeTimeout) window.clearTimeout( activeTimeout );
	activeTimeout = window.setTimeout( setMenuButtonCaption, 5000 );
}


/******************************************************************************
* MAIN PROGRAM
******************************************************************************/


// Add event handler to be executed after the page is fully loaded

AddEvent( window, 'load', function(){

	// Remove classes used to hide JS-only links to "no-script clients"
	var e = document.getElementsByClassName('js_required');
	for( i = 0 ; i < e.length ; i++ ) {
		var s = e[i].className;
		s = s.replace( ' js_required', '' );
		s = s.replace( 'js_required ', '' );
		s = s.replace( 'js_required',  '' );
		e[i].className = s;
	}

	// Scroll history down
	e = document.getElementsByClassName('scrolling');
	if (e[0] != undefined) {
		e[0].scrollBy(0, 10000000);
	}

	AddEvent(
		document.getElementById('menu'),
		'mouseover',
		function() { setMenuButtonCaption('Switch Style'); }
	);

}); // onLoad


/* EOF */