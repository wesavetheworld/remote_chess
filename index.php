<?php /* index.php */ $VERSION = '&alpha;0.1.7';
/******************************************************************************
* REMOTE CHESS - MAIN SCRIPT and HTML TEMPLATE
*******************************************************************************
* Copyleft Tue, Dec 8, 2015 by http://harald.ist.org/
*******************************************************************************
* Every state of a game is represented by a URL, which can be bookmarked.
* Players enter their move and send the resulting link to their chess partners,
* in order for them to continue the game likewise.
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
*  |A6|B6|C6|D6|E6|F6|G6|H6|                        O P Q R S T U V
*  +--+--+--+--+--+--+--+--+
*  |A5|B5|C5|D5|E5|F5|G5|H5|   S, s: Rook, n.y.m.   G H I J K L M N
*  +--+--+--+--+--+--+--+--+   L, l: King, n.y.m.
*  |A4|B4|C4|D4|E4|F4|G4|H4|                        y z A B C D E F
*  +--+--+--+--+--+--+--+--+
*  |A3|B3|C3|D3|E3|F3|G3|H3|                        q r s t u v w x
*  +--+--+--+--+--+--+--+--+
*  |A2|B2|C2|D2|E2|F2|G2|H2|    P P P P P P P P     i j k l m n o p
*  +--+--+--+--+--+--+--+--+
*  |A1|B1|C1|D1|E1|F1|G1|H1|    R N B Q K B N R     a b c d e f g h
*  +--+--+--+--+--+--+--+--+         WHITE
******************************************************************************
* TODO
* - Name of players and move number in title for nicer bookmarking
* . Movement rules: En passant, castles, pawn at top
* . Capture: Utilize "Taken by player" locations "." and ":"
* ^ Flag: En Passant allowed next turn
* ^ Flag: Castling only if king not moved yet
* ^ Pawn reaches top: Menu for selecting new piece
* - Start date and last move date in URL
* - History: Show En Passant
* - History: $href_history[$steps] for links back, built when building history
* - History: GET: goto (history)
* - History: Implement  HISTORY_PROMPT  in new history_markup()
* . castle king/queenside gets each own code (piece) character (history)
* ^ Also applies to switching pawns into other pieces - or use  GET_BASE_BOARD ?
* - Detect check mate
* - Proper error messages instead of  die() .
* - Respond to improper input verbously
* - Select valid piece of opponent: No error is shown!
*******************************************************************************
* OPTIONAL
* - Speech bubbles with user comments
* - Mark "from" field
* - Semantic markup for history
* ? Nice URL: from == to? --> HTTP Redirect!
* - GET toggle to show URL to send, URL does not contain that "show" toggle: "Send this"/"Your Move"
* - Compressed URL: Coordinate only, if piece not adjacent (like text terminal)
* ?  Compressed URL: History: Remove redundant values
* - Chess riddles $riddle[code]=description html
* - Editor mode: from = piece code, to = field: create piece, delete: "to" only?
* - Output: coded / clear link or cheat option or editor
* - GET param: move_nr (when using base)
* - Pack GET parameters into text block and provide textarea
* - Taken by player: visual list of removed pieces
* . Provide everything needed to input the move in initial page (all moves
* ^ list), in order to prevent reloads while entering one's move (JS?)
* - Play against AI
*******************************************************************************
* RE-CHECK
* - Both modes: RECONSTRUCT_FROM_HISTORY
* - Sync GET parameter order between update_href and form in index.php
* - Replace all hardcoded strings with constants (GET_TO, GET_FROM, ...)
* - Fix everything marked with  //...
******************************************************************************/
$debug_html = 'Remote Chess ' . $VERSION;
function debug_out( $message )
{	global $debug_html;
	$debug_html .= $message;
}
function debug_array( $array )
{	ob_start();
	print_r( $array );
	debug_out( ob_get_clean() );
}
/*****************************************************************************/

//... Log everything! No errors to the browser!
set_time_limit( 2 );   // Maximum script run time, stops endless loops

include 'definitions.php';       // Constant values, signal names
include 'game_logic.php';        // Main game control
include 'movement_rules.php';    // Find fields a piece can move to
include 'url_helpers.php';       //  update_parameters() , etc.
include 'generate_markup.php';   // Output to HTML

main_control();   // see  game_logic.php


/******************************************************************************
* OUTPUT TO BROWSER - after  main_control()  returns, markup is built and sent
******************************************************************************/

