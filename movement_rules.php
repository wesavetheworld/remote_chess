<?php /* movement_rules.php */ if (!isset($VERSION)) die('Include only.');
/******************************************************************************
* REMOTE CHESS - Copy(L)eft 2015                         http://harald.ist.org/
* MOVEMENT RULES
******************************************************************************/

/**
 * Constants
 */
define( 'TARGET_OUT_OF_BOUNDS', -1 );
define( 'EMPTY_FIELD', 0 );
define( 'PLAYERS_PIECE', 1 );
define( 'OPPONENTS_PIECE', 2 );


/******************************************************************************
* AIDING FUNCTIONS
******************************************************************************/

/**
 * get_player_pieces() - Returns letter codes for the current player
 */
function get_player_pieces( $player )
{
	return ($player == WHITES_MOVE) ? WHITE_PIECES : BLACK_PIECES ;
}


/**
 * check_target() - See, if target is friend, foe, empty or outside of board
 */
function check_target( $board_array, $current_player, $row, $col )
{
	$ret = EMPTY_FIELD;   // No target, empty field

	$player_pieces = get_player_pieces( $current_player );

	if (($row < 0) || ($row > 7) || ($col < 0) || ($col > 7)) {
		return TARGET_OUT_OF_BOUNDS;
	}

	$target = $board_array[$row][$col];

	if ($target != '') {
		if (strpos($player_pieces, $target) !== false) {
			$ret = PLAYERS_PIECE;
		} else {
			$ret = OPPONENTS_PIECE;
		}
	}

	return $ret;

} // check_target


/**
 * check_move() - Checks, if a move would leave the king in check
 */
function check_move( $board_array, $current_player,  $f_row, $f_col,  $t_row, $t_col )
{
	//...
	// Create board with move applied
	// Build opponent's possible moves
	// See, if king can be captured

	return true;
}


/******************************************************************************
* PIECE MOVEMENT RULES
******************************************************************************/

/**
 * generic_move_list()
 * Checks, if any of  $moves  is possible. Used by the King, Knight, //...Pawn?
 */
function generic_move_list( $board_array, $current_player, $field, $moves )
{
	$ret = Array( $field );   // Add current piece for deselection link
	list( $row, $col ) = field_to_rowcol( $field );

	for( $i = 0 ; $i < count($moves) ; $i++ ) {
		$offset_r = $moves[$i][0];
		$offset_c = $moves[$i][1];
		$target = check_target(
			$board_array,
			$current_player,
			$row + $offset_r,
			$col + $offset_c
		);

		if (($target == EMPTY_FIELD) || ($target == OPPONENTS_PIECE)) {

			//... Test: Move not ending in king being in check?

			$ret[] = rowcol_to_field(
				$row + $offset_r,
				$col + $offset_c
			);
		}
	}

	return $ret;
}


/******************************************************************************
* PIECE SPECIFIC FUNCTIONS
******************************************************************************/

function pawn_move_list( $board_array, $current_player, $field )
{
	$ret = Array( $field );   // Add current piece for deselection link
	list( $row, $col ) = field_to_rowcol( $field );

	$dir = ($current_player == WHITES_MOVE) ? +1 : -1 ;

	if (check_target(
		$board_array,
		$current_player,
		$row+$dir*1, $col
		) == EMPTY_FIELD
	) {
		//... Test: Move not ending in king being in check?
		$ret[] = rowcol_to_field( $row+$dir*1, $col );

		$is_initial_move
		= ($current_player == WHITES_MOVE) && ($row == 1)
		||($current_player == BLACKS_MOVE) && ($row == 6)
		;

		if ($is_initial_move) {
			if (check_target(
				$board_array,
				$current_player,
				$row+$dir*2, $col
				) == EMPTY_FIELD
			) {
				//... Test: Move not ending in king being in check?

				//... En passant for opponent next move?

				$ret[] = rowcol_to_field( $row+$dir*2, $col );
			}
		}
	}


	// Capture diagonaly

	if (check_target(
		$board_array,
		$current_player,
		$row+$dir*1, $col+1
		) == OPPONENTS_PIECE
	) {
		//... Test: Move not ending in king being in check?
		$ret[] = rowcol_to_field( $row+$dir*1, $col+1 );
	}

	if (check_target(
		$board_array,
		$current_player,
		$row+$dir*1, $col-1
		) == OPPONENTS_PIECE
	) {
		//... Test: Move not ending in king being in check?
		$ret[] = rowcol_to_field( $row+$dir*1, $col-1 );
	}


	// En passant
	
	if (($row == 3) || ($row == 4)) {

		$e = get_parameter( GET_EN_PASSANT );
		$e = ord($e) - ord('A');

		if (($e == $col+1) || ($e == $col-1)) {
			if ($col > 0) {   // check to the left
				if (check_target(
					$board_array,
					$current_player,
					$row, $col-1
					) == OPPONENTS_PIECE
				) {
					$ret[] = rowcol_to_field( $row+$dir*1, $col-1 );
				}
			}

			if ($col < 7) {   // check to the right
				if (check_target(
					$board_array,
					$current_player,
					$row, $col+1
					) == OPPONENTS_PIECE
				) {
					$ret[] = rowcol_to_field( $row+$dir*1, $col+1 );
				}
			}
		}
	}

	return $ret;

} // pawn


