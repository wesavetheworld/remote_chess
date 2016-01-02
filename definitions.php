<?php /* definitions.php */ if (!isset($VERSION)) die('Include only.');
/******************************************************************************
* REMOTE CHESS - Copy(L)eft 2015                         http://harald.ist.org/
* DEFINITIONS
******************************************************************************/

/******************************************************************************
* OPTIONS
******************************************************************************/

define( 'DEBUG', !false );                      // Show  debug_out()  messages
define( 'DEBUG_TO_FILE', false );              // Write messages to file
define( 'DEBUG_FILE_NAME', '../debug.log' );

// TRUE: Use <a href="..."> instead of <span> for the moves:
define( 'HISTORY_LINK_BACK', isset($_GET['goto']) );   // Link each single move

// TRUE: Insert </ul><ul> every 20 moves, breaking the list into columns:
define( 'HISTORY_BREAK_UL', isset($_GET['goto']) );    // Non-semantic cheat

define( 'USE_UNICODE_GLYPHS', true );          // Use special characters
define( 'STEADY_BOARD', false );               // true: Black plays downwards,
                                               // board never flips
                                              
define( 'DEFAULT_NAME_WHITE', 'A. White' );    // Preset names for new games
define( 'DEFAULT_NAME_BLACK', 'B. Black' );


/******************************************************************************
* CONSTANTS
******************************************************************************/

define( 'WHITES_MOVE', true );     //... This could be done nicer, there are..
define( 'BLACKS_MOVE', false );    //... .. too many black/white related names

define( 'IN_PROGRESS', '<b>?</b>' );
define( 'WHITE_WINS', '<strong>1 &mdash; 0</strong>' );   // Used for output and for signaling
define( 'BLACK_WINS', '<strong>0 &mdash; 1</strong>' );
define( 'NOONE_WINS', '<strong>&frac12; &mdash; &frac12;</strong>' );

define( 'HEADING_CHECK_MATE', '<strong>Checkmate!</strong>' );
define( 'HEADING_STALE_MATE', '<strong>Stalemate!</strong>' );

/**
 * If these change, check:
 * - game_logic.php
 *	apply_move()
 * - movement_rules.php
 *	possible_move_list()
 * - generate_markup.php
 *	piece_class_name()
 *	piece_glyph()
 * Successing letters placed before their predecessor are used to indicate
 * not yet having been moved (S, L).
 */
define( 'WHITE_PIECES', 'PSRNBQLK' );
define( 'BLACK_PIECES', 'psrnbqlk' );

define( 'PIECE_ROOK',   2 );   // Indices to the above strings
define( 'PIECE_KNIGHT', 3 );
define( 'PIECE_BISHOP', 4 );
define( 'PIECE_QUEEN',  5 );


// Used for nicer function calls
define( 'REMOVE_FROM_LINK', 'REMOVE_FROM_LINK' );   // Remove a GET parameter


/**
 * GET parameter names
 */
define( 'GET_FLIP_BOARD', 'flip'      );
define( 'GET_PLAYER',     'player'    );
define( 'GET_BASE_BOARD', 'base'      );
define( 'GET_HISTORY',    'history'   );
define( 'GET_EN_PASSANT', 'enpassant' );
define( 'GET_WHITE',      'white'     );
define( 'GET_BLACK',      'black'     );
define( 'GET_FROM',       'from'      );
define( 'GET_TO',         'to'        );
define( 'GET_NEW_GAME',   'newgame'   );
define( 'GET_PROMOTE',    'promote'   );
define( 'GET_SELECT',     'select'    );
define( 'GET_COMMENT',    'comment'   );
define( 'GET_GOTO',       'goto'      );
// Update GET_PARAMETER_ORDER, if you add or change any parameters above!
/**
 * GET_PARAMETER_ORDER
 * Parameters not listed here will be ignored by
 *  update_href()  in  url_helpers.php !
 */
define( 'GET_PARAMETER_ORDER',
	'flip player base history enpassant promote select white black from to goto'
	. ' comment'
);


/**
 * LOCATION_CODES
 * To reduce GET parameter length, locations are coded with one char:
 */
define( 'LOCATION_CODES',
	  'abcdefgh'   // Row 1, a = A1, b = B1, ...
	. 'ijklmnop'   // Row 2, i = A2
	. 'qrstuvwx'
	. 'yzABCDEF'
	. 'GHIJKLMN'
	. 'OPQRSTUV'
	. 'WXYZ0123'
	. '456789*$'   // Row 8, ")" = H8
	. '.:_'
		// "." = taken by white, ":" = taken by black
		// Underscores ("_") are used for history markup
);


/**
 * INITIAL_BOARD_CODED
 * List of pieces plus location code, 2 chars per piece
 * White pieces: r, n, b, q, k
 * Black pieces: R, N, B, Q, K
 */
define( 'INITIAL_BOARD_CODED',
	  'SaNbBcQdLeBfNgSh'   // Row 1, A1..H1
	. 'PiPjPkPlPmPnPoPp'   // 2
	. 'pWpXpYpZp0p1p2p3'   // 7
	. 's4n5b6q7l8b9n*s$'   // 8
);


/**
 * CHESS_RIDDLES
 */
// http://derstandard.at/2000004889739/Loesungen-Schachraetsel-2206-2207-2208 (This is 2207)
define( 'CHESS_RIDDLE', "?player=white&base=QzkJKTNWpXpZn5&comment=White+moves+and+checkmates+in+two+turns" );


/**
 * TEST LINK
 */
$TEST_LINKS = array(
	'En Passant' => '?player=black&history=mC5QCK1LnD&enpassant=F&white=A.+White&black=B.+Black&from=D7',
	'Castle'     => '?player=white&history=mC5QCK1LdE2UlB*TbqTNcV9VfAZRgvQz&white=A.+White&black=B.+Black&from=E1',
	'Promotion'  => '?player=black&history=mC5QCK1LdE2UlB*TbqTNcV9VfAZRgvQzKR7ZecZyhe8*RZ0Kdl91qHKCHqCuedumBJ&white=A.+White&black=B.+Black',
	'Check Mate' => '?player=white&white=A.+White&black=B.+Black&base=KePmpuqRq2k8',
	'Stale Mate' => '?player=white&history=lBZJksYIBI6Lgv7GdJ*TJXLCX6G76787vKTJcM1TMDTKDKJDK5DtmtCL5D2UtB0KDK92fH$8H878K2LCnvCteltLhe812K49KR3Ne01*RK910W1WIQW0KRLCbqUMR0CLQYLUY6(6Q)U865MEvENFEMFxBJxoqHog(gq)5K*3HY3UY8gapFaiKTUN82NF0RijltjHsAHJAJFETDEx2NxoMUoxU2xoDuofulfg2*(*Q)gh&white=A.+White&black=B.+Black',
	'History'    => '?player=black&history=lBZJksYIBI6Lgv7GdJ*TJXLCX6G76787vKTJcM1TMDTKDKJDK5DtmtCL5D2UtB0KDK92fH$8H878K2LCnvCteltLhe812K49KR3Ne01*RK910W1WIQW0KRLCbqUMR0CLQYLUY6(6Q)U865MEvENFEMFxBJxoqHog(gq)5K&white=A.+White&black=B.+Black',
	'Queens'     => '?base=QbQlQvQFQIQOQ2Q8',
);


# EOF ?>