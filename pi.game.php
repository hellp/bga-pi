<?php
 /**
  *------
  * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
  * P.I. implementation: © Fabian Neumann <fabian.neumann@posteo.de>
  *
  * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
  * See http://en.boardgamearena.com/#!doc/Studio for more information.
  * -----
  *
  * pi.game.php
  *
  * This is the main file for your game logic.
  *
  * In this PHP file, you are going to defines the rules of the game.
  *
  */


require_once( APP_GAMEMODULE_PATH.'module/table/table.game.php' );
require_once( 'modules/tokens.php' );
require_once( 'modules/utils.php' );


class pi extends Table
{
    function __construct( )
    {
        // Your global variables labels:
        //  Here, you can assign labels to global variables you are using for this game.
        //  You can use any number of global variables with IDs between 10 and 99.
        //  If your game has options (variants), you also have to associate here a label to
        //  the corresponding ID in gameoptions.inc.php.
        // Note: afterwards, you can get/set the global variables with getGameStateValue/setGameStateInitialValue/setGameStateValue
        parent::__construct();

        self::initGameStateLabels(array(
            // P.I. is played over 3 "mini games". Here we store in which of
            // these games we are. Valid values: 1, 2, 3
            "minigame" => 10,

            // The inner-minigame round. Required to determine how many points
            // players score when solving. All players that solve in the same
            // round score the same, then the score drops. Also a minigame ends
            // when a new round begins and only 1 player is left with an
            // unsolved case. That player then scores 0 for that minigame and a
            // new one starts (or game end).
            "minigame_round" => 11,

            // The current value of points a player would get if they would
            // score correctly now. Decreases after rounds in that a player (or
            // multiple) successfully scored.
            "points_winnable" => 12,

            // "my_first_game_variant" => 100,
            // "my_second_game_variant" => 101,
        ));

        $this->cards = self::getNew("module.common.deck");
        $this->cards->init("card");
        $this->tokens = new Tokens();
    }

    protected function getGameName( )
    {
        // Used for translations and stuff. Please do not modify.
        return "pi";
    }

    /*
        setupNewGame:

        This method is called only once, when a new game is launched.
        In this method, you must setup the game according to the game rules, so that
        the game is ready to be played.
    */
    protected function setupNewGame($players, $options=array())
    {
        // Set the colors of the players with HTML color code
        // The default below is red/green/blue/orange/brown
        // The number of colors defined here must correspond to the maximum number of players allowed for the gams
        $gameinfos = self::getGameinfos();
        $default_colors = $gameinfos['player_colors'];

        // Create players
        // Note: if you added some extra field on "player" table in the database (dbmodel.sql), you can initialize it there.
        $sql = "INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar) VALUES ";
        $values = array();
        foreach($players as $player_id => $player)
        {
            $color = array_shift($default_colors);
            $values[] = "('".$player_id."','$color','".$player['player_canal']."','".addslashes( $player['player_name'] )."','".addslashes( $player['player_avatar'] )."')";
        }
        $sql .= implode( $values, ',' );
        self::DbQuery($sql);
        self::reattributeColorsBasedOnPreferences( $players, $gameinfos['player_colors'] );
        self::reloadPlayersBasicInfos();

        /************ Start the game initialization *****/

        // Set initial scores. `player_score_aux` is (investigators_remaining *
        // 100 - penalty_points).
        self::DbQuery("UPDATE player SET player_score = 0, player_score_aux = 500");

        // First create all the cards we have.
        // - 36 Evidence cards
        // - 12 Suspects
        // - 10 Crimes
        // - 14 Locations

        $cards = array();
        // Create Evidence + Case cards
        foreach ($this->cardBasis as $card_id => $card) {
            $cards[] = array(
                'type' => ($card_id <= 36) ? 'evidence' : $card['casetype'],
                'type_arg' => $card_id,
                'nbr' => 1);
        }
        // Create tiles -- not actual cards, but handled similarly
        foreach ($this->tiles as $tile_id => $tile) {
            $cards[] = array(
                'type' => 'tile_' . $tile['tiletype'],
                'type_arg' => $tile_id,
                'nbr' => 1);
        }

        // Create all, but don't put them into 'deck' yet, the piles have to be
        // sorted first.
        $this->cards->createCards($cards, 'offtable');

        $players = self::loadPlayersBasicInfos();
        foreach($players as $player_id => $player) {
            $color = $this->constants['HEX2COLORNAME'][$player['player_color']];

            // Create player tokens, and put into 'supply'. Not yet into their
            // personal supply. This will be done in st_setupMinigame. Note: we
            // do not do a createTokens($this->tokeninfos) as this would create
            // many tokens in the DB that we don't need for games with less
            // than the full player count.
            $player_tokens = array_filter($this->tokeninfos, function ($v) use ($color) {
                return strpos($v['key'], "_{$color}") > 0;
            });
            $this->tokens->createTokens($player_tokens, 'supply');

            // Give each player their 5 investigators. These will *not* be
            // replenished on each minigame.
            $this->tokens->moveTokens(
                array_pluck($this->tokens->getTokensOfTypeInLocation("pi_{$color}_%"), 'key'),
                "pi_supply_{$player_id}");
            // Put penalty token on the "0"
            $this->tokens->moveToken("penalty_{$color}", "penalty_0");
        }

        // Init game statistics
        self::initStat('table', 'turns_number', 0);
        self::initStat('table', 'rounds_1', 0);
        self::initStat('table', 'rounds_2', 0);
        self::initStat('table', 'rounds_3', 0);
        self::initStat('player', 'turns_number', 0);
        self::initStat('player', 'cards_taken_1', 0);
        self::initStat('player', 'cards_taken_2', 0);
        self::initStat('player', 'cards_taken_3', 0);
        self::initStat('player', 'investigators_used_1', 0);
        self::initStat('player', 'investigators_used_2', 0);
        self::initStat('player', 'investigators_used_3', 0);
        self::initStat('player', 'penalty_1', 0);
        self::initStat('player', 'penalty_2', 0);
        self::initStat('player', 'penalty_3', 0);
        self::initStat('player', 'vp_1', 0);
        self::initStat('player', 'vp_2', 0);
        self::initStat('player', 'vp_3', 0);
        self::initStat('player', 'solved_minigames', 0);
        self::initStat('player', 'avg_investigator_neighborhood', $this->constants['AVG_LOCATION_NEIGHBORS']);
        self::initStat('player', 'neighbor_case_cards_taken', 0);
        self::initStat('player', 'avg_cubes_to_solve', $this->constants['CUBES_PER_PLAYER']);
        self::initStat('player', 'avg_discs_to_solve', $this->constants['DISCS_PER_PLAYER']);

        // Setup the initial game situation here
        self::setGameStateInitialValue('minigame', 0);  // will be increased in st_setupMinigame
        self::setGameStateInitialValue('minigame_round', 0);  // will be really set in st_setupMinigame
        self::setGameStateInitialValue('points_winnable', 7);  // will be really set in st_setupMinigame

        /************ End of the game initialization *****/
    }

