<?php /* generate_markup.php */ if (!isset($VERSION)) die('Include only.');
/******************************************************************************
* REMOTE CHESS - MARKUP CONSTRUCTION
******************************************************************************/


## CONVERSIONS ################################################################

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
	$col = ord($field[0]) - ord('1');
	$row = ord($field[1]) - ord('A');

	return substr( LOCATION_CODES, $row*8+$col, 1 );
}

function decode_field( $code )
{
	$p = strpos( LOCATION_CODES, $code );

	$col = $p % 8;
	$row = ($p-$col) / 8;

	return rowcol_to_field( $row, $col );
}


## HISTORY MARKUP #############################################################

/**
 * history_markup_old() - old function, before real history was implemented
 * The new function is kept in  game_logic.php  next to  decode_history() ,
 * because  history_markup()  is an extended version of  decode_history() ,
 * so they should be next to each other for comparison.
 */
function history_markup_old( $board_array, $history, $name_white, $name_black )
{
	$ret = "<pre class=\"history\">\n<b>$name_white</b> vs. <b>$name_black</b>\n";

	$move_nr = 0;

	$length = strlen( $history );
	for( $i = 0 ; $i < $length ; $i+=2 ) {

		$coded_move = substr( $history, $i, 2 );
		$from = decode_field( $coded_move[0] );
		$to   = decode_field( $coded_move[1] );

		$move_nr++;
		$ret .= "$move_nr: $from - $to";

		if ($move_nr % 2 == 0) {
			$ret .= "\n"; 
		} else {
			$ret .= "  ...  ";
		}
	}

	if (HISTORY_PROMPT) {
		$ret .= ++$move_nr . ": ?\n";
	}

	$ret .= "</pre>\n";
	return $ret;

} // history_markup


## CHESS BOARD MARKUP #########################################################

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
 * piece_class_names()
 * returns the CSS class for a piece code character
 */
function piece_class_name( $code )
{
	$class_names = Array(
		'P' => 'white pawn',
		'S' => 'white rook notmoved',
		'R' => 'white rook',
		'N' => 'white knight',
		'B' => 'white bishop',
		'Q' => 'white queen',
		'L' => 'white king notmoved',
		'K' => 'white king',

		'p' => 'black pawn',
		's' => 'black rook notmoved',
		'r' => 'black rook',
		'n' => 'black knight',
		'b' => 'black bishop',
		'q' => 'black queen',
		'l' => 'black king notmoved',
		'k' => 'black king',
	);

	if (isset( $class_names[ $code ] )) {
		return $class_names[ $code ];
	} else {
		return '';
	}
} // piece_class_name


/**
 * piece_class_markup()
 */
function piece_class_markup( $code, $selected = false )
{
	$class_name = piece_class_name( $code );

	if ($class_name != '') {
		if ($selected) $class_name .= ' selected';
		return " class=\"$class_name\"";
	} else {
		return '';
	}
}


/**
 * piece_name() - Clear text name of a piece
 */
function piece_name( $code )
{
	$ret = ucfirst( piece_class_name($code) );

	$ret = str_replace( 'notmoved', '(not moved yet)', $ret );

	return $ret;
}


/**
 * piece_name_markup()
 */
function piece_name_markup( $code )
{
	$piece_name = piece_name( $code );

	if ($piece_name != '') {
		return " title=\"$piece_name\"";
	} else {
		return '';
	}
}


/**
 * piece_glyph()
 */
function piece_glyph( $piece )
{
	if (USE_UNICODE_GLYPHS) {

		$ret = '<span class="glyph">';

		switch ($piece) {
			case 'P' : $ret .= '&#9817;'; break;
			case 'S' : // Fall through: Not yet moved white rook
			case 'R' : $ret .= '&#9814;'; break;
			case 'N' : $ret .= '&#9816;'; break;
			case 'B' : $ret .= '&#9815;'; break;
			case 'Q' : $ret .= '&#9813;'; break;
			case 'L' : // Fall through: Not yet moved white king
			case 'K' : $ret .= '&#9812;'; break;
			case 'p' : $ret .= '&#9823;'; break;
			case 's' : // Fall through: Not yet moved black rook
			case 'r' : $ret .= '&#9820;'; break;
			case 'n' : $ret .= '&#9822;'; break;
			case 'b' : $ret .= '&#9821;'; break;
			case 'q' : $ret .= '&#9819;'; break;
			case 'l' : // Fall through: Not yet moved black king
			case 'k' : $ret .= '&#9818;'; break;
			default: return '?';
		}

		$ret .= '</span>';
	}
	else {
		$ret = $piece;
	}

	return $ret;

} // piece_glyph


