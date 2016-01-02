<?php /* index.php */
$PROGRAM_NAME = 'Mail Chess';
$VERSION      = 'v0.2.11&beta;';
/******************************************************************************
* REMOTE CHESS - Copy(L)eft 2015                         http://harald.ist.org/
* MAIN SCRIPT and HTML TEMPLATE
******************************************************************************/

$start_time = microtime( true );   // Seconds since boot (float)

error_reporting( E_ALL | E_STRICT );   // Show everything, notices included
ini_set( 'display_errors', 'off'  );   // Don't show errors in the browser, ..
ini_set( 'display_startup_errors', 'off' );   // .. not even startup errors.
ini_set( 'log_errors', 'on' );         // Should be active by default
set_time_limit( 2 /*seconds*/ );       // Stops unintended endless loops

include 'helpers.php';             // Debug and other small helper functions
include 'definitions.php';         // Constant values, signal names
include 'movement_rules.php';      // Find fields a piece can move to
include 'url_helpers.php';         //  update_href() , etc.
include 'generate_markup.php';     // Output to HTML
include 'game_logic.php';          // Main game control

main_control();   // see  game_logic.php

$cpu_mhz = substr(trim(shell_exec('grep MHz /proc/cpuinfo | head -n 1')), 11);
$cpu_mhz = round($cpu_mhz * 100) / 100;
$elapsed_ms = round((microtime(true) - $start_time) * 100000) / 100;
$cycles = round(($elapsed_ms/100) * $cpu_mhz) / 10;
debug_out("<span class=\"time\">{$elapsed_ms}ms@{$cpu_mhz}MHz={$cycles}Mc</span>");


/******************************************************************************
* OUTPUT TO BROWSER - after  main_control()  returns, markup is built and sent
******************************************************************************/

////////////////////////////////////////////////////////////// COMMON HEADER ?>
<!DOCTYPE html><html id="top" lang="en"><head><meta charset="utf-8">
<title><?= $game_title ?><?= $PROGRAM_NAME ?> - <?= $VERSION ?></title>
<meta name="author" content="Harald Markus Wirth, http://harald.ist.org/">
<meta name="description" content="Web service for playing chess via e-mail or instant messenger. No login required.">
<meta name="keywords" content="remote,correspondence,mail,chess,fern,post,brief,schach">
<meta name="robots" content="index,follow">
<meta name=viewport content="width=device-width, width=560">
<link rel="stylesheet" type="text/css" href="default.css">
<? IF ($_SERVER['QUERY_STRING'] != ''): // NOT NEW GAME ?>
<link rel="alternate stylesheet" type="text/css" href="perspective.css" title="Perspective">
<link rel="alternate stylesheet" type="text/css" href="no_guides.css" title="No Guides">
<link rel="alternate stylesheet" type="text/css" href="fancy.css" title="Fancy">
<link rel="alternate stylesheet" type="text/css" href="ponies.css" title="Ponies">
<link rel="alternate stylesheet" type="text/css" href="large.css" title="Large">
<link rel="alternate stylesheet" type="text/css" href="small.css" title="Small">
<link rel="shortcut icon" href="chess-icon.png">
<script type="text/javascript" src="chess_board.js"></script>
<script type="text/javascript" src="style_switcher.js"></script>
<? ENDIF ?>
<? IF ($_SERVER['QUERY_STRING'] == ''): /////////////////////////// NEW GAME ?>
</head><body id="new_game">

<form action="./" method="get" accept-charset="utf-8">
<h1><?= $PROGRAM_NAME ?></h1>
<p>Enter names:</p>
<p class="names">
	<label for="idWhite" class="no_css"><?= ucfirst(GET_WHITE) ?>:</label>
	<input type="text" id="idWhite" name="white" value="<?= $name_white ?>">
	vs.
	<label for="idBlack" class="no_css"><?= ucfirst(GET_BLACK) ?>:</label>
	<input type="text" id="idBlack" name="black" value="<?= $name_black ?>">
</p><p>
	<label for="idSubmit" class="no_css">Submit:</label>
	<input type="submit" value="Start Game">
</p>
<input type="hidden" name="<?= GET_NEW_GAME ?>">
<script type="text/javascript"> document.getElementById('idWhite').select(); </script>
</form>

<? ELSE: /////////////////////////////////////////////////////// CHESS BOARD ?>
</head><body id="chess_board">

<header>
<h1><?= $PROGRAM_NAME ?></h1>
</header>