    /*
        getAllDatas:

        Gather all informations about current game situation (visible by the current player).

        The method is called each time the game interface is displayed to a player, ie:
        _ when the game starts
        _ when a player refreshes the game page (F5)
    */
    protected function getAllDatas()
    {
        $result = array();

        // Global / static information
        $result['cardinfos'] = $this->cardBasis;
        $result['locationinfos'] = $this->locations;
        $result['tileinfos'] = $this->tiles;

        $result = array_merge($result, $this->getPrivateGameInfos(self::getCurrentPlayerId()));
        $result = array_merge($result, $this->getPublicGameInfos());
        return $result;
    }

    /*
        getGameProgression:

        Compute and return the current game progression.
        The number returned must be an integer beween 0 (=the game just started) and
        100 (= the game is finished or almost finished).

        This method is called each time we are in a game state with the "updateGameProgression" property set to true
        (see states.inc.php)
    */
    function getGameProgression()
    {
        // With the mini game number we divide the game in 3 thirds (0-33,
        // 33-66, 66-100%), and looking at the player discs we can further
        // divide each third: each disc on an agentarea counts as a 1/9th
        // solved case; each disc on a locslot as a 1/3rd solve case. We
        // average that over the player count, and thus get the in-minigame
        // progress.
        $base = (self::getGameStateValue("minigame") - 1) * (100 / $this->constants['MINIGAMES']);
        $base = max(0, $base);
        $discs_on_agentarea = count($this->tokens->getTokensOfTypeInLocation('disc_%', 'agentarea_%'));
        $discs_on_locslot = count($this->tokens->getTokensOfTypeInLocation('disc_%', 'locslot_%'));
        $perc_cases_solved = 0;
        $perc_cases_solved += $discs_on_agentarea * (1/9);
        $perc_cases_solved += $discs_on_locslot * (1/3);
        $minigame_progress = $perc_cases_solved / self::getPlayersNumber();
        $progress = $base + ($minigame_progress * 33);
        return floor($progress);
    }


//////////////////////////////////////////////////////////////////////////////
//////////// Utility functions
////////////
    /*
        In this space, you can put any utility methods useful for your game logic
    */

    function getCorrespondingTile($card_id)
    {
        $card = $this->cards->getCard($card_id);
        $cardinfo = $this->cardBasis[$card['type_arg']];
        $tile_mid = null;
        foreach ($this->tiles as $mid => $tile) {
            if ($tile['name'] == $cardinfo['name']) {
                $tile_mid = $mid;
                break;
            }
        }
        // only one to be expected, but PHP needs this in-between step...
        $tiles = $this->cards->getCardsOfType("tile_{$cardinfo['casetype']}", $tile_mid);
        return array_shift($tiles);
    }

    function getLocationIdOfTile($tile)
    {
        // Assert that the tile is actually on the board.
        if ($tile['location'] != 'locslot') {
            throw new BgaVisibleSystemException("Tile is not on the board. Please report this.");
        }
        $tile_slot_id = $tile['location_arg'];
        return floor((int)$tile_slot_id / 100); // the invers of ($loc_id * 100 + 1|2|3)
    }

    /**
     * For the given location_id, return all adjacent tiles' names (includes
     * all case aspects).
     */
    function getAdjacentTileNames($location_id, $tile_type=null)
    {
        $neighbor_mids = $this->locations[$location_id]['neighbors'];
        $adjacent_slot_ids = array_pluck(
            array_flatten(array_pluck(
                array_filter_by_keys($this->locations, $neighbor_mids),
                'slots')),
            'id');
        $sql = array();
        $sql[] = "SELECT card_type_arg FROM `card` WHERE 1";
        $sql[] = "AND card_location_arg IN (" . implode(',', $adjacent_slot_ids) . ")";
        if ($tile_type) {
            $sql[] = "AND card_type = '{$tile_type}'";
        }
        $sql = implode(' ', $sql);
        $tile_mids = self::getObjectListFromDB($sql, true);
        return array_pluck(array_filter_by_keys($this->tiles, $tile_mids), 'name');
    }

    /**
     * For the given card_id, find the tile that it corresponds to and return
     * all adjacent tiles' names (only considering tiles with the same casetype,
     * e.g. only suspects).
     */
    function getAdjacentTileNamesFromCard($card_id)
    {
        // Get the tile that corresponds to the card.
        // $card = $this->cards->getCard($card_id);
        $tile = $this->getCorrespondingTile($card_id);
        $location_mid = $this->getLocationIdOfTile($tile);
        return $this->getAdjacentTileNames($location_mid, $tile['type']);
    }

