<?php /* definitions.php */ if (!isset($VERSION)) die('Include only.');
/******************************************************************************
* REMOTE CHESS - DEFINITIONS
******************************************************************************/

## OPTIONS ####################################################################

define( 'DEBUG', !false );

define( 'USE_UNICODE_GLYPHS', true );
define( 'RECONSTRUCT_FROM_HISTORY', true );
define( 'HISTORY_PROMPT', !false );

define( 'DEFAULT_NAME_WHITE', 'A. White' );
define( 'DEFAULT_NAME_BLACK', 'B. Black' );


## CONSTANTS ##################################################################

define( 'WHITES_MOVE', true );
define( 'BLACKS_MOVE', false );

/**
 * If these change, check:
 *  game_logic.php:apply_move()
 *  movement_rules.php:possible_move_list()
 *  generate_markup.php:piece_class_name()
 */
define( 'WHITE_PIECES', 'PSRNBQLK' );
define( 'BLACK_PIECES', 'psrnbqlk' );

// Used for nicer function calls
define( 'REMOVE_FROM_LINK', 'REMOVE_FROM_LINK' );   // Remove a GET parameter
define( 'BOARD_FLIPPED', true );


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
define( 'GET_REDIRECT',   'redirect'  );
define( 'GET_NEW_GAME',   'newgame'   );


/**
 * GET_PARAMETER_ORDER
 * Parameters not listed here will be ignored by
 *  update_href()  in  url_helpers.php !
 */
define( 'GET_PARAMETER_ORDER', 'flip player base history enpassant white black from to redirect' );


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
	. '.:'         // "." = taken by white, ":" = taken by black
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
 * TEST LINK
 */
IF (RECONSTRUCT_FROM_HISTORY):
/*EnPassant*/	#define( 'TEST_LINK', '?player=black&history=mC5QCK&white=A.+White&black=B.+Black&from=F7' );
/*Castling*/	define( 'TEST_LINK', '?player=white&history=mC5QCK1LdE2UlB*TbqTNcV9VfAZRgvQz&white=A.+White&black=B.+Black&from=E1' );
ELSE:
	define( 'TEST_LINK', '?player=black&history=lB5QBJ&white=A.+White&black=B.+Black&base=SaNbBcQdLeBfNgShPiPjPkPmPnPoPpPJnQpWpXpYpZp0p1p2p3s4b6q7l8b9n*s$' );
ENDIF


# EOF ?>