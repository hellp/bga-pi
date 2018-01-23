{OVERALL_GAME_HEADER}
<!--
--------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- fabiantest implementation : © Fabian Neumann <fabian.neumann@posteo.de>
--
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-------
-->

<div id="evidence_wrap" class="whiteblock" style="float:left; width:950px"><div id="evidence"></div></div>
<div class="whiteblock" style="display:inline-block;background:none; opacity:0.75; width:110px;">
    <div id="evidence_discard"></div>
</div>

<div id="board" style="clear:both">
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

    <!-- BEGIN locslot -->
      <div id="locslot_{STRID}" class="locslot"
           style="top:{TOP}px; left:{LEFT}px; transform:rotate({ROTATION}deg);"></div>
    <!-- END locslot -->

    <!-- <div id="locslot_centralstation_suspect" class="locslot" style="top:50.8%; left:25.8%; transform:rotate(2.5deg);"></div>
    <div id="locslot_littleitaly_crime" class="locslot" style="top:30.4%; left:29.7%; transform:rotate(2.5deg);"></div>
    <div id="locslot_littleitaly_suspect" class="locslot" style="top:30.8%; left:15.8%; transform:rotate(2.5deg);"></div>
    <div id="locslot_mainstreet_crime" class="locslot" style="top:50.4%; left:64.7%; transform:rotate(0deg);"></div>
    <div id="locslot_mainstreet_suspect" class="locslot" style="top:50.8%; left:50.8%; transform:rotate(0deg);"></div> -->

    <div class="investigator" style="background: red; top:218px; left:160px"></div>
    <div class="investigator" style="background: #fe0; top:218px; left:220px"></div>
    <div class="investigator" style="background: lime; top:218px; left:280px"></div>
    <div class="investigator" style="background: magenta; top:218px; left:340px"></div>
    <div class="investigator" style="background: blue; top:218px; left:400px"></div>
    <div class="loc_tokens loc_forestpark" style="">
        <div class="investigator" style="background: #fe0;"></div>
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

<div id="myhand-wrap" class="whiteblock">
    <h4>{CASE_CARDS_OF_PLAYER} XXX</h4>
    <div id="myhand"></div>
</div>
<div id="myevidence-wrap" class="whiteblock">
    <h4>{MY_EVIDENCE_CARDS}</h4>
    <div id="myevidence"></div>
</div>

<div style="clear:both"></div>

<div id="playerdisplays">
    <!-- BEGIN player -->
    <div class="whiteblock">
        <h4>{EVIDENCE_CARDS_OF_PLAYER} <span style="color:#{PLAYER_COLOR}">{PLAYER_NAME}</span></h4>
        <div id="playerdisplay_{PLAYER_ID}"></div>
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
