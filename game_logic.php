<?php /* game_logic.php */ if (!isset($VERSION)) die('Include only.');
/******************************************************************************
* REMOTE CHESS - Copy(L)eft 2015                         http://harald.ist.org/
* GAME LOGIC and MAIN CONTROL
******************************************************************************/

/**
 * new_board()
 * Make an 8 x 8 array. //...Is there a better way?
 */
function new_board()
{
	$ret = array();

	for( $row = 0 ; $row < 8 ; $row++ ) {
		$ret[$row] = array();
		for( $col = 0 ; $col < 8 ; $col++ ) {
			$ret[$row][$col] = '';
		}
	}

	return $ret;
}


/**
 * find_king()
 */
function find_king( $board_array, $current_player )
{
	$king_codes = ($current_player == WHITES_MOVE) ? 'LK' : 'lk' ;
	$king_field = '';

	for( $row = 0 ; $row < 8 ; $row++ ) {
		for( $col = 0 ; $col < 8 ; $col++ ) {

			$f = $board_array[$row][$col];

			if ($f != '') {
				if (strpos($king_codes, $f) !== false) {
					$king_field = rowcol_to_field(
						$row,
						$col
					);
				}
			}
		}
	}

	return $king_field;

} // find_king


/**
 * hot_fields()
 */
function hot_fields( $board_array, $current_player )
{
	$ret = array();
	$hot_array = new_board();

	$movable_opponents = find_movable_pieces(
		$board_array,
		! $current_player
	);

	foreach( $movable_opponents as $from_field ) {

		$possible_moves = possible_move_list(
			$board_array,
			! $current_player,
			$from_field
		);

		foreach( $possible_moves as $to_field ) {
			list( $row, $col ) = field_to_rowcol( $to_field );
			$hot_array[$row][$col] = 'not empty';
		}
	}

	for( $row = 0 ; $row < 8 ; $row++ ) {
		for( $col = 0 ; $col < 8 ; $col++ ) {
			if ($hot_array[$row][$col] == 'not empty') {
				$ret[] = rowcol_to_field( $row, $col );
			}
		}
	}

	return $ret;

} // hot_fields


/**
 * apply_move() - transfers a piece to another field
 */
function apply_move( $board_array,  $f_row, $f_col,  $t_row, $t_col )
{
	$piece = $board_array[$f_row][$f_col];   // "Take" the piece, ..
	$board_array[$f_row][$f_col] = '';       // .. clear origin field

	$dx = $t_col - $f_col;
	$dy = $t_row - $f_row;


	// En passant - Remove captured enemy from board

	if( (($dx == +1) || ($dx == -1))
	&&	(  ($piece == 'P') && ($f_row == 4) && ($dy == +1)
		|| ($piece == 'p') && ($f_row == 3) && ($dy == -1)
		)
	) {
		if ($piece == 'P') {
			if ($board_array[$f_row][$t_col] == 'p') {
				$board_array[$f_row][$t_col] = '';
			}
		} else {
			if ($board_array[$f_row][$t_col] == 'P') {
				$board_array[$f_row][$t_col] = '';
			}
		}
	}


	// Castles

	if( (($piece == 'L') || ($piece == 'l'))
	) {
		if ($t_col == 2) {
			$moved_rook = $board_array[$f_row][0];
			if ($moved_rook == 'S') $moved_rook = 'R';
			if ($moved_rook == 's') $moved_rook = 'r';
			$board_array[$f_row][$f_col-1] = $moved_rook;
			$board_array[$f_row][0] = '';
		}
		if ($t_col == 6) {
			$moved_rook = $board_array[$f_row][7];
			if ($moved_rook == 'S') $moved_rook = 'R';
			if ($moved_rook == 's') $moved_rook = 'r';
			$board_array[$f_row][$f_col+1] = $moved_rook;
			$board_array[$f_row][7] = '';
		}
	}


	// Turn not yet moved pieces into already moved ones

	if ($piece == 'S') $piece = 'R';   // White rooks
	if ($piece == 's') $piece = 'r';   // Black rook
	if ($piece == 'L') $piece = 'K';   // White king
	if ($piece == 'l') $piece = 'k';   // Black king


	// Move piece to target field

	$board_array[$t_row][$t_col] = $piece;


	return $board_array;

} // apply_move


/**
 * decode_history()
 */
