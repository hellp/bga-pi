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
    <div id="evidence_wrap" class="whiteblock" style="float:left; width:838px; padding:5px 8px;"><div id="evidence"></div></div>
    <div class="whiteblock" style="display:inline-block; background:none; margin-right:0; padding:5px 8px; opacity:0.75; width:90px;">
        <div id="evidence_discard"></div>
    </div>
</div>

<div id="boardwrap">
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

    <div id="vp_7" class="vpbox"></div>
    <div id="vp_5" class="vpbox"></div>
    <div id="vp_3" class="vpbox"></div>
    <div id="vp_1" class="vpbox"></div>
    <div id="vp_0" class="vpbox"></div>

    <div id="penalty_0" class="penaltybox"></div>
    <div id="penalty_2" class="penaltybox"></div>
    <div id="penalty_4" class="penaltybox"></div>
    <div id="penalty_6" class="penaltybox"></div>
    <div id="penalty_8" class="penaltybox"></div>
    <div id="penalty_10" class="penaltybox"></div>

    <div id="minigamecounter" class="whiteblock">
        <h5>{MINIGAME_X_OF_Y}</h5>
    </div>
</div>
</div>

<div id="belowboard">
    <div id="myhand-wrap" class="whiteblock">
        <h4>{CASE_CARDS_OF_PLAYER} <span style="color:#{LEFT_NEIGHBOR_COLOR}">{LEFT_NEIGHBOR_NAME}</span></h4>
        <div id="myhand"></div>
        <span id="casecards_help" class="myicon20 myicon20_help"></span>
    </div>
    <div id="myevidence-wrap" class="whiteblock">
        <h4>{MY_EVIDENCE_CARDS}</h4>
        <div id="myevidence"></div>
        <span id="myevidence_help" class="myicon20 myicon20_help"></span>
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
</div>

<script type="text/javascript">
// Javascript HTML templates

var jstpl_player_board = '' +
    '<div>' +
    '<div class="boarditem" style="width:33%">' +
    '  <div class="investigator investigator_${colorname}"></div>&times;&nbsp;<span id="remaining_investigators_${id}"></span>' +
    '</div><div class="boarditem" style="width:25%">' +
    '  <div class="cube_supply" id="cubes_${id}"></div>' +
    '</div><div class="boarditem" style="width:20%">' +
    '  <div class="disc_supply" id="discs_${id}"></div>' +
    '</div><div class="boarditem" style="width:5%">' +
    '<div class="sp_marker" id="sp_marker_${id}"></div>' +
    '</div>' +
    '</div>';

var jstpl_token_cube = '<div id="${key}" class="token cube20 cube20_${color}"></div>';
var jstpl_token_disc = '<div id="${key}" class="token disc30 disc30_${color}"></div>';
var jstpl_token_penalty = '<div id="${key}" class="token penalty penalty_${color}"></div>';
var jstpl_token_pi = '<div id="${key}" class="token investigator investigator_${color}"></div>';
var jstpl_token_vp = '<div id="${key}" class="token vpmarker"></div>';
var jstpl_card_tooltip = '<div class="cardtooltip">${name}</div>';
var jstpl_casecard_tooltip = '<div class="cardtooltip casecardtooltip">${name}</div>';
var jstpl_displaycard_tooltip = '<div class="cardtooltip">${name}</div><hr><span class="help_click">${action}</span>';

</script>

{OVERALL_GAME_FOOTER}
