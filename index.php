<?php /* index.php */ $VERSION = 'v0.1.10&beta;';
/******************************************************************************
* REMOTE CHESS - Copy(L)eft 2015                         http://harald.ist.org/
* MAIN SCRIPT and HTML TEMPLATE
******************************************************************************/

//... Log everything! No errors to the browser!
set_time_limit( 2 /*seconds*/ );   // Script run time, stops endless loops

include 'helpers.php';             // Debug and other small helper functions
include 'definitions.php';         // Constant values, signal names
include 'movement_rules.php';      // Find fields a piece can move to
include 'url_helpers.php';         //  update_parameters() , etc.
include 'generate_markup.php';     // Output to HTML
include 'game_logic.php';          // Main game control

main_control();   // see  game_logic.php


/******************************************************************************
* OUTPUT TO BROWSER - after  main_control()  returns, markup is built and sent
******************************************************************************/

//////////////////////////////////////////////////////////////// COMMON HEAD ?>
<!DOCTYPE html><html id="top" lang="en"><head><meta charset="utf-8">
<title><?= $game_title ?>Remote Chess - <?= $VERSION ?></title>
<meta name="author" content="Harald Markus Wirth, http://harald.ist.org/">
<meta name="description" content="Web service for playing chess via e-mail or instant messenger. No login required.">
<meta name="keywords" content="remote,mail,chess">
<meta name="robots" content="index,follow">
<link rel="stylesheet" type="text/css" href="default.css">
<link rel="alternate stylesheet" type="text/css" href="three_d.css" title="Perspective">
<link rel="alternate stylesheet" type="text/css" href="horizontal.css" title="Horizontal">
<link rel="shortcut icon" href="chess-icon.png">
<script type="text/javascript" src="chess_board.js"></script>
<script type="text/javascript" src="style_switcher.js"></script>
<? IF ($_SERVER['QUERY_STRING'] == ''): /////////////////////////// NEW GAME ?>
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

<? ELSE: /////////////////////////////////////////////////////// CHESS BOARD ?>
</head><body id="chess_board">

<header>
<h1>Remote Chess</h1>
</header>

<h2 class="nocss">Site Navigation</h2>
<nav><ul>
<li><button onclick="toggleStyle()">Switch Style</button>
<hr>
<li><a href="./">New Game</a>
<li><a href="<?= $href_flip ?>">Flip Board</a>
<li><a href="<?= $href_player ?>">Switch Sides</a>
<li><a href="./?base=">Empty Board</a>
<hr>
<li><a href="<?= update_href( TEST_LINK, '', '' ); ?>">Test:Temp</a>
<li><a href="<?= update_href( TEST_LINK_EP, '', '' ); ?>">Test:EnPassant</a>
<li><a href="<?= update_href( TEST_LINK_CA, '', '' ); ?>">Test:Castle</a>
<li><a href="<?= update_href( TEST_LINK_PR, '', '' ); ?>">Test:Promotion</a>
<hr>
<li><a href="<?= update_href( CHESS_RIDDLE, '', '' ); ?>">Riddle</a>
</ul></nav>

<? IF ($game_state_link != ''):  ?>
<h2>Send this link:</h2>
<p class="game_state_link">
	<?= substr($game_title, 0, -3) ?>:
	<br>
	<a href="<?= $game_state_link ?>"><?= $game_state_link ?></a>
</p>
<? ENDIF ?>

<h2><?= $heading ?></h2>
<?= $promotion_dialog_markup ?>
<?= $chess_board_markup ?>

<? IF ($show_command_form): ?>
<form action="./" method="get" accept-charset="utf-8">
<?  IF ($flip_board): ?>
<input type="hidden" name="<?= GET_FLIP_BOARD ?>" value="">
<?  ENDIF ?>
<input type="hidden" name="<?= GET_PLAYER ?>" value="<?= ($current_player == WHITES_MOVE) ? GET_WHITE : GET_BLACK ?>">
<input type="hidden" name="<?= GET_HISTORY ?>" value="<?= $history ?>">
<input type="hidden" name="<?= GET_WHITE ?>" value="<?= $name_white ?>">
<input type="hidden" name="<?= GET_BLACK ?>" value="<?= $name_black ?>">
<input type="hidden" name="<?= GET_BASE_BOARD ?>" value="<?= $board_encoded ?>">
<?  IF ($promotion_dialog_markup == ''): ?>
<p class="move">
	Move
	<label for="idFrom">from:</label>
	<input type="text" id="idFrom" name="from" value="<?= $preset_from_value ?>">
	<label for="idTo">to:</label>
	<input type="text" id="idTo" name="to" value="<?= $preset_to_value ?>">
	<label for="idSubmit" class="nocss">Submit:</label>
	<input type="submit" id="idSubmit" value="Submit">
</p>
<?  ENDIF ?>
<?  IF (false && ($id_focus != '')): ?>
<script type="text/javascript"> document.getElementById('<?= $id_focus ?>').focus(); </script>
<?  ENDIF ?>
</form>

<? ENDIF ?>
<?= $history_markup ?>

<? ENDIF ///////////////////////////////////////////////////// COMMON FOOTER ?>
<footer>
<h3><?= $VERSION ?></h3>
<? IF (DEBUG): ?>
<pre class="debug">
<?= $debug_html ?>
</pre>
<? ENDIF ?>
</footer>

</body></html>