function decode_history( $base_array, $history, $goto = '' )
{
	$ret = $base_array;
	//...$positions = array();

	if ($goto == '') {
		// Calculate move number from history length
		$promotions = substr_count( $history, '(' );
		$goto = (strlen($history) - $promotions*4) / 2;
	}

	// Board reconstruction: Moving the pieces around according to history
	for
	( $parsed_moves = 0, $i = 0
	; ($parsed_moves < $goto) || (substr($history, $i, 1) == '(')
	; $i += 2
	) {
		$from_code = substr( $history, $i, 1 );
		$to_code   = substr( $history, $i+1, 1 );

		if ($from_code == '(') {   // Promotion of previously moved pawn
			// Find out, to what piece the pawn was promoted to
			$field = decode_field( $to_code );
			$piece = substr( $history, $i+2, 1 );
			list( $row, $col ) = field_to_rowcol( $field );

			// Turn piece into new one
			$ret[$row][$col] = $piece;

			$i += 2;
		}
		else {   // Normal move
			$from_field = decode_field( $from_code );
			$to_field   = decode_field( $to_code );

			list( $f_row, $f_col ) = field_to_rowcol( $from_field );
			list( $t_row, $t_col ) = field_to_rowcol( $to_field );

			$ret = apply_move( $ret,  $f_row, $f_col,  $t_row, $t_col );
			//...?$positions[] = encode_board( $ret );
			$parsed_moves++;
		}
	}


	//...// Analyze positions
	//...$tie_info = false;

	return $ret; //...array( $ret, $tie_info );

} // decode_history


/**
 * select_piece() - Creates  $possible_moves
 * The returned data will be used when building markup with clickable pieces
 */
function select_piece( $board_array, $current_player, $from_field )
{
	// Find fields, this piece can generally move to
	// Also checks for obstacles and possible captures

	$clickable = possible_move_list(
		$board_array,
		$current_player,
		$from_field
	);

	// Eliminate moves that would end in own king being in check

	$new = array( $clickable[0] );
	list( $f_row, $f_col ) = field_to_rowcol( $from_field );
	$piece = $board_array[$f_row][$f_col];

	foreach( $clickable as $to_field ) {

		if ($from_field != $to_field) {     // Ignore deselection

			list( $t_row, $t_col ) = field_to_rowcol( $to_field );

			$new_array = apply_move(    // Try the move
				$board_array,
				$f_row, $f_col,
				$t_row, $t_col
			);

			$king_field = find_king(    // Locate the king
				$new_array,
				$current_player
			);

			$hot_fields = hot_fields(   // See, if the king would..
				$new_array,         // ..end up under attack
				$current_player
			);

			if (! in_array( $king_field, $hot_fields )) {
				// King is NOT under attack when doing the move
				$attacked = false;   // Not direclty, at least

				// Check, if we try to castle and any fields
				// are under attack, preventing the move.
				$dir = $t_col - $f_col;

				if ($piece == 'L') {    // White king
					if ($dir == -2) { // B, C, D must be free
						$attacked
						=  in_array( 'B1', $hot_fields )
						|| in_array( 'C1', $hot_fields )
						|| in_array( 'D1', $hot_fields )
						;
					}
					else if ($dir == +2) { // F and G must be free
						$attacked
						=  in_array( 'F1', $hot_fields )
						|| in_array( 'G1', $hot_fields )
						;
					}
				}

				if ($piece == 'l') {    // Black king
					if ($dir == -2) { // B, C, D must be free
						$attacked
						=  in_array( 'B8', $hot_fields )
						|| in_array( 'C8', $hot_fields )
						|| in_array( 'D8', $hot_fields )
						;
					}
					else if ($dir == +2) { // F and G must be free
						$attacked
						=  in_array( 'F8', $hot_fields )
						|| in_array( 'G8', $hot_fields )
						;
					}
				}

				if (/* normal move or */ ! $attacked) {
					$new[] = $to_field;
				}
			}
		}
	}

	$clickable = $new;
	$selected = array( $clickable[0] );

	return array( $clickable, $selected );

} // select_piece


/******************************************************************************
* MAIN CONTROL
******************************************************************************/

