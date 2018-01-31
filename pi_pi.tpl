{OVERALL_GAME_HEADER}
<!--
--------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- P.I. implementation: © Fabian Neumann <fabian.neumann@posteo.de>
--
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-------
-->

<div id="carddisplay">
    <div id="evidence_wrap" class="whiteblock" style="float:left; width:950px"><div id="evidence"></div></div>
    <div class="whiteblock" style="display:inline-block;background:none; opacity:0.75; width:110px;">
        <div id="evidence_discard"></div>
    </div>
</div>

<div id="board" style="clear:both">
    <div id="tilestock"></div>
    <!-- BEGIN loccont -->
    <div id="loccont_{ID}" class="loccont"
         style="top:{TOP}px; left:{LEFT}px; transform:rotate({ROTATION}deg);">
        <div id="agentarea_{ID}" class="agentarea"></div>
        <!-- BEGIN locslot -->
        <div id="locslot_{ID}" class="locslot {TYPE}_slot">
          <div id="locslot_{ID}_cubes" class="locslot_cubes"></div>
        </div>
        <!-- END locslot -->
    </div>
    <!-- END loccont -->

</div>

<div id="myhand-wrap" class="whiteblock">
    <h4>{CASE_CARDS_OF_PLAYER} <span style="color:#{LEFT_NEIGHBOR_COLOR}">{LEFT_NEIGHBOR_NAME}</span></h4>
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

var jstpl_player_board = '<div class="cp_board"><span class="sp_marker" id="sp_marker_${id}">➊</span></div>';

</script>

{OVERALL_GAME_FOOTER}
