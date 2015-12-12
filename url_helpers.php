<?php /* url_helpers.php */ if (!isset($VERSION)) die('Include only.');
/******************************************************************************
* REMOTE CHESS - URL MANAGING, updating of GET parameters
******************************************************************************/

/**
 * new_board()
 */
function get_parameter( $parameter_name, $default_value = '' )
{
	if (isset($_GET[$parameter_name])) {
		$ret = $_GET[ $parameter_name ];
	} else {
		$ret = $default_value;
	}

	return $ret;
}


/**
 * decode_board()
 * encode_board()
 */
function decode_board( $board_coded )
{
	$ret = new_board();
	$length = strlen( $board_coded );

	for( $i = 0 ; $i < $length ; $i+=2 ) {
		$piece = substr( $board_coded, $i, 1 );
		$code = substr( $board_coded, $i + 1, 1 );

		$p = strpos( LOCATION_CODES, $code );
		$col = $p % 8;
		$row = ($p - $col) / 8;
		$ret[$row][$col] = $piece;
	}

	return $ret;
}

function encode_board( $board )
{
	$ret = '';

	for( $row = 0 ; $row < 8 ; $row++ ) {
		for( $col = 0 ; $col < 8 ; $col++ ) {
			if ($board[$row][$col] != '') {
				$ret .= $board[$row][$col];
				$ret .= substr(
					LOCATION_CODES,
					$row * 8 + $col,
					1
				);
			}
		}
	}

	return $ret;
}


/**
 * encode_move() - return a compressed code describing a move
 */
function encode_move( $f_row, $f_col, $t_row, $t_col )
{
	$f_code = substr(
		LOCATION_CODES,
		$f_row * 8 + $f_col,
		1
	);

	$t_code = substr(
		LOCATION_CODES,
		$t_row * 8 + $t_col,
		1
	);

	return $f_code.$t_code;
}


/**
 * update_href() - Nice URL formatting
 * The full game state is being stored in the URL as HTTP GET parameters,
 * this function helps keeping the URL well formatted.
 */
function update_href( $current_link = '', $parameter_name = '', $add_value = '' )
{
	// INITIALIZE

	// Remove HTML entities and replace with single ascii character
	$current_link = str_replace( '&amp;', '&', $current_link );

	// If no link was given, we start from scratch
	if ($current_link == '') {
		$current_link
		= 'http://'
		. $_SERVER['HTTP_HOST']
		. $_SERVER['PHP_SELF']
		;

		$file_name = basename( $current_link );
		$current_link = substr( $current_link, 0, -strlen($file_name) );
	}


	// ANALYZE: Extract GET parameters from  $current_link .

	$p = strpos( $current_link, '?' );
	if ($p === false) $p = strlen( $current_link );
	$base_link = substr( $current_link, 0, $p );
	$params_url = substr( $current_link, $p + 1 );

	// Build array containing single parameters (of form "name=value")
	$params_exp = explode( '&', $params_url );

	// Create array using named indices ("params_key[name]=value")
	$params_key = Array();
	if ($params_url != '') {
		foreach( $params_exp as $p ) {

			// Only key given?
			if (strpos($p, '=') === false) {
				//  $p  is the key name. 
				$params_key[$p] = '';
			}
			// Key and value given.
			else {
				list($key, $value) = explode( '=', $p );
				$params_key[$key] = $value;
			}
		}
	}

	//  $params_key  now contains all parameters found in  $current_link .

	// Add or update the given parameter
	if ($parameter_name != '') {
		$params_key[$parameter_name] = $add_value;
	}


	// SORT parameters for increased niceness

	$order = explode( ' ', GET_PARAMETER_ORDER );
	$new_order = Array();

	foreach( $order as $key ) {

		if (isset( $params_key[$key] )) {
			$new_order[$key] = $params_key[$key];
		}
	}

	$params_key = $new_order;


	// SERIALIZE: Build URL parameter string

	$GET_toggles = Array( 'flip' );   // Allow toggling of certain parameters here

	$params_out = '';
	foreach( $params_key as $key => $value ) {
		if ($value != REMOVE_FROM_LINK)
		{
			if ($key != GET_BASE_BOARD) {
				if ($value != '') {
					$params_out .= "&$key=$value";
				}
				else if (in_array($key, $GET_toggles)) {
					$params_out .= "&$key";
				}
			}
		}
	}

	$key = GET_BASE_BOARD;
	if (isset( $params_key[$key] )) {
		$value = $params_key[ $key ];
		if ($value != INITIAL_BOARD_CODED) {
			$params_out .= "&".GET_BASE_BOARD."=$value";
		}
	}

	if (substr($params_out,0,1) == '&') $params_out[0] = '?';


	// Fix some

	$ret = str_replace( '&', '&amp;', $base_link.$params_out );
	$ret = str_replace( ' ' , '+', $ret );

	return $ret;

} // update_href


# EOF ?>