    /**
     * Return an array of the player's current case' solution in the form:
     *
     * array(
     *     "crime": "<Name>",
     *     "location": "<Name>",
     *     "suspect": "<Name>"
     * )
     */
    function getPlayerCaseSolution($player_id)
    {
        $card_mids = array_pluck($this->getPlayerCaseCards($player_id), 'type_arg');
        $solution = array(
            $this->cardBasis[$card_mids[0]]['casetype'] => $this->cardBasis[$card_mids[0]]['name'],
            $this->cardBasis[$card_mids[1]]['casetype'] => $this->cardBasis[$card_mids[1]]['name'],
            $this->cardBasis[$card_mids[2]]['casetype'] => $this->cardBasis[$card_mids[2]]['name']
        );
        return $solution;
    }

    function getPrivateGameInfos($player_id)
    {
        return array(
            // Cards in player hand (the other player's case cards)
            'hand' => $this->cards->getCardsInLocation('hand', $player_id)
        );
    }

    function getPublicGameInfos()
    {
        $minigame = self::getGameStateValue("minigame");

        $counters = array();
        $this->setCounter($counters, "current_minigame", $minigame);

        // Get information about players
        $start_player_no = $this->getStartPlayerNo($minigame);
        $sql = "
            SELECT
                player_id as id,
                player_color as color,
                player_score as score,
                player_solved_in_round as solved_in_round,
                player_no = {$start_player_no} as is_startplayer
            FROM player
        ";
        $players = self::getCollectionFromDb($sql);
        foreach ($players as $idx => $player) {
            $players[$idx]['colorname'] = $this->constants['HEX2COLORNAME'][$player['color']];
            $this->setCounter(
                $counters, "remaining_investigators_" . $player['id'],
                $this->tokens->countTokensInLocation("pi_supply_" . $player['id']));
        }

        return array(
            'counters' => $counters,
            'players' => $players,

            // Evidence cards on display
            'evidence_display' => $this->cards->getCardsInLocation('evidence_display'),
            'evidence_discard' => $this->cards->getCardsInLocation('discard'),
            'player_display_cards' => $this->cards->getCardsInLocation('player_display'),
            'tiles' => $this->cards->getCardsInLocation('locslot'),
            'tokens' => array_merge(
                array_values($this->tokens->getTokensInLocation('box')),
                array_values($this->tokens->getTokensInLocation('offtable')),
                array_values($this->tokens->getTokensInLocation('agentarea_%')),
                array_values($this->tokens->getTokensInLocation('cubes_%')), // player supplies
                array_values($this->tokens->getTokensInLocation('discs_%')), // player supplies
                array_values($this->tokens->getTokensInLocation('locslot_%')),
                array_values($this->tokens->getTokensInLocation('penalty_%')),
                array_values($this->tokens->getTokensInLocation('vp_%'))
            )
        );
    }

    /**
     * Return the cards cards that represent the solution for the given player.
     * These are the player's right neighbors hand cards.
     */
    function getPlayerCaseCards($player_id)
    {
        return $this->cards->getPlayerHand(self::getPlayerBefore($player_id));
    }

    /** Return the `player_id` of the player that is start player in the given
     * mini-game. */
    function getStartPlayerId($minigame)
    {
        $start_player_no = $this->getStartPlayerNo($minigame);
        $sql = "SELECT player_id FROM player WHERE player_no = $start_player_no";
        return self::getUniqueValueFromDB($sql);
    }

    /** Return the `player_no` of the player that is start player in the given
     * mini-game. */
    function getStartPlayerNo($minigame)
    {
        return (($minigame - 1) % self::getPlayersNumber()) + 1;
    }

    /**
     * Send a blank 'animate' notification to all players. Used to delay UI.
     */
    function notifyAnimate()
    {
        self::notifyAllPlayers("animate", "", array());
    }

    /**
     * Send a notification to all player with the current scores.
     */
    function notifyNewScores()
    {
        $scores = self::getCollectionFromDb("SELECT player_id, player_score FROM player", true);
        self::notifyAllPlayers("newScores", "", array("scores" => $scores));
    }

    /**
     * Put a new evidence cards on display. Also takes care about reshuffling
     * the deck if we run out.
     */
    function replenishEvidenceDisplay()
    {
        // First check, if we have to do so. If the display is full, do nothing.
        if ($this->cards->countCardInLocation('evidence_display')
            == $this->constants['EVIDENCE_DISPLAY_SIZE']) {
            return;
        }

        // While auto-reshuffle is still a mystery to me, check here manually.
        if ($this->cards->countCardInLocation('deck') == 0
                && $this->cards->countCardInLocation('discard') > 0) {
            $this->cards->moveAllCardsInLocation('discard', 'deck');
            $this->cards->shuffle('deck');
        }

        if ($this->cards->countCardInLocation('deck') > 0) {
            $newCard = $this->cards->pickCardForLocation("deck", "evidence_display");
            self::notifyAllPlayers(
                'evidenceReplenished', '',
                array(
                    'card_id' => $newCard['id'],
                    'card_type' => $newCard['type_arg'],
                    'discard_is_empty' => $this->cards->countCardInLocation('discard') == 0,
                ));
        } else {
            // TODO FIXME: show this only once; the first time, the deck runs
            // out. after that it's obvious to the players.

            // Rare case, but it could happen. Players are now forced to do
            // something else. But this is implicit from the UI: no more cards,
            // no more clicks on them. Solving is always the last ressort.
            self::notifyAllPlayers(
                'evidenceExhausted',
                clienttranslate('The evidence deck and discard pile are empty.'),
                array());
        }
    }

    protected function setCounter(&$array, $key, $value) {
        $array[$key] = array('counter_name' => $key, 'counter_value' => $value);
    }

//////////////////////////////////////////////////////////////////////////////
//////////// Player actions
////////////

    /*
        Each time a player is doing some game action, one of the methods below is called.
        (note: each method below must match an input method in pi.action.php)
    */