################################################################ COMMON HEAD ?>
<!DOCTYPE html><html id="top" lang="en"><head><meta charset="utf-8">
<title><?= $game_title ?>Remote Chess - <?= $VERSION ?></title>
<meta name="author" content="Harald Markus Wirth, http://harald.ist.org/">
<meta name="description" content="Web service for playing chess via e-mail or instant messenger. No login required.">
<meta name="keywords" content="remote,mail,chess">
<meta name="robots" content="index,follow">
<link rel="stylesheet" type="text/css" href="default.css">
<link rel="alternate stylesheet" type="text/css" href="three_d.css" title="Perspective">
<link rel="shortcut icon" href="chess-icon.png">
<script type="text/javascript" src="chess_board.js"></script>
<script type="text/javascript" src="style_switcher.js"></script>
<? IF ($_SERVER['QUERY_STRING'] == ''): ########################### NEW GAME ?>
</head><body id="new_game">

<form action="./" method="get" accept-charset="utf-8">
<h1>Remote Chess</h1>
<p>Enter names:</p>
<p class="names">
	<label for="idWhite" class="nocss"><?= ucfirst(GET_WHITE) ?>:</label>
	<input type="text" id="idWhite" name="white" value="<?= $name_white ?>">
	vs.
	<label for="idBlack" class="nocss"><?= ucfirst(GET_BLACK) ?>:</label>
	<input type="text" id="idBlack" name="black" value="<?= $name_black ?>">
</p>
<p>
	<label for="idSubmit" class="nocss">Submit:</label>
	<input type="submit" value="Start Game">
</p>
<input type="hidden" name="<?= GET_NEW_GAME ?>">
<script type="text/javascript"> document.getElementById('idWhite').select(); </script>
</form>

<? ELSE: ####################################################### CHESS BOARD ?>
</head><body id="chess_board">

<header>
<h1>Remote Chess</h1>
</header>

<h2><?= $heading ?></h2>
<? IF ($promotion_popup): ?>
<ul class="popup">
<li><a href=""><div class="white rook" title="White rook">R</div></a>
<li><a href=""><div class="white knight" title="White knight">N</div></a>
<li><a href=""><div class="white bishop" title="White bishop">B</div></a>
<li><a href=""><div class="white queen" title="White queen">Q</div></a>
</ul>

<? ENDIF ?>
<?= $chess_board_markup; ?>

<? IF ($show_command_form): ?>
<form action="./" method="get" accept-charset="utf-8">
<? IF ($flip_board): ?>
<input type="hidden" name="<?= GET_FLIP_BOARD ?>" value="">
<? ENDIF ?>
<input type="hidden" name="<?= GET_PLAYER ?>" value="<?= ($current_player == WHITES_MOVE) ? GET_WHITE : GET_BLACK ?>">
<input type="hidden" name="<?= GET_HISTORY ?>" value="<?= $history ?>">
<input type="hidden" name="<?= GET_WHITE ?>" value="<?= $name_white ?>">
<input type="hidden" name="<?= GET_BLACK ?>" value="<?= $name_black ?>">
<input type="hidden" name="<?= GET_BASE_BOARD ?>" value="<?= $board_encoded ?>">
<? IF (!$promotion_popup): ?>
<p class="move">
	Move
	<label for="idFrom">from:</label>
	<input type="text" id="idFrom" name="from" value="<?= $preset_from_value ?>">
	<label for="idTo">to:</label>
	<input type="text" id="idTo" name="to" value="<?= $preset_to_value ?>">
	<label for="idSubmit" class="nocss">Submit:</label>
	<input type="submit" id="idSubmit" value="Submit">
</p>
<? ENDIF ?>
<? IF ($id_focus != ''): ?>
<script type="text/javascript"> document.getElementById('<?= $id_focus ?>').focus(); </script>
<? ENDIF ?>
</form>

<? ENDIF ?>
<?= $history_markup ?>

<? IF ($game_state_link != ''): ?>
<h2>Send this link:</h2>
<p class="game_state_link">
	<a href="<?= $game_state_link ?>"><?= $game_state_link ?></a>
</p>

<? ENDIF ?>
<footer>
	
</footer>

<?/* IF ($hmw_home_link != ''): ?>
<p class="game_state_link">
	<a href="<?= $hmw_home_link ?>"><?= $hmw_home_link ?></a>
</p>

<? ENDIF */?>
<nav><ul>
<li><button onclick="toggleStyle()">Switch Style</button>
<li><a href="<?= $href_flip ?>">Flip Board</a>
<li><a href="<?= $href_player ?>">Switch Sides</a>
<li><a href="./">New Game</a>
<li><a href="<?= $href_test ?>">Test</a>
</ul></nav>

<? IF (DEBUG): ?>
<pre class="debug">
<?= $debug_html ?>
</pre>

<? ENDIF # show_command_form ?><? ENDIF ###################### COMMON FOOTER ?>
</body></html>