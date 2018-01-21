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
    Discard:
    <div id="evidence_discard" style="border:solid 1px red"></div>
</div>

<div id="board">
    <div class="disc" style="top:230px; left:150px"></div>
    <div class="disc red" style="top:100px; left:500px"></div>
    <div class="disc blue" style="top:150px; left:500px"></div>
    <div class="disc green" style="top:200px; left:500px"></div>
    <div class="disc purple" style="top:340px; left:440px"></div>
    <div class="disc yellow" style="top:240px; left:223px"></div>
    <div class="disc yellow" style="top:230px; left:223px"></div>
    <div class="disc yellow" style="top:220px; left:223px"></div>

    <div class="cube" style="top:330px; left:150px"></div>
    <div class="cube red" style="top:200px; left:500px"></div>
    <div class="cube blue" style="top:250px; left:500px"></div>
    <div class="cube green" style="top:300px; left:500px"></div>
    <div class="cube purple" style="top:400px; left:166px"></div>
    <div class="cube yellow" style="top:300px; left:200px"></div>
    <div class="cube yellow" style="top:250px; left:251px"></div>
    <div class="cube yellow" style="top:240px; left:251px"></div>

    <div class="location_slot" style="top:282px; left:348px; transform:rotate(2.5deg);"></div>

    <div class="investigator" style="background: red; top:218px; left:160px"></div>
    <div class="investigator" style="background: gold; top:218px; left:220px"></div>
    <div class="investigator" style="background: lime; top:218px; left:280px"></div>
    <div class="investigator" style="background: magenta; top:218px; left:340px"></div>
    <div class="investigator" style="background: blue; top:218px; left:400px"></div>

    <div class="loc_tokens loc_forestpark" style="">
        <div class="investigator" style="background: gold;"></div>
        <div class="investigator" style="background: magenta;"></div>
        <div class="investigator" style="background: red;"></div>
        <div class="investigator" style="background: lime;"></div>
        <div class="investigator" style="background: blue;"></div>
    </div>

    <div class="loc_tokens loc_chinatown" style="">
        <div class="investigator" style="background: red;"></div>
        <div class="investigator" style="background: blue;"></div>
    </div>

    <div class="loc_tokens loc_trocadero" style="">
        <div class="investigator" style="background: red;"></div>
        <div class="investigator" style="background: blue;"></div>
    </div>

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