    function placeInvestigator($location_id)
    {
        self::checkAction("placeInvestigator");
        $player_id = self::getActivePlayerId();
        $player = self::loadPlayersBasicInfos()[$player_id];
        $color = $this->constants['HEX2COLORNAME'][$player['player_color']];
        $agent_area = "agentarea_{$location_id}";
        $pis_in_supply = $this->tokens->countTokensInLocation("pi_supply_{$player_id}");

        // No more investigator. Should be handled in the UI, but safety first.
        if ($pis_in_supply == 0) {
            throw new BgaUserException(self::_("You have no investigators left."));
        }

        // Check if player already has an investigator at this location.
        if (count($this->tokens->getTokensOfTypeInLocation("pi_{$color}_%", $agent_area))) {
            throw new BgaUserException(self::_("You already have an investigator at this location."));
        }

        // Increase the 'investigators used' stats.
        self::incStat(1, "investigators_used_" . self::getGameStateValue('minigame'), $player_id);

        // Calculate the 'average investigator location neighborhood' stat.
        $number_of_loc_neighbors = count($this->locations[$location_id]['neighbors']);
        $total_pis_used = $this->constants["PIS_PER_PLAYER"] - $pis_in_supply;
        $current_val = self::getStat('avg_investigator_neighborhood', $player_id);
        // Calculate the "rolling average".
        self::setStat(
            ($current_val * $total_pis_used + $number_of_loc_neighbors) / ($total_pis_used + 1),
            'avg_investigator_neighborhood',
            $player_id
        );

        // Place investigator token here.
        $_temp = $this->tokens->pickTokensForLocation(1, "pi_supply_{$player_id}", $agent_area);
        $pi_token = array_shift($_temp);
        $counters = array();
        $this->setCounter(
            $counters, "remaining_investigators_$player_id",
            $this->tokens->countTokensInLocation("pi_supply_$player_id"));
        // Adjust the tiebreaker score.
        self::DbQuery("UPDATE player SET player_score_aux = player_score_aux - 100 WHERE player_id = $player_id");
        self::notifyAllPlayers(
            'placeToken',
            clienttranslate('${player_name} sends an investigator to ${location_name}.'),
            array(
                'i18n' => array('location_name'),
                'counters' => $counters,
                'token' => $pi_token,
                'target_id' => $agent_area,
                'player_name' => $player['player_name'],
                'location_name' => $this->locations[$location_id]['name']
            ));

        $solution = $this->getPlayerCaseSolution($player_id);

        $locslots = $this->locations[$location_id]['slots'];
        $slot_ids = array_pluck($locslots, 'id');

        // IMPORTANT! Even if we match, we must not notify/place right away, as
        // we must not give away which of the aspects the new token(s) refer
        // to. So we collect the new tokens first. Then shuffle this list and
        // notify/place all at the same time. Also we go through the slots in a
        // random order, so we don't acidentally pick up tokens (they are
        // id'ed!) in a revealing order.
        $new_tokens = array();
        $locslots_copy = array_values($locslots);
        shuffle($locslots_copy);
        foreach ($locslots_copy as $slot) {
            $slot_id = $slot['id'];
            $locslot_location = "locslot_{$slot_id}";

            // First, check if there's a disc/cube of player color on it
            // already. If so, we have as much information for this aspect as
            // we can get: continue.
            if (count($this->tokens->getTokensOfTypeInLocation("cube_{$color}_%", $locslot_location)) ||
                count($this->tokens->getTokensOfTypeInLocation("disc_{$color}_%", $locslot_location))) {
                continue;
            }

            // Check for a match with the solution.
            $_tiles = $this->cards->getCardsInLocation('locslot', $slot_id); // only 1, but php...
            $tile = array_shift($_tiles);
            $tile_mid = $tile['type_arg'];
            $mtile = $this->tiles[$tile_mid]; // material tile
            
            // Full match: put disc into the agent area
            $full_match = $mtile['name'] == $solution[$mtile['tiletype']];
            if ($full_match) {
                $disc = $this->tokens->getTokenOnTop("discs_{$player_id}");
                $this->tokens->moveToken($disc['key'], $agent_area);
                $new_tokens[] = $disc;
                // Done with this location slot
                continue;
            }
            
            // Not a full match; check adjacent locations now.
            $adjacent_tile_names = $this->getAdjacentTileNames($location_id, "tile_{$mtile['tiletype']}");
            $close_match = false;
            foreach ($solution as $name) {
                if (in_array($name, $adjacent_tile_names)) {
                    $close_match = true;
                    break;
                }
            }

            // Close match: put cube into the agent area
            if ($close_match) {
                $cube = $this->tokens->getTokenOnTop("cubes_{$player_id}");
                if (!$cube) {
                    // TODO: warning/error?? Player run out of cubes.
                }
                $this->tokens->moveToken($cube['key'], $agent_area);
                $new_tokens[] = $cube;
            }
        }

        // TODO: in case of 3 full matches, we could place the tokens on the
        // tiles directly. More of a convenience feature, however. Same in the
        // case of N cubes OR discs when N is the number of
        // non-NO-CRIME/NO-SUSPECT tiles on the location. If it's mixed (cubes
        // + disc), then it's not possible to tell!

        if (count($new_tokens)) {
            shuffle($new_tokens);
            self::notifyAllPlayers(
                'placeTokens', '',
                array(
                    '_comment' => 'The tiles were checked in a random order; tokens are shuffled! No guessing!',
                    'tokens' => $new_tokens,
                    'target_id' => $agent_area,
                )
            );
        }
        $this->notifyAnimate();

        $this->gamestate->nextState('nextTurn');
    }

