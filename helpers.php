<?php /* helpers.php */ if (!isset($VERSION)) die('Include only.');
/******************************************************************************
* REMOTE CHESS - Copy(L)eft 2015                         http://harald.ist.org/
* HELPER FUNCTIONS
******************************************************************************/

/******************************************************************************
* DEBUG HELPERS
******************************************************************************/

/**
 * Debug information will be collected in  $debug_html  and may be output
 * in the HTML output, if  DEBUG  is set to true in  definitions.php .
 */
$debug_html = 'Remote Chess ' . $VERSION;

/**
 * debug_out() - Usually you begin this with a "\n".
 */
function debug_out( $message )
{
	global $debug_html;

	if (DEBUG) {
		$debug_html .= $message;
	}

	if (DEBUG_TO_FILE) {
		if (($fh = fopen(DEBUG_FILE_NAME, 'a')) !== false) {
			fwrite( $fh, $message );
			fclose( $fh );
		}
	}
}

/**
 * debug_array() - Formatted display of an array
 */
function debug_array( $array, $name = '' )
{
	ob_start();
	if ($name != '') echo "$name = ";
	print_r( $array );
	debug_out( ob_get_clean() );
}


/******************************************************************************
* CONVERSIONS
******************************************************************************/

/**
 * valid_field_name() - checks if a field name is well formed
 */
function valid_field_name( $field )
{
	$valid = true;

	if (strlen($field) == 2) {
		$a = ord( $field[0] );
		$n = ord( $field[1] );

		$valid &= ($a >= ord('A'));
		$valid &= ($a <= ord('H'));
		$valid &= ($n >= ord('1'));
		$valid &= ($n <= ord('8'));
	} else {
		$valid = ($field == '');
	}

	return $valid;
}


/**
 * rowcol_to_field() - transforms 0-based coordinates to field names like "E2"
 */
function rowcol_to_field( $row, $col )
{
	$ret
	= chr( ord('A') + $col )
	. chr( ord('1') + $row )
	;

	return $ret;
}


/**
 * field_to_rowcol() - returns list($row, $col) corresponding to given field
 */
function field_to_rowcol( $field )
{
	$col = ord( $field[0] ) - ord('A');
	$row = ord( $field[1] ) - ord('1');

	return Array( $row, $col );
}


/**
 * encode_field()
 * decode_field()
 */
function encode_field( $field )
{
	$col = ord($field[0]) - ord('A');
	$row = ord($field[1]) - ord('1');

	return substr( LOCATION_CODES, $row*8+$col, 1 );
}

function decode_field( $code )
{
	$p = strpos( LOCATION_CODES, $code );

	$col = $p % 8;
	$row = ($p-$col) / 8;

	return rowcol_to_field( $row, $col );
}


/**
 * i_to_round()
 * Calculate from length of  $history  string, accounting for promotions
 */
function i_to_round( $history, $i = -1 )
{
	if ($i == -1) {
		$i = strlen( $history );
	}
	$round = 1 + floor( $i / 4 );

	$promotions = substr_count(
		substr( $history, 0, $i ),
		')'
	);

	return $round - $promotions;
}

function goto_to_round( $history, $goto )
{
	$round = floor( ($goto + 1) / 2 );

	$promotions = substr_count(
		substr($history, 0, $round*4 ),
		')'
	);

	return $round - $promotions;
}


# EOF ?>