function rook_move_list( $board_array, $current_player, $field )
{
	$ret = Array( $field );   // Add current piece for deselection link
	list( $row, $col ) = field_to_rowcol( $field );

	$dir = Array( true, true, true, true );

	for( $i = 1 ; $i < 8 ; $i++ ) {
		if ($dir[0]) {
			$target = check_target(
				$board_array,
				$current_player,
				$row, $col+$i
			);

			if(($target == EMPTY_FIELD)
			|| ($target == OPPONENTS_PIECE)
			) {
				//... Test: Move not ending in king being in check?
				$ret[] = rowcol_to_field( $row, $col+$i );
				if ($target == OPPONENTS_PIECE) {
					$dir[0] = false;
				}
			} else {
				$dir[0] = false;
			}
		}
		if ($dir[1]) {
			$target = check_target(
				$board_array,
				$current_player,
				 $row, $col-$i
			);

			if(($target == EMPTY_FIELD)
			|| ($target == OPPONENTS_PIECE)
			) {
				//... Test: Move not ending in king being in check?
				$ret[] = rowcol_to_field( $row, $col-$i );
				if ($target == OPPONENTS_PIECE) {
					$dir[1] = false;
				}
			} else {
				$dir[1] = false;
			}
		}
		if ($dir[2]) {
			$target = check_target(
				$board_array,
				$current_player,
				$row+$i, $col
			);
			if(($target == EMPTY_FIELD)
			|| ($target == OPPONENTS_PIECE)
			) {
				//... Test: Move not ending in king being in check?
				$ret[] = rowcol_to_field( $row+$i, $col );
				if ($target == OPPONENTS_PIECE) {
					$dir[2] = false;
				}
			} else {
				$dir[2] = false;
			}
		}
		if ($dir[3]) {
			$target = check_target(
				$board_array,
				$current_player,
				$row-$i, $col
			);
			if(($target == EMPTY_FIELD)
			|| ($target == OPPONENTS_PIECE)
			) {
				//... Test: Move not ending in king being in check?
				$ret[] = rowcol_to_field( $row-$i, $col );
				if ($target == OPPONENTS_PIECE) {
					$dir[3] = false;
				}
			} else {
				$dir[3] = false;
			}
		}
	}

	return $ret;

} // rook


function knight_move_list( $board_array, $current_player, $field )
{
	$moves = Array(
		Array( +2, -1 ), Array( +2, +1 ),
		Array( -2, -1 ), Array( -2, +1 ),
		Array( -1, +2 ), Array( +1, +2 ),
		Array( -1, -2 ), Array( +1, -2 ),
	);

	return generic_move_list(
		$board_array,
		$current_player,
		$field,
		$moves
	);

} // knight


function bishop_move_list( $board_array, $current_player, $field )
{
	$ret = Array( $field );   // Add current piece for deselection link
	list( $row, $col ) = field_to_rowcol( $field );

	$dir = Array( true, true, true, true );

	for( $i = 1 ; $i < 8 ; $i++ ) {
		if ($dir[0]) {
			$target = check_target(
				$board_array,
				$current_player,
				$row+$i, $col+$i
			);

			if(($target == EMPTY_FIELD)
			|| ($target == OPPONENTS_PIECE)
			) {
				$ret[] = rowcol_to_field( $row+$i, $col+$i );
				if ($target == OPPONENTS_PIECE) {
					$dir[0] = false;
				}
			} else {
				$dir[0] = false;
			}
		}
		if ($dir[1]) {
			$target = check_target(
				$board_array,
				$current_player,
				 $row-$i, $col-$i
			);

			if(($target == EMPTY_FIELD)
			|| ($target == OPPONENTS_PIECE)
			) {
				$ret[] = rowcol_to_field( $row-$i, $col-$i );
				if ($target == OPPONENTS_PIECE) {
					$dir[1] = false;
				}
			} else {
				$dir[1] = false;
			}
		}
		if ($dir[2]) {
			$target = check_target(
				$board_array,
				$current_player,
				$row+$i, $col-$i
			);
			if(($target == EMPTY_FIELD)
			|| ($target == OPPONENTS_PIECE)
			) {
				$ret[] = rowcol_to_field( $row+$i, $col-$i );
				if ($target == OPPONENTS_PIECE) {
					$dir[2] = false;
				}
			} else {
				$dir[2] = false;
			}
		}
		if ($dir[3]) {
			$target = check_target(
				$board_array,
				$current_player,
				$row-$i, $col+$i
			);
			if(($target == EMPTY_FIELD)
			|| ($target == OPPONENTS_PIECE)
			) {
				$ret[] = rowcol_to_field( $row-$i, $col+$i );
				if ($target == OPPONENTS_PIECE) {
					$dir[3] = false;
				}
			} else {
				$dir[3] = false;
			}
		}
	}

	return $ret;

} // bishop