    function selectEvidence($card_id)
    {
        // Some checks first
        self::checkAction("selectEvidence");
        $currentCard = $this->cards->getCard($card_id);
        // Should not happen; also anti-cheat
        if ($currentCard['location'] != "evidence_display") {
            throw new BgaUserException(self::_("Card is not on display. Press F5 in case of problems."));
        }

        // Player infos
        $player_id = self::getActivePlayerId();
        $player = self::loadPlayersBasicInfos()[$player_id];
        $color = $this->constants['HEX2COLORNAME'][$player['player_color']];

        // Find corresponding tile and locslot
        $tile = $this->getCorrespondingTile($currentCard['id']);
        $target_id = "locslot_{$tile['location_arg']}";

        // Abort if there is already a token of that player on the tile.
        if (count($this->tokens->getTokensOfTypeInLocation("%_{$color}_%", $target_id))) {
            throw new BgaUserException(self::_("You already have a cube or disc there."));
        }

        // Various other infos we need
        $card_name = $this->cardBasis[$currentCard['type_arg']]['name'];
        $location_id = $this->getLocationIdOfTile($tile);
        $agent_area = "agentarea_{$location_id}";

        // Increase the 'cards taken' stats.
        self::incStat(1, "cards_taken_" . self::getGameStateValue('minigame'), $player_id);
        // If card takes was one of the aspects from the player's hand,
        // increase that stat too.
        $hand_cards = $this->cards->getPlayerHand($player_id);
        if (in_array($currentCard['type_arg'] + 36, array_pluck($hand_cards, 'type_arg'))) {
            self::incStat(1, "neighbor_case_cards_taken", $player_id);
        }

        // The solution
        $solution = $this->getPlayerCaseSolution($player_id);

        // Check for a full match with the player's case.
        if (in_array($card_name, $solution)) {
            // Get a disc to put on the tile.

            // First we check if there is already a dics at that *location*,
            // i.e. on an investigator tile. If so, we take it from there. Else
            // from our supply.
            $discs = $this->tokens->getTokensOfTypeInLocation("disc_{$color}_%", $agent_area);
            if (count($discs)) {
                $disc = array_shift($discs);
            } else {
                $disc = $this->tokens->getTokenOnTop("discs_{$player_id}");
            }

            // Move token
            $this->tokens->moveToken($disc['key'], $target_id);
            self::notifyAllPlayers(
                'placeToken', '',
                array(
                    'token' => $disc,
                    'target_id' => $target_id,
                ));

            // Put card on discard
            $this->cards->insertCardOnExtremePosition($card_id, "discard", true);
            self::notifyAllPlayers(
                'evidenceCorrect',
                clienttranslate('${player_name} found one aspect of their case: ${card_name}!'),
                array(
                    'i18n' => array('card_name'),
                    'card_id' => $card_id,
                    'card_name' => $card_name,
                    'card_type' => $currentCard['type_arg'],
                    'player_id' => $player_id,
                    'player_name' => self::getActivePlayerName()
                ));

            $this->gamestate->nextState('nextTurn');
            return;
        }

        // Next, check if we have a match in adjacent locations.
        $adjacent_tile_names = $this->getAdjacentTileNamesFromCard($card_id);
        $match_name = null;
        foreach ($solution as $casetype => $name) {
            if (in_array($name, $adjacent_tile_names)) {
                $match_name = $name;
                $match_casetype = $casetype;
                break;
            }
        }
        if ($match_name) {
            // Adjacent match

            // First check if there is already a cube at that *location*, i.e.
            // on an investigator tile. If so, we take it from there. Else from
            // our supply.
            $cubes = $this->tokens->getTokensOfTypeInLocation("cube_{$color}_%", $agent_area);
            if (count($cubes)) {
                $cube = array_shift($cubes);
            } else {
                $cube = $this->tokens->getTokenOnTop("cubes_{$player_id}");
            }
            if (!$cube) {
                throw new BgaUserException(self::_("No more cubes in your supply!"));
            }

            // Put card on discard
            $this->cards->insertCardOnExtremePosition($card_id, "discard", true);

            // Move token
            $this->tokens->moveToken($cube['key'], $target_id);
            self::notifyAllPlayers(
                'placeToken', '',
                array(
                    'token' => $cube,
                    'target_id' => $target_id,
                )
            );

            self::notifyAllPlayers(
                'evidenceClose',
                clienttranslate('${player_name} found out that ${card_name} is adjacent to the actual ${casetype}.'),
                array(
                    'i18n' => array('card_name'),
                    'card_id' => $card_id,
                    'card_name' => $card_name,
                    'card_type' => $currentCard['type_arg'],
                    'casetype' => $this->constants['CASETYPES'][$match_casetype],
                    'player_id' => $player_id,
                    'player_name' => self::getActivePlayerName(),
                )
            );

        } else {
            // No match at all
            self::notifyAllPlayers(
                'evidenceWrong',
                clienttranslate('${player_name} found out that ${card_name} is unrelated to their case.'),
                array(
                    'i18n' => array('card_name'),
                    'card_id' => $card_id,
                    'card_name' => $card_name,
                    'card_type' => $currentCard['type_arg'],
                    'player_id' => $player_id,
                    'player_name' => self::getActivePlayerName(),
                ));
            // Put card in front of user to remember the "useless evidence".
            $this->cards->moveCard($card_id, "player_display", $player_id);
        }
        $this->gamestate->nextState('nextTurn');
    }