<? IF (!isset( $_GET[GET_GOTO] )): ?>
<section class="game_state_link">
<?  IF ($show_command_form): ?>
<h2>Command</h2>
<form action="./" method="get" accept-charset="utf-8">
<?   IF ($flip_board): ?>
<input type="hidden" name="<?= GET_FLIP_BOARD ?>" value="">
<?   ENDIF ?>
<input type="hidden" name="<?= GET_PLAYER ?>" value="<?= ($current_player == WHITES_MOVE) ? GET_WHITE : GET_BLACK ?>">
<input type="hidden" name="<?= GET_HISTORY ?>" value="<?= $history ?>">
<input type="hidden" name="<?= GET_WHITE ?>" value="<?= $name_white ?>">
<input type="hidden" name="<?= GET_BLACK ?>" value="<?= $name_black ?>">
<input type="hidden" name="<?= GET_BASE_BOARD ?>" value="<?= $board_encoded ?>">
<?   IF ($promotion_dialog_markup == ''): ?>
<p class="move">
	<label for="idFrom">From:</label>
	<input type="text" id="idFrom" name="from" value="<?= $preset_from_value ?>">
</p><p class="move">
	<label for="idTo">to:</label>
	<input type="text" id="idTo" name="to" value="<?= $preset_to_value ?>">
</p><p>
	<label for="idSubmit" class="no_css">Submit:</label>
	<input type="submit" id="idSubmit" value="Submit">
</p>
<?   ENDIF ?>
<?   IF ($id_focus != ''): ?>
<!-- script type="text/javascript"> document.getElementById('<?= $id_focus ?>').focus(); </script -->
<?   ENDIF ?>
</form>
<?  ENDIF ?>
<?  IF ($game_state_link != ''):  ?>
<h2>Return Link</h2><!-- h2>Send this link:</h2 -->
<p>
	Turn #<?= $turn_nr ?>:
	<br>
	<a href="<?= $game_state_link ?>" title="Copy/paste this link"><?= $game_state_link ?></a>
</p>
<?  ENDIF ?>
</section><!-- /game_state_link -->

<? ENDIF ?>
<section class="game_window">
<h2><?= $heading ?></h2>
<?= $promotion_dialog_markup ?>
<?= $chess_board_markup ?>
</section><!-- /game_window -->

<section class="history">
<?= $history_markup ?>
</section><!-- /history -->

<nav>
<h2 class="no_css">Site Navigation</h2>
<ul id="menu">
	<li><button onclick="toggleStyle()" accesskey="s" title="Firefox: Next Style: Alt+Shift+S">Menu</button>
	<hr>
	<li><a href="./">New Game</a>
	<li><a accesskey="f" href="<?= $href_flip ?>">Flip Board</a>
	<li><a accesskey="x" href="<?= $href_player ?>">Switch Sides</a>
	<li><a href="./?base=">Empty Board</a>
	<hr>
	<li><a accesskey="p" href="<?= $history_prev; ?>" title="Firefox: Alt+Shift+P">History: Back (&uarr;P)</a>
	<li><a accesskey="n" href="<?= $history_next; ?>" title="Firefox: Alt+Shift+N">History: Next (&uarr;N)</a>
	<hr>
<? foreach( $TEST_LINKS as $caption => $link ) { ?>
	<li><a href="<?= update_href( $link, '', '' ); ?>">Test: <?= $caption ?></a>
<? } ?>
	<hr>
	<li><a href="<?= update_href( CHESS_RIDDLE, '', '' ); ?>">Riddle</a>
</ul>
</nav>

<? ENDIF ///////////////////////////////////////////////////// COMMON FOOTER ?>
<footer>
<? IF ($_SERVER['QUERY_STRING'] != ''): // NOT NEW GAME ?>
<h2 class="no_css">Main Menu</h2>
<nav><ul>
	<li><a href="./" title="Enter names and start a new game">New Game</a>
	<li><a Xaccesskey="p" href="<?= $history_prev; ?>" title="History: Show previous move. Firefox: Alt+Shift+P">Back</a>
	<li><a Xaccesskey="n" href="<?= $history_next; ?>" title="History: Show next move. Firefox: Alt+Shift+N">Next</a>
	<li><a href="<?= update_href( CHESS_RIDDLE, '', '' ); ?>" title="Show a chess composition for beginners">Riddle</a>
	<li class="js_required"><a href="javascript:return false" onclick="toggleStyle()">Style</a>
</ul></nav>
<? ENDIF ?>
<h3><?= $PROGRAM_NAME ?> <?= $VERSION ?><br>Copy(l)eft 2015 by <a href="https://github.com/hwirth/remote_chess">hmw</a></h3>
<? IF (DEBUG): ?>
<pre class="debug">
<?= $debug_html ?>
</pre>
<? ENDIF ?>
</footer>

</body></html>