function main_control()
{
	// These globals are used in the HTML template

	global $current_player, $history;
	global $heading, $name_white, $name_black;
	global $show_command_form, $flip_board;
	global $preset_from_value, $preset_to_value, $id_focus;
	global $chess_board_markup, $history_markup, $promotion_dialog_markup;
	global $board_encoded, $game_title, $turn_nr;
	global $history_next, $history_prev;
	global $href_this, $href_player, $href_flip;
	global $game_state_link, $hmw_home_link;


	// Initialize a bit

	$promotion_popup = false;     //...NYI Show pawn promotion dialog
	$show_command_form = true;    // Show the move input dialog

	$game_status = IN_PROGRESS;   // The game has not ended yet
	$heading = '';                // "White's move" caption
	$game_title = '';             // Current game info for page title
	$game_state_link = '';        // "Send this link"-link ..
	$hmw_home_link = '';          // .. corrected for my stupid router



	//... Remember an initial double move of a pawn

	$get_en_passant = get_parameter( GET_EN_PASSANT );
	$new_en_passant = '';


	// Retreive GET data

	$flip_board = isset( $_GET[GET_FLIP_BOARD] );
	$history    = get_parameter( GET_HISTORY );
	$goto       = get_parameter( GET_GOTO );
	$name_white = get_parameter( GET_WHITE, DEFAULT_NAME_WHITE );
	$name_black = get_parameter( GET_BLACK, DEFAULT_NAME_BLACK );

	if (get_parameter(GET_PLAYER, GET_WHITE) != GET_WHITE) {
		//  &player  set, but not to "white", is taken as "black"
		$current_player = BLACKS_MOVE;
	} else {
		$current_player = WHITES_MOVE;
	}

	// Load base positions of pieces
	$base_array = decode_board(
		get_parameter( GET_BASE_BOARD, INITIAL_BOARD_CODED )
	);


	// Trace history (Reconstruct the current board from initial positions)

	//...list( $board_array, $tie_info ) = decode_history(
	$board_array = decode_history(
		$base_array,
		$history,
		$goto
	);


	// Execute given command

	// Retreive FORM input
	// Move: "from" and "to" must be field names
	// Edit: "from" must be the code character for a piece and "to" a field

	$clickable = $selected = array();
	$redirect_after_move = false;

	$cmd_piece = get_parameter(GET_FROM);
	$cmd_from = strtoupper( get_parameter(GET_FROM) ); // Retreive commands
	$cmd_to   = strtoupper( get_parameter(GET_TO) );

	if ($cmd_from == $cmd_to) {           // Deselect a piece
		$cmd_from = $cmd_to = '';
	}
	if ($cmd_from == '') $cmd_to = '';    // Never allow only TO command

	// Exec: Editor
	if ((strlen($cmd_from) == 1) && valid_field_name($cmd_to)) {
		if (strpos(WHITE_PIECES.BLACK_PIECES, $cmd_from) !== false) {

			list( $row, $col ) = field_to_rowcol( $cmd_to );

			if ($base_array[$row][$col] == $cmd_piece) {
				// Delete existing piece
				$base_array[$row][$col] = '';
			}
			else if ($base_array[$row][$col] == '') {
				// Add new piece
				$base_array[$row][$col] = $cmd_piece;
			}

			#$base_array = $board_array;
			$redirect_after_move = true;
		}

		// No other commands with FROM being only one char.
		$cmd_from = $cmd_to = '';
	}

	// Make sure, no invalid data is being processed as a move
	if (! valid_field_name( $cmd_from )) $cmd_from = '';
	if (! valid_field_name( $cmd_to   )) $cmd_to   = '';

	// Exec: Move
	if (($cmd_from != '') && ($cmd_to != '')) {

		$turn_nr = i_to_round( $history );

		if (($turn_nr - floor($turn_nr)) == 0.5) {
			$turn_nr = floor($turn_nr);
			$color = 'Black';
		} else {
			$color = 'White';
		}
debug_out( $_SERVER['REMOTE_ADDR'] . " - $name_white vs. $name_black, $turn_nr, $color: $cmd_from - $cmd_to\n" );


		list($f_row, $f_col) = field_to_rowcol( $cmd_from );
		list($t_row, $t_col) = field_to_rowcol( $cmd_to );

		list( $clickable, $selected ) = select_piece(
			$board_array,
			$current_player,
			$cmd_from,
			$get_en_passant
		);


		// Check if it is our//... piece (or a piece at all)

		if (! in_array( $cmd_from, $clickable )) {
			die( "Error: clickable[$f_row][$f_col] empty." );
		}

		if (! in_array( $cmd_to, $clickable )) {
			// Capturing a piece!
			echo "Capture! clickable = ";
			print_r( $clickable );
			die();
		}


		// En passant

		$piece = $board_array[$f_row][$f_col];

		if( (($piece == 'P') && ($f_row == 1))
		||  (($piece == 'p') && ($f_row == 6))
		) {
			if (($t_row == 3) || ($t_row == 4)) {
				$get_en_passant = chr( ord('A') + $f_col );
			}
		}

		if( (($piece == 'P') && ($t_row-$f_row == +1))
		||  (($piece == 'p') && ($t_row-$f_row == -1))
		||  (($piece != 'P') && ($piece != 'p'))
		) {
			$get_en_passant = '';
		}


		// Promotion

		if( (($piece == 'P') && ($t_row == 7))
		||  (($piece == 'p') && ($t_row == 0))
		) {
			$href_this = update_href(
				$href_this,
				GET_PROMOTE,
				chr( ord('A') + $t_col )
			);
			$promotion_popup = true;
			$heading = 'Promote your pawn to:';

			$board_array = apply_move(
				$board_array,
				$f_row, $f_col,
				$t_row, $t_col
			);
		}


		// New move applied, prepare for fresh move

		$clickable = $selected = array();
		
		$cmd_from = $cmd_to = '';   // Fall through to NO COMMAND mode
		$current_player = ! $current_player;

		$history .= encode_move( $f_row, $f_col,  $t_row, $t_col );

		// We changed the board, but the user's browser still shows the
		// move command in its address bar. An HTTP redirect is used to
		// update that address, but the URL is not determined yet
		if (! $promotion_popup) {
			$redirect_after_move = true;
		}
	}

	// Exec: Deselect, continuation of fall throughs above
	if (($cmd_from == '') && ($cmd_to == '')) {

		$clickable = find_movable_pieces(
			$board_array,
			$current_player
		);

		// Get rid of moves with king in check afterwards
		$new = array();
		foreach( $clickable as $from_field ) {
			// Get two arrays (clickable, selected)
			$temp = select_piece(
				$board_array,
				$current_player,
				$from_field
			);
			// Ignore moves with only one "deselection entry"
			if (count($temp[0]) > 1) {
				$new[] = $from_field;
			}
		}
		$clickable = $new;
		if (count($clickable) == 0) {
			if (king_in_check( $board_array, $current_player )) {
				$heading = HEADING_CHECK_MATE;
				$game_status
				= ($current_player != WHITES_MOVE)
				? WHITE_WINS
				: BLACK_WINS
				;
			} else {
				$heading = HEADING_STALE_MATE;
				$game_status = NOONE_WINS;
			}
		}
	}

	// Exec: Select piece
	if (($cmd_from != '') && ($cmd_to == '') && ($goto == '')) {

		list( $clickable, $selected ) = select_piece(
			$board_array,
			$current_player,
			$cmd_from
		);
		$heading = 'Select target';
	}


	// Prepare move command form

	$preset_from_value = $cmd_from;
	$preset_to_value = $cmd_to;

	$id_focus = ($preset_from_value == '') ? 'idFrom' : 'idTo' ;


	// Generate links for main menu and board markup (pieces)

	$board_encoded = encode_board( $base_array );


	// Name parameter as code for who's player's term this is
	$p = ($current_player == WHITES_MOVE) ? GET_WHITE : GET_BLACK ;

	$href_this = update_href();   // get base link
	$href_this = update_href( $href_this, GET_FROM, $preset_from_value );
	$href_this = update_href( $href_this, GET_TO, $preset_to_value );
	$href_this = update_href( $href_this, GET_PLAYER, $p );
	$href_this = update_href( $href_this, GET_HISTORY, $history );
	$href_this = update_href( $href_this, GET_WHITE, $name_white );
	$href_this = update_href( $href_this, GET_BLACK, $name_black );
	$href_this = update_href( $href_this, GET_EN_PASSANT, $get_en_passant );

	$href_this = update_href( $href_this, GET_BASE_BOARD, $board_encoded );

	if ($flip_board) {
		$href_this = update_href( $href_this, GET_FLIP_BOARD, '' );
		$href_flip = update_href( $href_this, GET_FLIP_BOARD, REMOVE_FROM_LINK );
	} else {
		$href_flip = update_href( $href_this, GET_FLIP_BOARD, '' );
	}

	if ($current_player == BLACKS_MOVE) {
		$href_player = update_href( $href_this, GET_PLAYER, GET_WHITE );
	} else {
		$href_player = update_href( $href_this, GET_PLAYER, GET_BLACK );
	}


	// HTTP redirect?

	if (!false && $redirect_after_move) {

		if (DEBUG) {
			#die( "Continue: <a href='$href_this'>$href_this</a>" );
		}

		// Game state has been updated. In case of an executed move,
		// the browser needs to reload the page with the updated URL:
		header( 'HTTP/1.0 303 Found') ;
		header( 'Location: ' . htmlspecialchars_decode($href_this) );
		die();
	}


	// Create HTML markup

	// History
	$history_markup = history_markup(
		$base_array,
		$history,
		$href_this,
		$name_white,
		$name_black,
		$game_status
	);

	// Promotion
	if ($promotion_popup) {
		$promotion_dialog_markup = promotion_dialog_markup(
			$href_this,
			$current_player,
			$t_row, $t_col,
			$history
		);
		$clickable = $selected = array();
	} else {
		$promotion_dialog_markup = '';
	}

	// Board
	if ((STEADY_BOARD) && ($current_player == BLACKS_MOVE)) {
		//... GET switch
		$flip_board = ! $flip_board;
	}

	if (isset( $_GET[GET_GOTO] )) {
		$clickable = $selected = array();
	}
	else if (($history > '') && ($cmd_from == $cmd_to)) {
		if (substr($history, -4, 1) == '(') {
			// Skip promotion
			$selected[] = decode_field( substr($history, -3, 1) );
			$selected[] = decode_field( substr($history, -6, 1) );
		} else {
			// Normal move
			$selected[] = decode_field( substr($history, -2, 1) ); 
			$selected[] = decode_field( substr($history, -1, 1) ); 
		}
	}

	$chess_board_markup = chess_board_markup(
		$href_this,
		$board_array,
		$clickable,
		$selected,
		$current_player,
		$flip_board
	);


	// If no heading was set above, say who's next

	if ($goto != '') {
		$current_player = (($goto % 2) != 0);
	}

	if ($heading == '') {
		$heading = ($current_player) ? $name_white : $name_black ;
		$heading = ucfirst($heading) . "'";
		if (substr($heading, -2, 1) != 's') $heading .= 's';
		$heading .= ' move';
	}

	if( king_in_check( $board_array, $current_player )
	&&  (strpos( $heading, 'mate') === false)
	) {
		$heading .= ' - <strong>Check!</strong>';
	}

	if ($goto != '') {
		$turn_nr = goto_to_round( $history, $goto );
		$heading = "Round $turn_nr, $heading";
	}

	$heading = get_parameter( GET_COMMENT, $heading );


	// Game title

	if (isset( $_GET[GET_WHITE] )) {
		$turn_nr = i_to_round( $history );
		$game_title = "$name_white vs. $name_black - Turn #$turn_nr - ";
	}

	$turn_nr = floor($turn_nr);


	// History links

	$next_goto = ($goto + 1) % (count_moves($history) + 1);
	$history_next = update_href(
		$href_this,
		GET_GOTO,
		$next_goto
	);

	$next_goto = ($goto + count_moves($history)) % (count_moves($history) + 1);
	$history_prev = update_href(
		$href_this,
		GET_GOTO,
		$next_goto
	);


	// Links for copy and paste

	if( (! isset( $_GET[GET_NEW_GAME] ))     // Don't show for first move
	&&  (! isset( $_GET[GET_BASE_BOARD] ))   // Base board given? Riddle.
	&&  (isset($_SERVER['HTTP_REFERER']) )   // Don't show after click
	&&  ($_SERVER['QUERY_STRING'] != '')
	&&  ($cmd_from == '')                    // Don't show while ..
	&&  ($cmd_to == '')                      // .. command being entered
	) {
		// Empty referer: Not reached by clicking a link in the browser

		$t = str_replace( '&amp;', '&', $href_this );
		$game_state_link = str_replace( ' ', '+', $t );

		$home_IPs = array( '192.168.14.1', '213.47.94.176', 'local.at' );
		foreach( $home_IPs as $ip_address ) {
			if (strpos($game_state_link, $ip_address) !== false) {
				$hmw_home_link = str_replace(
					$ip_address,
					'harald.ist.org/home',
					$game_state_link
				);
			}
		}

		if ($hmw_home_link != '') {
			$game_state_link = $hmw_home_link;
		}

		$game_state_link = str_replace( 'flip&', '', $game_state_link );
		$game_state_link = str_replace( '&', '&amp;', $game_state_link );
	}

debug_out("board_encoded = $board_encoded\n");

} // main_conrol


# EOF ?>