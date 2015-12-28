<?php /* generate_markup.php */ if (!isset($VERSION)) die('Include only.');
/******************************************************************************
* REMOTE CHESS - Copy(L)eft 2015                         http://harald.ist.org/
* MARKUP CONSTRUCTION
******************************************************************************/

/******************************************************************************
* PSEUDO CONSTANTS
******************************************************************************/

/**
 * piece_class_names()
 * returns the CSS class for a piece code character
 */
function piece_class_name( $code )
{
	$class_names = array(
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
 * piece_glyph()
 */
function piece_glyph( $piece, $class = 'glyph' )
{
	$ret = '';

	if (USE_UNICODE_GLYPHS) {

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

			default:
				debug_out( "\ngenerate_markup: piece_glyph: PIECE: $piece" );
				return '?';
		}
	}
	else {
		$ret = $piece;
	}

	return $ret;

} // piece_glyph


/******************************************************************************
* HISTORY MARKUP
******************************************************************************/

/**
 * history_link()
 */
function history_link( $href_this, $goto, $player )
{
	if ($player == BLACKS_MOVE) $goto++;
	$ret = update_href( $href_this, GET_GOTO, $goto );
/*
	if ($i == 0) {
		$ret .= '&amp;goto=0';
	}
*/
	return $ret;
}


/**
 * simplify_history()
 */
function simplify_history( $markup )
{
	$white_pawn = piece_glyph('P');
	$black_pawn = piece_glyph('p');

	$PAWN_OPENINGS = array(
		"{$white_pawn}a2 &ndash; a3" => "a3",  "{$white_pawn}a2 &ndash; a4" => "a4",
		"{$white_pawn}b2 &ndash; b3" => "b3",  "{$white_pawn}b2 &ndash; b4" => "b4",
		"{$white_pawn}c2 &ndash; c3" => "c3",  "{$white_pawn}c2 &ndash; c4" => "c4",
		"{$white_pawn}d2 &ndash; d3" => "d3",  "{$white_pawn}d2 &ndash; d4" => "d4",
		"{$white_pawn}e2 &ndash; e3" => "e3",  "{$white_pawn}e2 &ndash; e4" => "e4",
		"{$white_pawn}f2 &ndash; f3" => "f3",  "{$white_pawn}f2 &ndash; f4" => "f4",
		"{$white_pawn}g2 &ndash; g3" => "g3",  "{$white_pawn}g2 &ndash; g4" => "g4",
		"{$white_pawn}h2 &ndash; h3" => "h3",  "{$white_pawn}h2 &ndash; h4" => "h4",

		"{$black_pawn}a7 &ndash; a6" => "a6",  "{$black_pawn}a7 &ndash; a5" => "a5",
		"{$black_pawn}b7 &ndash; b6" => "b6",  "{$black_pawn}b7 &ndash; b5" => "b5",
		"{$black_pawn}c7 &ndash; c6" => "c6",  "{$black_pawn}c7 &ndash; c5" => "c5",
		"{$black_pawn}d7 &ndash; d6" => "d6",  "{$black_pawn}d7 &ndash; d5" => "d5",
		"{$black_pawn}e7 &ndash; e6" => "e6",  "{$black_pawn}e7 &ndash; e5" => "e5",
		"{$black_pawn}f7 &ndash; f6" => "f6",  "{$black_pawn}f7 &ndash; f5" => "f5",
		"{$black_pawn}g7 &ndash; g6" => "g6",  "{$black_pawn}g7 &ndash; g5" => "g5",
		"{$black_pawn}h7 &ndash; h6" => "h6",  "{$black_pawn}h7 &ndash; h5" => "h5",

		"&#215; {$white_pawn}" => "&#215; ",   "&#215; {$black_pawn}" => "&#215; ",
		$white_pawn => '', $black_pawn => ''
	);

	foreach( $PAWN_OPENINGS as $old => $new ) {
		$markup = str_replace(
			$old,
			$new,
			$markup
		);
	}

	return $markup;

} // simplify_history


/**
 * history_markup()
 * See  decode_history  in  game_logic.php , it was the template for this
 * function.
 */
function history_markup(
	$board,
	$history,
	$href_this,
	$name_white,
	$name_black,
	$prompt
) {
	$history .= '__';
	$goto = get_parameter( GET_GOTO );


	$class = HISTORY_BREAK_UL ? '' : ' class="scrolling"';
	$ret
	= "<h2><strong>$name_white</strong>"
	. " vs. <strong>$name_black</strong></h2>\n"
	. "<ul$class>\n"
	;

	$current_move = 0;   //... Newly introduced. Routine might need cleanup.
	$skipped_turns = 0;
	$length = strlen( $history );

	for( $i = 0 ; $i < $length ; $i += 2 ) {
		$from_code = substr( $history, $i, 1 );
		$to_code   = substr( $history, $i+1, 1 );

		$from_field = decode_field( $from_code );
		$to_field   = decode_field( $to_code );

		list( $f_row, $f_col ) = field_to_rowcol( $from_field );
		list( $t_row, $t_col ) = field_to_rowcol( $to_field );

		if ((HISTORY_BREAK_UL) && ($i > 0) && (($i-4*$skipped_turns) % 80 == 0)) {
			$ret .= "</ul><ul>\n";
		}

		switch ($from_code) {
		case '_':   // Last item, show prompt /////////////////////////
			if ($i % 4 == 0) {
				$link = $href_this;
				$ret .= "\t<li>";
				$nr = i_to_round( $history, $i );
				$ret .= $nr . ': ';
				$ret .= "<a href=\"$link\">";
			}

			$ret .= $prompt;
			break;

		case '(':   // Pawn Promotion /////////////////////////////////
			$i += 2;
			$skipped_turns++;
			break;

		default:   // Move ////////////////////////////////////////////
			$new_move = '';
			$current_move++;
			$current_color = (($current_move % 2) == WHITES_MOVE);
			$opponent_color = ! $current_color;

			if ($i % 4 == 0) {
				if ($i < $length) {
					$link = history_link(
						$href_this,
						$current_move,
						WHITES_MOVE
					);
				} else {
					$link = $href_this;
				}

				// Highlight history entry
				$class
				= ($current_move == $goto)
				? ' class="selected"'
				: ''
				;

				$nr = i_to_round( $history, $i );
				$new_move .= "\t<li>$nr: <a href=\"$link\"$class>";
			}

			$piece = $board[$f_row][$f_col];
			$new_move .= piece_glyph( $piece );
			$new_move .= strtolower( $from_field );

			if ($board[$t_row][$t_col] != '') {
				$new_move
				.= ' &#215; '
				.  piece_glyph( $board[$t_row][$t_col] )
				;
			} else {
				$new_move .= ' &ndash; ';
			}

			$new_move .= strtolower( $to_field );


			// Update the board

			$board = apply_move(
				$board,
				$f_row, $f_col,
				$t_row, $t_col
			);


			// Promotion

			if (substr($history, $i+2, 1) == '(') {
				$piece = substr( $history, $i+4, 1 );
				$new_move .= piece_glyph( $piece );

				$board[$t_row][$t_col] = substr(
					$history,
					$i + 4,
					1
				);
			}


			// Do we threaten the other king?

			if (king_in_check( $board, ! $current_color )) {
				$new_move .= '+';
			}

			if ($i % 4 == 0) {
				if ($i + 4 < $length) {
					$link = history_link(
						$href_this,
						$current_move,
						BLACKS_MOVE
					);
				} else {
					$link = $href_this;
				}
				$class
				= ($current_move + 1 == $goto)
				? ' class="selected"'
				: ''
				;
				$new_move .= ",</a> <a href=\"$link\"$class>";
			} else {
				$new_move .= "</a>\n";
			}


			// Reduce opening pawn moves

			$new_move = simplify_history( $new_move );
			$ret .= $new_move;

		} // switch $from_code
	} // for $i

	$ret .= '</a>';

	if (substr($ret, -1, 1) != "\n") {
		$ret .= "\n";
	}

	return $ret . "</ul>\n";

} // history_markup


/******************************************************************************
* PAWN PROMOTION DIALOG
******************************************************************************/

/**
 * promotion_dialog_markup()
 */
function promotion_dialog_markup( $href_this, $current_player, $row, $col, $history )
{
	$current_player = ! $current_player;
	
	$player_pieces = get_player_pieces( $current_player );
	$field = rowcol_to_field( $row, $col );

	$ret  = "<ul class=\"popup\">\n";

	$field_code = encode_field( $field );

	$code_r = '(' . $field_code . $player_pieces[PIECE_ROOK]   . ')';
	$code_k = '(' . $field_code . $player_pieces[PIECE_KNIGHT] . ')';
	$code_b = '(' . $field_code . $player_pieces[PIECE_BISHOP] . ')';
	$code_q = '(' . $field_code . $player_pieces[PIECE_QUEEN]  . ')';

	if ($current_player == WHITES_MOVE) {
		$color = GET_WHITE;
		$next_player = GET_BLACK;
	} else {
		$color = GET_BLACK;
		$next_player = GET_WHITE;
	}
	$t_color = ucfirst( $color );

	$href_this = update_href( $href_this, GET_PLAYER, $next_player );

	$href_r = update_href( $href_this, GET_HISTORY, $history . $code_r );
	$href_n = update_href( $href_this, GET_HISTORY, $history . $code_k );
	$href_b = update_href( $href_this, GET_HISTORY, $history . $code_b );
	$href_q = update_href( $href_this, GET_HISTORY, $history . $code_q );

	if (USE_UNICODE_GLYPHS) {
		$r = '&#9814;';
		$n = '&#9816;';
		$b = '&#9815;';
		$q = '&#9813;';
	} else {
		$r = $player_pieces[PIECE_ROOK];
		$n = $player_pieces[PIECE_KNIGHT];
		$b = $player_pieces[PIECE_BISHOP];
		$q = $player_pieces[PIECE_QUEEN];
	}

	$ret .= "<li><a href=\"$href_r\"><div class=\"$color rook\" title=\"$t_color rook\">$r</div></a>\n";
	$ret .= "<li><a href=\"$href_n\"><div class=\"$color knight\" title=\"$t_color knight\">$n</div></a>\n";
	$ret .= "<li><a href=\"$href_b\"><div class=\"$color bishop\" title=\"$t_color bishop\">$b</div></a>\n";
	$ret .= "<li><a href=\"$href_q\"><div class=\"$color queen\" title=\"$t_color queen\">$q</div></a>\n";
	$ret .= "</ul>\n\n";

	return $ret;

} // promotion_dialog_markup


/******************************************************************************
* CHESS BOARD MARKUP
******************************************************************************/

/**
 * piece_class_markup()
 */
function piece_class_markup( $code, $is_selected = false )
{
	$class_name = piece_class_name( $code );

	if ($class_name != '') {
		if ($is_selected) $class_name .= ' selected';
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

	$ret = str_replace( 'notmoved', '(not yet moved)', $ret );

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
 * piece_in_field()
 * returns a DIV for a piece, if any is in the given field
 */
function piece_in_field( $pieces, $selected, $field_name )
{
	global $PIECE_CLASS_NAMES;

	foreach( $pieces as $f => $p ) {
		if ($f == $field_name) {
			$is_selected = in_array( $f, $selected );
			$class = piece_class_markup( $p, $is_selected );
			$title = piece_name_markup( $p );
			$p = piece_glyph( $p );
			//...$title = ucfirst( $p);
			return "<div$class$title>$p</div>";
		}
	}

	return '';
}


/**
 * get_pieces() - Get associative array of pieces orderd by field name
 */
function get_pieces( $board )
{
	$ret = array();

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
	if ($flip_board) $board_flipped = ! $board_flipped;

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

			$class = in_array( $field_name, $selected )
			?	' class="selected"'
			:	''
			;

			//$base_link
			$has_href = in_array( $field_name, $clickable );

			if (get_parameter(GET_FROM) != get_parameter(GET_TO)) {
				$href = update_href( $base_href, GET_TO, $field_name );
			} else {
				$href = update_href( $base_href, GET_FROM, $field_name );
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