/**
 * piece_in_field()
 * returns a DIV for a piece, if any is in the given field
 */
function piece_in_field( $pieces, $selected, $field_name )
{
	global $PIECE_CLASS_NAMES;

	foreach( $pieces as $f => $p ) {
		if ($f == $field_name) {
			$selected = in_array( $f, $selected );
			$class = piece_class_markup( $p, $selected );
			$title = piece_name_markup( $p );
			$p = piece_glyph( $p );
			//...$title = ucfirst( $p);
			return "<div$class$title>$p</div>";
		}
	}

	return '';
}


/**
 * get_pieces()
 */
function get_pieces( $board )
{
	$ret = Array();

	for( $row = 0 ; $row < 8 ; $row++ ) {
		for( $col = 0 ; $col < 8 ; $col++ ) {
			if ($board[$row][$col] != '') {
				$field = rowcol_to_field( $row, $col );
				$ret[$field] = $board[$row][$col];
			}
		}
	}

	return $ret;
}


/******************************************************************************
 * chess_board_markup()
 */
function chess_board_markup(
	$base_href,
	$board,
	$clickable,
	$selected,
	$player,
	$flip_board
) {
	$board_flipped = ($player == WHITES_MOVE);
	if ($flip_board) $board_flipped = !$board_flipped;

	$pieces = get_pieces( $board );

	$class = ($player == WHITES_MOVE) ? GET_WHITE : GET_BLACK ;
	$class .= ($flip_board) ? ' flipped' : '' ;
	$html = "<table class=\"$class\">\n";


	// Create horizontal legend (A .. H) table row
	$legend = "<tr><th></th>";
	for( $row = 0 ; $row < 8 ; $row++ ) {
		$legend .= '<th>';
		if ($board_flipped) {
			$legend .= chr( ord('A') + ($row) );
		} else {
			$legend .= chr( ord('A') + (7 - $row) );
		}
		$legend .= '</th>';
	}
	$legend .= "<th></th></tr>\n";

	$html .= $legend;

	// Create main part of the table
	for( $row = 0 ; $row < 8 ; $row++ ) {
		if ($board_flipped) {
			$row_name = 8 - $row;
		} else {
			$row_name = $row + 1;
		}

		// Start row with left legend
		$html .= "<tr><th>$row_name</th>\n";

		for( $col = 0 ; $col < 8 ; $col++ ) {
			if ($board_flipped) {
				$col_name = chr( ord('A') + $col );
			} else {
				$col_name = chr( ord('A') + (7-$col) );
			}

			$field_name = $col_name . $row_name;

			$class = in_array($field_name, $selected)
			?	' class="selected"'
			:	''
			;

			//$base_link
			$has_href = in_array($field_name, $clickable);

			if ($class == '') {
				$href = update_href( $base_href, GET_FROM, $field_name );
			} else {
				$href = update_href( $base_href, GET_TO, $field_name );
			}

			// Add TD for field
			$html .= "\t<td$class>";
			if ($has_href) $html .= "<a href=\"$href\">";

			$p = piece_in_field( $pieces, $selected, $field_name );
			if ($p != '') {
				$html .= $p;
			} else {
				$html .= '.';
			}

			if ($has_href) $html .= "</a>";
			$html .= "</td>\n";
		}

		// Right legend, finalize row
		$html .= "<th>$row_name</th></tr>\n";
	}

	// Add second legend row and finalize
	$html .= "$legend</table>\n";

	return $html;

} // chess_board_markup


# EOF ?>