{OVERALL_GAME_HEADER}
<!--
--------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- fabiantest implementation : © Fabian Neumann <Your email address here>
--
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-------
-->

<div id="evidence_wrap" class="whiteblock">
    <div id="evidence"></div>
</div>

<div id="board">
    <div class="disc" style="top:230px; left:150px"></div>
    <div class="disc red" style="top:100px; left:500px"></div>
    <div class="disc blue" style="top:150px; left:500px"></div>
    <div class="disc green" style="top:200px; left:500px"></div>
    <div class="disc purple" style="top:340px; left:440px"></div>
    <div class="disc yellow" style="top:200px; left:200px"></div>

    <div class="cube" style="top:330px; left:150px"></div>
    <div class="cube red" style="top:200px; left:500px"></div>
    <div class="cube blue" style="top:250px; left:500px"></div>
    <div class="cube green" style="top:300px; left:500px"></div>
    <div class="cube purple" style="top:400px; left:166px"></div>
    <div class="cube yellow" style="top:300px; left:200px"></div>

    <div class="investigator-placeholder yellow" style="top:300px; left:500px"></div>

</div>

<h3 style="margin-top:1em">{MY_HAND} ({CASE_CARDS_OF_PLAYER} XXX)</h3>
<div class="playertable whiteblock">
    <div id="myhand"></div>
</div>
<h3>{MY_EVIDENCE_CARDS}</h3>
<div class="playertable whiteblock">
    <div id="myevidence"></div>
</div>

<h3>{NO_EVIDENCE_CARDS}:</h3>
<div id="playertables">
    <!-- BEGIN player -->
    <div class="playertable whiteblock">
        <h4 style="color:#{PLAYER_COLOR}">{PLAYER_NAME}</h4>
        <div class="playertablecard" id="playertablecard_{PLAYER_ID}">
        </div>
    </div>
    <!-- END player -->
</div>

<script type="text/javascript">

// Javascript HTML templates

/*
// Example:
var jstpl_some_game_item='<div class="my_game_item" id="my_game_item_${id}"></div>';

*/

</script>

{OVERALL_GAME_FOOTER}