function queen_move_list( $board_array, $current_player, $field )
{
	$rook_moves = rook_move_list( $board_array, $current_player, $field );
	$bishop_moves = bishop_move_list( $board_array, $current_player, $field );

	unset( $bishop_moves[0] );

	$ret = array_merge( $rook_moves, $bishop_moves );

	return $ret;

} // queen


function king_move_list( $board_array, $current_player, $field )
{
	$moves = Array(
		Array( +0, -1 ), Array( +0, +1 ),
		Array( +1, -0 ), Array( -1, +0 ),
		Array( -1, -1 ), Array( -1, +1 ),
		Array( +1, -1 ), Array( +1, +1 ),
	);

	$ret = generic_move_list(
		$board_array,
		$current_player,
		$field,
		$moves
	);

	// Castling

	list( $row, $col ) = field_to_rowcol( $field );
	$dir = ($current_player == WHITES_MOVE) ? true : false ;

	$open_queenside =
	(	($board_array[$dir ? 0 : 7][0] == 's')
	||	($board_array[$dir ? 0 : 7][0] == 'S')
	)
	&&	($board_array[$dir ? 0 : 7][1] == '')
	&&	($board_array[$dir ? 0 : 7][2] == '')
	&&	($board_array[$dir ? 0 : 7][3] == '')
	;

	$open_kingside =
	(	($board_array[$dir ? 0 : 7][7] == 's')
	||	($board_array[$dir ? 0 : 7][7] == 'S')
	)
	&&	($board_array[$dir ? 0 : 7][6] == '')
	&&	($board_array[$dir ? 0 : 7][5] == '')
	;

	if (true  || 'line not under attack') {
		if ($open_queenside) {
			$ret[] = rowcol_to_field( $row, $col-2 );
		}
		if ($open_kingside) {
			$ret[] = rowcol_to_field( $row, $col+2 );
		}
	}

	return $ret;

} // king


/******************************************************************************
* FIND TARGET FIELDS
******************************************************************************/

/**
 * possible_move_list() - returns fields a piece can move to
 */
function possible_move_list( $board_array, $current_player, $field )
{
	$ret = Array();
	list( $row, $col ) = field_to_rowcol( $field );

	$piece_codes = get_player_pieces( $current_player );

	$piece = $board_array[$row][$col];
	if ($piece == '') {
		die( "Error: possible_move_list(): board_array[$row][$col] Empty field selected?" );
	}

	$b = $board_array;
	$c = $current_player;
	$f = $field;

	if (strpos($piece_codes, $piece) !== false) {
		switch( strtolower($piece) ) {
			case 'p': $ret = pawn_move_list( $b, $c, $f ); break;
			case 's': // Fall through: Not yet mode rook
			case 'r': $ret = rook_move_list( $b, $c, $f ); break;
			case 'n': $ret = knight_move_list( $b, $c, $f ); break;
			case 'b': $ret = bishop_move_list( $b, $c, $f ); break;
			case 'q': $ret = queen_move_list( $b, $c, $f ); break;
			case 'l': // Fall through: Not yet moved king
			case 'k': $ret = king_move_list( $b, $c, $f ); break;
			default: die( 'Error: possible_move_list(): unknown piece.' );
		}
	}

	return $ret;

} // possible_move_list


/**
 * find_movable_pieces() - Creates  $clickable (Array of field names)
 * Checks the board for pieces that can be moved.
 * If all your pieces are boxed in or any move would result in your king to
 * get into check, an empty array is returned, indicating the end of the game.
 */
function find_movable_pieces( $board_array, $current_player )
{
	$ret = Array();
	$player_pieces = get_player_pieces( $current_player );

	for( $row = 0 ; $row < 8 ; $row++ ) {
		for( $col = 0 ; $col < 8 ; $col++) {

			$piece = $board_array[$row][$col];

			if(($piece != '')
			&& (strpos($player_pieces, $piece) !== false)
			) {
				$field = rowcol_to_field( $row, $col );

				// Check, if piece can move at all
				$moves = possible_move_list(
					$board_array,
					$current_player,
					$field
				);

				// First entry of the returned move list is
				// the FIELD of the currently checked piece:
				if (isset( $moves[1] )) {
					$ret[] = $field; //...$moves[0];
				}

			}
		}
	}

	return $ret;

} // find_movable_pieces


/******************************************************************************
* CHECK for CHECK
******************************************************************************/

/**
 * king_in_check() - return true, if king is in check
 */
function king_in_check( $board_array, $current_player )
{
	$ret = false;
	$king_field = find_king( $board_array, $current_player );

	$opponents_pieces = find_movable_pieces(
		$board_array,
		! $current_player,
		true
	);

	// See, if any of the opponents pieces can capture the player's king
	foreach( $opponents_pieces as $o ) {
		$captures = possible_move_list(
			$board_array,
			! $current_player,
			$o
		);

		$ret |= in_array( $king_field, $captures );
	}

	return $ret;

} // king_in_check


# EOF ?>