    function solveCase($tile_ids) {
        self::checkAction("solveCase");
        $minigame = self::getGameStateValue('minigame');
        $player_id = self::getActivePlayerId();
        $color = $this->constants['HEX2COLORNAME'][self::getCurrentPlayerColor()];

        // Get material ids for the solution (case cards) and the proposed
        // solution (tiles). Then find the 3 aspects, and compare them.
        $card_mids = array_pluck($this->getPlayerCaseCards($player_id), 'type_arg');
        $tiles = $this->cards->getCards($tile_ids);
        $tile_mids = array_pluck($tiles, 'type_arg');
        $proposed_solution = array(
            $this->tiles[$tile_mids[0]]['tiletype'] => $this->tiles[$tile_mids[0]]['name'],
            $this->tiles[$tile_mids[1]]['tiletype'] => $this->tiles[$tile_mids[1]]['name'],
            $this->tiles[$tile_mids[2]]['tiletype'] => $this->tiles[$tile_mids[2]]['name']
        );
        $solution = $this->getPlayerCaseSolution($player_id);
        $player_correct = $proposed_solution == $solution;

        if ($player_correct) {
            // Score + mark as inactive for the rest of the minigame.
            $points_winnable = self::getGameStateValue('points_winnable');
            self::DbQuery("
                UPDATE player
                SET player_score = player_score + " . $points_winnable . ",
                    player_solved_in_round = " . self::getGameStateValue('minigame_round') . "
                WHERE player_id = $player_id
            ");
            $this->tokens->moveToken("vp_{$color}_" . ($minigame - 1), "vp_{$points_winnable}");
            self::setStat($points_winnable, "vp_{$minigame}", $player_id);

            // Calculate the 'average cubes required to solve' stats
            $cubes_on_board = $this->constants['CUBES_PER_PLAYER'] - $this->tokens->countTokensInLocation("cubes_{$player_id}");
            $solved_mg_so_far = self::getStat("solved_minigames", $player_id);
            $current_val = self::getStat('avg_cubes_to_solve', $player_id);
            // Calculate the "rolling average".
            self::setStat(
                ($current_val * $solved_mg_so_far + $cubes_on_board) / ($solved_mg_so_far + 1),
                'avg_cubes_to_solve',
                $player_id
            );

            // Calculate the 'average discs required to solve' stats
            $discs_on_board = $this->constants['DISCS_PER_PLAYER'] - $this->tokens->countTokensInLocation("discs_{$player_id}");
            $solved_mg_so_far = self::getStat("solved_minigames", $player_id);
            $current_val = self::getStat('avg_discs_to_solve', $player_id);
            // Calculate the "rolling average".
            self::setStat(
                ($current_val * $solved_mg_so_far + $discs_on_board) / ($solved_mg_so_far + 1),
                'avg_discs_to_solve',
                $player_id
            );

            // Increment the solve_minigames stat.
            self::incStat(1, "solved_minigames", $player_id);

            // Move cubes back to player supply; puts discs on solution.
            $this->tokens->moveTokens(
                array_pluck($this->tokens->getTokensOfTypeInLocation("cube_{$color}_%"), 'key'),
                "cubes_{$player_id}");
            $locslot_ids = array_pluck($tiles, 'location_arg');
            foreach ($locslot_ids as $i => $locslot_id) {
                $this->tokens->moveToken("disc_{$color}_{$i}", "locslot_{$locslot_id}");
            }
            // Also investigators that have been used go back to the box.
            $used_investigators = $this->tokens->getTokensOfTypeInLocation("pi_{$color}_%", 'agentarea_%');
            if ($used_investigators) {
                $this->tokens->moveTokens(array_pluck($used_investigators, 'key'), 'box');
            }
            self::notifyAllPlayers(
                'placeTokens', '',
                array('tokens' => array_values(array_merge(
                    $this->tokens->getTokensOfTypeInLocation("disc_{$color}_%"),
                    $this->tokens->getTokensOfTypeInLocation("vp_{$color}_%"),
                    $this->tokens->getTokensOfTypeInLocation("cube_{$color}_%"),
                    $this->tokens->getTokensOfTypeInLocation("pi_{$color}_%")
                )))
            );
            $this->notifyAnimate();
            self::notifyAllPlayers(
                'playerSolved',
                clienttranslate('${player_name} solved their case!'),
                array(
                    'player_id' => $player_id,
                    'player_name' => self::getActivePlayerName(),
                )
            );
        } else {
            // Give penalty points
            self::DbQuery("
                UPDATE player
                SET player_score = player_score - 2,
                    player_score_aux = player_score_aux - 2,
                    player_penalty = player_penalty - 2
                WHERE player_id = $player_id
            ");
            self::incStat(-2, "penalty_" . self::getGameStateValue('minigame'), $player_id);
            // Move token to appropriate penalty slot. Note: the max here is
            // -10. To be discussed: is -10 the max penalty we can give; or is
            // it just a UI thing. The rules say nothing about it.
            $total_pen = self::getUniqueValueFromDB("SELECT player_penalty FROM player WHERE player_id = $player_id");
            $this->tokens->moveToken("penalty_{$color}", "penalty_" . min(abs($total_pen), 10));
            self::notifyAllPlayers(
                'placeToken', '',
                array('token' => $this->tokens->getTokenInfo("penalty_{$color}"))
            );
            self::notifyAllPlayers(
                'playerFailed',
                clienttranslate('${player_name} tried to solve (${suspect}, ${location}, ${crime}), without success.'),
                array(
                    'i18n' => array('crime', 'location', 'suspect'),
                    'crime' => $proposed_solution['crime'],
                    'location' => $proposed_solution['location'],
                    'suspect' => $proposed_solution['suspect'],
                    'player_name' => self::getActivePlayerName()
                )
            );
        }
        $this->notifyNewScores();
        $this->gamestate->nextState('nextTurn');
    }

    /**
     * Depending on the minigame we are in, set the gamestate to nextMinigame or
     * endGame.
     */
    function gotoNextMinigameOrEndGame()
    {
        $in_last_minigame = self::getGameStateValue('minigame') == $this->constants['MINIGAMES'];
        return $this->gamestate->nextState($in_last_minigame ? 'endGame' : 'nextMinigame');
    }


//////////////////////////////////////////////////////////////////////////////
//////////// Game state arguments
////////////

    /*
        Here, you can create methods defined as "game state arguments" (see "args" property in states.inc.php).
        These methods function is to return some additional information that is specific to the current
        game state.
    */

    function argPlayerTurn()
    {
        $args = array(
            'remainingInvestigators' => $this->tokens->countTokensInLocation("pi_supply_" . self::getActivePlayerId())
        );
        return $args;
    }

    function argStartMinigame()
    {
        $args = array('_private' => array());
        $args = array_merge(
            $args,
            $this->getPublicGameInfos()
        );
        // Inform about private information (hands)
        $players = self::loadPlayersBasicInfos();
        foreach($players as $player_id => $player) {
            $args['_private'][$player_id] = $this->getPrivateGameInfos($player_id);
        }
        return $args;
    }

//////////////////////////////////////////////////////////////////////////////
//////////// Game state actions
////////////

    /*
        Here, you can create methods defined as "game state actions" (see "action" property in states.inc.php).
        The action method of state X is called everytime the current game state is set to X.
    */

    function st_setupMinigame()
    {
        self::incGameStateValue('minigame', 1);
        $minigame = self::getGameStateValue('minigame');

        self::setGameStateValue('minigame_round', 1);
        self::setStat(1, "rounds_{$minigame}");
        self::setGameStateValue('points_winnable', 7);
        self::DbQuery("UPDATE `player` SET `player_solved_in_round` = NULL");

        // Get all cards, sort into piles, shuffle piles.
        $this->cards->moveAllCardsInLocation(null, "offtable");
        $this->cards->moveCards(array_pluck($this->cards->getCardsOfType('evidence'), 'id'), 'deck');
        $this->cards->shuffle('deck');
        $this->cards->moveCards(array_pluck($this->cards->getCardsOfType('crime'), 'id'), 'crime_deck');
        $this->cards->shuffle('crime_deck');
        $this->cards->moveCards(array_pluck($this->cards->getCardsOfType('location'), 'id'), 'location_deck');
        $this->cards->shuffle('location_deck');
        $this->cards->moveCards(array_pluck($this->cards->getCardsOfType('suspect'), 'id'), 'suspect_deck');
        $this->cards->shuffle('suspect_deck');

        // Get all tiles, shuffle them in two decks, associate them with
        // location slots. Decks are called "cri_tile_d" (crime tile deck) and
        // "sus_tile_d" (suspect tile deck). VARCHAR(16) ftw!
        $this->cards->moveCards(array_pluck($this->cards->getCardsOfType('tile_crime'), 'id'), 'cri_tile_d');
        $this->cards->moveCards(array_pluck($this->cards->getCardsOfType('tile_suspect'), 'id'), 'sus_tile_d');
        $this->cards->shuffle('cri_tile_d');
        $this->cards->shuffle('sus_tile_d');
        foreach($this->locations as $loc_id => $loc) {
            $this->cards->pickCardForLocation('cri_tile_d', 'locslot', $loc['slots']['crime']['id']);
            $this->cards->pickCardForLocation('sus_tile_d', 'locslot', $loc['slots']['suspect']['id']);

            // Get the specific location tile (also currently 'offboard') and
            // put it into its fixed location slot.
            //
            // (28 + $loc_id) is the "material id" aka type_arg of the fake
            // location tile.
            $temp = $this->cards->getCardsOfType('tile_location', 28 + $loc_id); // required, as array_shift does not want a direct reference
            $location_tile = array_shift($temp);
            $this->cards->moveCard($location_tile['id'], 'locslot', $loc['slots']['location']['id']);
        }

        // Main display of evidence cards
        $this->cards->pickCardsForLocation($this->constants['EVIDENCE_DISPLAY_SIZE'], 'deck', 'evidence_display');

        // Investigators that have been used go back to the box.
        $used_investigators = $this->tokens->getTokensOfTypeInLocation('pi_%', 'agentarea_%');
        if ($used_investigators) {
            $this->tokens->moveTokens(array_pluck($used_investigators, 'key'), 'box');
        }

        // Set up a case for every player and distribute the case cards to their
        // right neighbor.
        $players = self::loadPlayersBasicInfos();
        foreach($players as $player_id => $player) {
            $this->cards->pickCard('crime_deck', $player_id);
            $this->cards->pickCard('location_deck', $player_id);
            $this->cards->pickCard('suspect_deck', $player_id);

            // Give their tokens
            $color = $this->constants['HEX2COLORNAME'][$player['player_color']];
            $this->tokens->moveTokens(
                array_pluck($this->tokens->getTokensOfTypeInLocation("cube_{$color}_%"), 'key'),
                "cubes_{$player_id}");
            $this->tokens->moveTokens(
                array_pluck($this->tokens->getTokensOfTypeInLocation("disc_{$color}_%"), 'key'),
                "discs_{$player_id}");
        }

        // Trigger clean up in frontend. Sending only tokens that are to be
        // moved away.
        self::notifyAllPlayers("cleanBoard", "", array(
            'tokens' => array_values(array_merge(
                $this->tokens->getTokensInLocation('box'),
                $this->tokens->getTokensInLocation('offtable'),
                $this->tokens->getTokensInLocation('cubes_%'),
                $this->tokens->getTokensInLocation('discs_%')
            ))
        ));
        $this->notifyAnimate();

        $this->gamestate->nextState(); // start minigame
    }

    function st_startMinigame()
    {
        $minigame = self::getGameStateValue('minigame');
        self::notifyAllPlayers(
            "message",
            array(
                1 => clienttranslate('The first of three mini-games starts.'),
                2 => clienttranslate('The second mini-game starts.'),
                3 => clienttranslate('The third and final mini-game starts.'),
            )[$minigame],
            array()
        );

        // Select a new first player. In minigame 1 it's player_no 1, in
        // minigame 2 player_no 2 etc.; using module to cover the 'more rounds
        // than players' case.
        $next_player_no = $this->getStartPlayerNo($minigame);
        $next_player_id = self::getUniqueValueFromDB("SELECT player_id FROM player WHERE player_no = $next_player_no");
        $this->gamestate->changeActivePlayer($next_player_id);
        self::incStat(1, "turns_number");
        self::incStat(1, "turns_number", $next_player_id);
        $this->gamestate->nextState(); // always STATE_PLAYER_TURN
    }

    function st_gameTurn()
    {
        $active_player_id = self::getActivePlayerId(); // not really 'active', as this is a 'game' turn.
        $minigame = self::getGameStateValue('minigame');

        // Player who did not solve yet
        $unsolved_player_ids = self::getObjectListFromDB(
            "SELECT player_id FROM player WHERE player_solved_in_round IS NULL", true);

        if (count($unsolved_player_ids) == 0) {
            // Great, everybody finished successfully! Let's move on!
            self::notifyAllPlayers(
                'minigameEnds',
                clienttranslate('All players solved their cases! This ends the mini-game.'),
                array()
            );
            return $this->gotoNextMinigameOrEndGame();
        }

        // First check if the round is over; then we start a new minigame, or
        // even end the game completely, if we are already in the last minigame.
        // Round is over once all players solved; or even if a new round starts
        // and only 1 player remains with an unsolved case.

        // Look for the next 'unsolved' player to activate. If we get to (or
        // skip over) the current mini-game's start player, then the current
        // round is over.
        $round_over = false;
        $start_player_id = $this->getStartPlayerId($minigame);
        $next_player_id = $active_player_id;
        do {
            $next_player_id = self::getPlayerAfter($next_player_id);
            if ($round_over || $next_player_id == $start_player_id) $round_over = true;
        } while (!in_array($next_player_id, $unsolved_player_ids));

        if ($round_over) {
            // Is only one player with unsolved case left? -> start new minigame
            if (count($unsolved_player_ids) == 1) {
                // Put VP token of unsolved player to "0"
                $unsolved_player_id = array_shift($unsolved_player_ids);
                $unsolved_player = self::loadPlayersBasicInfos()[$unsolved_player_id];
                $unsolved_color = $this->constants['HEX2COLORNAME'][$unsolved_player['player_color']];
                $token_key = "vp_{$unsolved_color}_" . ($minigame - 1);
                $token_target = "vp_0";
                $this->tokens->moveToken($token_key, $token_target);
                self::notifyAllPlayers(
                    'placeToken',
                    '',
                    array('token' => array("key" => $token_key, "location" => $token_target))
                );
                self::notifyAllPlayers(
                    'minigameEnds',
                    clienttranslate('The round is over and only 1 player (${player_name}) remains with an unsolved case. This ends the mini-game.'),
                    array(
                        'player_name' => $unsolved_player['player_name'],
                    )
                );
                return $this->gotoNextMinigameOrEndGame();
            }
            // Did any player solve in that round? Then decrease points_winnable
            $round = self::getGameStateValue('minigame_round');
            $sql = "SELECT COUNT(player_id) FROM player WHERE player_solved_in_round = $round";
            if (self::getUniqueValueFromDB($sql)) {
                self::setGameStateValue(
                    'points_winnable',
                    max(0, self::getGameStateValue('points_winnable') - 2));
            }
            self::incGameStateValue('minigame_round', 1);
            self::incStat(1, "rounds_{$minigame}");
        }

        // TODO: Warn the next active player when it's their last chance to
        // solve, i.e. when they are the last one with an unsolved case in the
        // current minigame.

        // Draw a new card for evidence display
        $this->replenishEvidenceDisplay();

        $this->gamestate->changeActivePlayer($next_player_id);
        self::giveExtraTime($next_player_id);
        self::incStat(1, "turns_number");
        self::incStat(1, "turns_number", $next_player_id);
        $this->gamestate->nextState('nextPlayer'); // -> STATE_PLAYER_TURN
    }


//////////////////////////////////////////////////////////////////////////////
//////////// Zombie
////////////

    /*
        zombieTurn:

        This method is called each time it is the turn of a player who has quit the game (= "zombie" player).
        You can do whatever you want in order to make sure the turn of this player ends appropriately
        (ex: pass).
    */
    function zombieTurn($state, $active_player)
    {
        $statename = $state['name'];
        $player_id = $active_player;

        if ($statename === "playerTurn") {
            // For the zombie player not to block the mini-game to be able to
            // finish, the zombie must not count as a "still unsolved player".
            // Thus we let zombie fake-solve, but giving 0 VP. This makes the
            // player inactive and lets all real players finish.
            self::DbQuery("
                UPDATE player
                SET player_solved_in_round = " . self::getGameStateValue('minigame_round') . "
                WHERE player_id = $player_id
            ");
            self::notifyAllPlayers(
                'playerSolved',
                '',
                array(
                    'player_id' => $player_id,
                )
            );
            $this->gamestate->nextState('nextTurn');
            return;
        }

        throw new feException("Zombie mode not supported at this game state: " . $statename);
    }

///////////////////////////////////////////////////////////////////////////////////:
////////// DB upgrade
//////////

    /*
        upgradeTableDb:

        You don't have to care about this until your game has been published on BGA.
        Once your game is on BGA, this method is called everytime the system detects a game running with your old
        Database scheme.
        In this case, if you change your Database scheme, you just have to apply the needed changes in order to
        update the game database and allow the game to continue to run with your new version.

    */

    function upgradeTableDb( $from_version )
    {
        // $from_version is the current version of this game database, in numerical form.
        // For example, if the game was running with a release of your game named "140430-1345",
        // $from_version is equal to 1404301345

        // Example:
//        if( $from_version <= 1404301345 )
//        {
//            $sql = "ALTER TABLE xxxxxxx ....";
//            self::DbQuery( $sql );
//        }
//        if( $from_version <= 1405061421 )
//        {
//            $sql = "CREATE TABLE xxxxxxx ....";
//            self::DbQuery( $sql );
//        }
//        // Please add your future database scheme changes here
//
//


    }
}
