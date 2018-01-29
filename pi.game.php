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

        // Create tokens, and put into 'supply'
        $this->tokens->createTokens($this->tokeninfos, 'supply');

        // Give each player their 5 investigators. These will *not* be
        // replenished on each minigame.
        $players = self::loadPlayersBasicInfos();
        foreach($players as $player_id => $player) {
            $color = $this->constants['HEX2COLORNAME'][$player['player_color']];
            $this->tokens->moveTokens(
                array_pluck($this->tokens->getTokensOfTypeInLocation("pi_{$color}_%"), 'key'),
                "pi_supply_{$player_id}");
        }

        // TODO: Init game statistics
        // (note: statistics used in this file must be defined in your stats.inc.php file)
        //self::initStat( 'table', 'table_teststat1', 0 );    // Init a table statistics
        //self::initStat( 'player', 'player_teststat1', 0 );  // Init a player statistics (for all players)

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
        $current_player_id = self::getCurrentPlayerId();    // !! We must only return informations visible by this player !!
        $minigame = self::getGameStateValue("minigame");

        // Get information about players
        $sql = "
            SELECT
                player_id as id,
                player_score as score,
                player_solved_in_round as solved_in_round,
                player_no = $minigame as is_startplayer
            FROM player
        ";
        $result['players'] = self::getCollectionFromDb($sql);

        // Gather all information about current game situation (visible by player $current_player_id).

        // Global / static information
        $result['cardinfos'] = $this->cardBasis;
        $result['tileinfos'] = $this->tiles;

        $result = array_merge($result, $this->getPrivateGameInfos($current_player_id));
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
        // TODO: improve
        // The base percentage is based on the minigame we are in: 1=0%; 2=33%;
        // 3=66%. For the in-minigame percentage we average over each user's
        // progress (how many clues have they figured out yet, again:
        // 0/33/66/100%).
        $max = self::getGameStateValue("minigame") * (100 / $this->constants['MINIGAMES']);

        // Very naive, but we rarely should exhaust the deck in one minigame, so
        // let's treat the drawn cards as an indicator.
        $card_progress = ($this->cards->countCardInLocation('discard')
                          + $this->cards->countCardInLocation('player_display')) / $this->constants['EVIDENCE_DECK_SIZE'];
        // TODO: $player_progress = percent of player that solved already
        // $progress = max($card_progress, $player_progress)
        $progress = $card_progress * $max;
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

    /**
     * For the given card_id, find the tile that it corresponds to and return
     * all adjacent tiles' names (only considering tiles with the same casetype,
     * e.g. only suspects).
     */
    function getAdjacentTileNames($card_id)
    {
        // Get the tile that corresponds to the card.
        // $card = $this->cards->getCard($card_id);
        $tile = $this->getCorrespondingTile($card_id);

        // Assert that the tile is actually on the board.
        if ($tile['location'] != 'locslot') {
            throw new BgaVisibleSystemException("Tile is not on the board. Please report this.");
        }

        $tile_slot_id = $tile['location_arg'];
        $location_mid = floor((int)$tile_slot_id / 100); // the invers of ($loc_id * 100 + 1|2|3)
        $neighbor_mids = $this->locations[$location_mid]['neighbors'];
        $adjacent_slot_ids = array_pluck(
            array_flatten(array_pluck(
                array_filter_by_keys($this->locations, $neighbor_mids),
                'slots')),
            'id');
        $sql = "SELECT card_type_arg FROM `card`
                WHERE card_type = '{$tile['type']}'
                  AND card_location_arg IN (" . implode(',', $adjacent_slot_ids) . ")";
        $tile_mids = self::getObjectListFromDB($sql, true);
        return array_pluck(array_filter_by_keys($this->tiles, $tile_mids), 'name');
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
        return array(
            // Evidence cards on display
            'evidence_display' => $this->cards->getCardsInLocation('evidence_display'),
            'evidence_discard' => $this->cards->getCardsInLocation('discard'),
            'player_display_cards' => $this->cards->getCardsInLocation('player_display'),
            'tiles' => $this->cards->getCardsInLocation('locslot')
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
            // Rare case, but it could happen. Players are now forced to do
            // something else. But this is implicit from the UI: no more cards,
            // no more clicks on them. Solving is always the last ressort.
            self::notifyAllPlayers(
                'evidenceExhausted',
                clienttranslate('Evidence cards are exhausted and cannot be played anymore.'),
                array());
        }
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

        // No more investigator. Should be handled in the UI, but safety first.
        if ($this->tokens->countTokensInLocation("pi_supply_{$player_id}") == 0) {
            throw new BgaUserException(self::_("You have no investigators left."));
        }

        // Check if player already has an investigator at this location.
        if (count($this->tokens->getTokensOfTypeInLocation("pi_{$color}_", "location_{$location_id}"))) {
            throw new BgaUserException(self::_("You already have an investigator at this location."));
        }

        $solution = $this->getPlayerCaseSolution($player_id);

        // TODO: Place investigator token here.
        // TODO: get the 3 tiles at this location and do a check for each one of them

    }

    function selectEvidence($card_id)
    {
        self::checkAction("selectEvidence");
        $player_id = self::getActivePlayerId();
        $currentCard = $this->cards->getCard($card_id);
        $card_name = $this->cardBasis[$currentCard['type_arg']]['name'];

        // Should not happen; also anti-cheat
        if ($currentCard['location'] != "evidence_display") {
            throw new BgaUserException(self::_("Card is not on display. Press F5 in case of problems."));
        }

        $solution = $this->getPlayerCaseSolution($player_id);

        // Check for a match with the player's case.
        if (in_array($card_name, $solution)) {
            // Full match
            // // TODO: Put player-colored disc onto the locslot (if not there already)
            // $discs = $this->tokens->getTokensOfTypeInLocation('disc_%', "supply_{$player_id}");
            // if (!$discs) {
            //     // The disc for this match is out there already; should be on the slot's parent location/agent slot.
            //     // TODO: find it and put it on the locslot
            // } else {
            //     $disc = array_shift($discs);
            // }
            // $disc = $this->tokens->getTokenOnTop('');
            // if (!$disc) {
            //     throw new BgaUserException(self::_("No more discs in your supply. You should be able to solve the case!"));
            // }

            // $newtokens = ... 

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
                    // 'new_tokens' => ..., // or rather do this as a separate notification?
                    'player_id' => $player_id,
                    'player_name' => self::getActivePlayerName()
                ));
            $this->gamestate->nextState('nextTurn');
            return;
        }

        // Next, check if we have a match in adjacent locations.
        $adjacent_tile_names = $this->getAdjacentTileNames($card_id);
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
            self::notifyAllPlayers(
                'evidenceClose',
                clienttranslate('${player_name} found out that ${card_name} <b>is close</b> to the actual ${casetype}.'),
                array(
                    'i18n' => array('card_name'),
                    'card_id' => $card_id,
                    'card_name' => $card_name,
                    'card_type' => $currentCard['type_arg'],
                    'casetype' => $this->constants['CASETYPES'][$match_casetype],
                    'player_id' => $player_id,
                    'player_name' => self::getActivePlayerName(),
                ));
            // Put card on discard
            $this->cards->insertCardOnExtremePosition($card_id, "discard", true);
        } else {
            // No match at all
            self::notifyAllPlayers(
                'evidenceWrong',
                clienttranslate('${player_name} had no luck following evidence ${card_name}.'),
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
        $player_id = self::getActivePlayerId();

        // Get material ids for the solution (case cards) and the proposed
        // solution (tiles). Then find the 3 aspects, and compare them.
        $card_mids = array_pluck($this->getPlayerCaseCards($player_id), 'type_arg');
        $tile_mids = array_pluck($this->cards->getCards($tile_ids), 'type_arg');
        $proposed_solution = array(
            $this->tiles[$tile_mids[0]]['tiletype'] => $this->tiles[$tile_mids[0]]['name'],
            $this->tiles[$tile_mids[1]]['tiletype'] => $this->tiles[$tile_mids[1]]['name'],
            $this->tiles[$tile_mids[2]]['tiletype'] => $this->tiles[$tile_mids[2]]['name']
        );
        $solution = $this->getPlayerCaseSolution($player_id);
        $player_correct = $proposed_solution == $solution;

        if ($player_correct) {
            // Score + mark as inactive for the rest of the minigame.
            self::DbQuery("
                UPDATE player
                SET player_score = player_score + " . self::getGameStateValue('points_winnable') . ",
                    player_solved_in_round = " . self::getGameStateValue('minigame_round') . "
                WHERE player_id = $player_id
            ");
            // TODO: use this notification to grey out the player area
            self::notifyAllPlayers(
                'playerSolved',
                // TODO: improve wording
                clienttranslate('${player_name} solved their case successfully!'),
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
                    player_penalty = player_penalty - 2
                WHERE player_id = $player_id
            ");
            self::notifyAllPlayers(
                'playerFailed',
                // TODO: friendlier wording
                clienttranslate('${player_name} tried to solve (${suspect}, ${crime}, ${location}), but failed.'),
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

//////////////////////////////////////////////////////////////////////////////
//////////// Game state arguments
////////////

    /*
        Here, you can create methods defined as "game state arguments" (see "args" property in states.inc.php).
        These methods function is to return some additional information that is specific to the current
        game state.
    */

    /*
    Example for game state "MyGameState":

    function argMyGameState()
    {
        // Get some values from the current game situation in database...
        // return values:
        return array(
            'variable1' => $value1,
            'variable2' => $value2,
            ...
        );
    }
    */

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
        self::setGameStateValue('minigame_round', 1);
        self::setGameStateValue('points_winnable', 7);
        self::DbQuery("UPDATE `player` SET `player_solved_in_round` = NULL");

        $minigame = self::getGameStateValue('minigame');

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

        // Tokens: first, all cubes and discs back into the supply
        $this->tokens->moveTokens(array_pluck($this->tokens->getTokensOfTypeInLocation('cube_%'), 'key'), 'supply');
        $this->tokens->moveTokens(array_pluck($this->tokens->getTokensOfTypeInLocation('disc_%'), 'key'), 'supply');

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
                "supply_{$player_id}");
            $this->tokens->moveTokens(
                array_pluck($this->tokens->getTokensOfTypeInLocation("cube_{$color}_%"), 'key'),
                "supply_{$player_id}");
        }

        $notifText = array(
            1 => clienttranslate('The first of three mini-games starts.'),
            2 => clienttranslate('The second mini-game starts.'),
            3 => clienttranslate('The third and final mini-game starts.'),
        );

        // Inform about new public status
        self::notifyAllPlayers(
            "newMinigame",
            $notifText[$minigame],
            $this->getPublicGameInfos());

        // Inform about private information (hands)
        foreach($players as $player_id => $player) {
            self::notifyPlayer(
                $player_id,
                "newMinigamePrivate",
                '',
                $this->getPrivateGameInfos($player_id));
        }

        // Select a new first player. In minigame 1 it's player_no 1, in
        // minigame 2 player_no 2 etc.; using module to cover the 'more rounds
        // than players' case.
        $next_player_no = ((self::getGameStateValue("minigame") - 1) % count($players)) + 1;
        $next_player_id = self::getUniqueValueFromDB("SELECT player_id FROM player WHERE player_no = $next_player_no");
        $this->gamestate->changeActivePlayer($next_player_id);
        $this->gamestate->nextState();  // always a player turn
    }

    function st_gameTurn()
    {
        $active_player_id = self::getActivePlayerId(); // not really 'active', as this is a 'game' turn.
        $minigame = self::getGameStateValue('minigame');
        $in_last_minigame = $minigame == $this->constants['MINIGAMES'];

        // Player who did not solve yet
        $unsolved_player_ids = self::getObjectListFromDB(
            "SELECT player_id FROM player WHERE player_solved_in_round IS NULL", true);

        if (count($unsolved_player_ids) == 0) {
            // Great, everybody finished successfully! Let's move on!
            $this->gamestate->nextState($in_last_minigame ? 'endGame' : 'nextMinigame');
            return;
        }

        // First check if the round is over; then we start a new minigame, or
        // even end the game completely, if we are already in the last minigame.
        // Round is over once all players solved; or even if a new round starts
        // and only 1 player remains with an unsolved case.

        // A round (within this minigame) is over if the (potential) next player
        // has `player_no` == current minigame number.
        $player_after_id = self::getPlayerAfter($active_player_id);
        $sql = "SELECT player_no FROM player WHERE player_id = $player_after_id";
        $round_over = self::getUniqueValueFromDB($sql) == $minigame;

        if ($round_over) {
            // Is only one player with unsolved case left? -> start new minigame
            if (count($unsolved_player_ids) == 1) {
                $this->gamestate->nextState($in_last_minigame ? 'endGame' : 'nextMinigame');
                return;
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
        }

        // TODO: Warn the next active player when it's their last chance to
        // solve, i.e. when they are the last one with an unsolved case in the
        // current minigame.

        // Draw a new card for evidence display
        $this->replenishEvidenceDisplay();

        // Look for the next 'unsolved' player to activate.
        do {
            $next_active_player_id = self::activeNextPlayer();
        } while (!in_array($next_active_player_id, $unsolved_player_ids));

        self::giveExtraTime($next_active_player_id);
        $this->gamestate->nextState('nextPlayer');
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

    function zombieTurn( $state, $active_player )
    {
        $statename = $state['name'];

        if ($state['type'] === "activeplayer") {
            switch ($statename) {
                default:
                    $this->gamestate->nextState( "zombiePass" );
                    break;
            }

            return;
        }

        if ($state['type'] === "multipleactiveplayer") {
            // Make sure player is in a non blocking status for role turn
            $this->gamestate->setPlayerNonMultiactive( $active_player, '' );

            return;
        }

        throw new feException( "Zombie mode not supported at this game state: ".$statename );
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
