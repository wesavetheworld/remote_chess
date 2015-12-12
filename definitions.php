<?php /* definitions.php */ if (!isset($VERSION)) die('Include only.');
/******************************************************************************
* REMOTE CHESS - DEFINITIONS
*******************************************************************************
* https://en.wikipedia.org/wiki/Portable_Game_Notation
* https://en.wikipedia.org/wiki/Forsyth%E2%80%93Edwards_Notation
* https://www.reddit.com/r/dailyprogrammer/comments/3t0xdw/20151116_challenge_241_easy_unicode_chess/
*******************************************************************************
* https://en.wikipedia.org/wiki/Chess
* https://en.wikipedia.org/wiki/Castling
* https://en.wikipedia.org/wiki/Promotion_%28chess%29
* https://en.wikipedia.org/wiki/En_passant
* https://en.wikipedia.org/wiki/Checkmate
* https://en.wikipedia.org/wiki/Stalemate
* https://en.wikipedia.org/wiki/Draw_%28chess%29
* https://en.wikipedia.org/wiki/Chess_composition
* https://en.wikipedia.org/wiki/Chess_symbols_in_Unicode
*******************************************************************************
*  +--+--+--+--+--+--+--+--+         BLACK          LOCATION_CODES
*  |A8|B8|C8|D8|E8|F8|G8|H8|    r n b q k b n r     4 5 6 7 8 9 * $
*  +--+--+--+--+--+--+--+--+
*  |A7|B7|C7|D7|E7|F7|G7|H7|    p p p p p p p p     W X Y Z 0 1 2 3
*  +--+--+--+--+--+--+--+--+
*  |A6|B6|C6|D6|E6|F6|G6|H6|   s:NYM rook           O P Q R S T U V
*  +--+--+--+--+--+--+--+--+   l:NYM king
*  |A5|B5|C5|D5|E5|F5|G5|H5|   e:Pawn en passant    G H I J K L M N
*  +--+--+--+--+--+--+--+--+     possible next
*  |A4|B4|C4|D4|E4|F4|G4|H4|     turn               y z A B C D E F
*  +--+--+--+--+--+--+--+--+
*  |A3|B3|C3|D3|E3|F3|G3|H3|   S,L,E respektively   q r s t u v w x
*  +--+--+--+--+--+--+--+--+
*  |A2|B2|C2|D2|E2|F2|G2|H2|    P P P P P P P P     i j k l m n o p
*  +--+--+--+--+--+--+--+--+
*  |A1|B1|C1|D1|E1|F1|G1|H1|    R N B Q K B N R     a b c d e f g h
*  +--+--+--+--+--+--+--+--+         WHITE
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


/**
 * GET_PARAMETER_ORDER
 * Parameters not listed here will be ignored by
 *  update_href()  in  definitions.php !
 */
define( 'GET_PARAMETER_ORDER', 'flip player base history enpassant white black from to' );


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
//define( 'TEST_LINK', '?&player=white&history=lB0Kks5Q&white=A&black=B' );
define( 'TEST_LINK', '?player=white&history=lB0Kks5Q&white=A&black=B&base=ranbbcqdkebfngrhpjpkplpmpnpoppPWPXPYPZP0P1P2P3R4N5B6Q7K8B9N*R$' );


# EOF ?>