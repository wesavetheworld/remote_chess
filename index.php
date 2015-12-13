<?php /* index.php */ $VERSION = 'v0.1.6&alpha;';
/******************************************************************************
* REMOTE CHESS - MAIN SCRIPT and HTML TEMPLATE
*******************************************************************************
* Copyleft Tue, Dec 8, 2015 by http://harald.ist.org/
*******************************************************************************
* Every state of a game is represented by a URL, which can be bookmarked.
* Players enter their move and send the resulting link to their chess partners,
* in order for them to continue the game likewise.
*******************************************************************************
* TODO
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
* ? Nice URL: from == to? --> HTTP Redirect!
*******************************************************************************
* OPTIONAL
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

set_time_limit( 2 );   // Maximum script run time, stops endless loops

include 'definitions.php';       // Constant values, signal names
include 'game_logic.php';        // Main game control
include 'movement_rules.php';    // Find fields a piece can move to
include 'url_helpers.php';       // update_parameters() , etc.
include 'generate_markup.php';   // Output to HTML

main_control();   // see  game_logic.php


/******************************************************************************
* OUTPUT TO BROWSER - after  main_control()  returns, markup is built and sent
******************************************************************************/

################################################################ COMMON HEAD ?>
<!DOCTYPE html><html id="top" lang="en"><head><meta charset="utf-8">
<title>Remote Chess - <?= $VERSION ?></title>
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
<script type="text/javascript"> document.getElementById('idWhite').select(); </script>
</form>

<? ELSE: ####################################################### CHESS BOARD ?>
</head><body id="chess_board">

<header>
<h1>Remote Chess</h1>
</header>

<nav><ul>
<li><button onclick="toggleStyle()">Switch Style</button>
<li><a href="<?= $href_flip ?>">Flip Board</a>
<li><a href="<?= $href_player ?>">Switch Sides</a>
<li><a href="./">New Game</a>
<li><a href="<?= $href_test ?>">Test</a>
</ul></nav>

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

<? IF (DEBUG): ?>
<pre class="debug">
<?= $debug_html ?>
</pre>

<? ENDIF # show_command_form ?><? ENDIF ###################### COMMON FOOTER ?>
</body></html>