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
  * fabiantest.game.php
  *
  * This is the main file for your game logic.
  *
  * In this PHP file, you are going to defines the rules of the game.
  *
  */


require_once( APP_GAMEMODULE_PATH.'module/table/table.game.php' );
require_once( 'modules/utils.php' );


class fabiantest extends Table
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
            // P.I. is played over 3 "mini games". Here we store in which of these games we are. Valid values: 1, 2, 3
            "minigame" => 10,
            // "my_second_global_variable" => 11,
            // "my_first_game_variant" => 100,
            // "my_second_game_variant" => 101,
        ));

        $this->cards = self::getNew("module.common.deck");
        $this->cards->init("card");
    }

    protected function getGameName( )
    {
        // Used for translations and stuff. Please do not modify.
        return "fabiantest";
    }

    /*
        setupNewGame:

        This method is called only once, when a new game is launched.
        In this method, you must setup the game according to the game rules, so that
        the game is ready to be played.
    */
    protected function setupNewGame( $players, $options = array() )
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
        self::DbQuery( $sql );
        self::reattributeColorsBasedOnPreferences( $players, $gameinfos['player_colors'] );
        self::reloadPlayersBasicInfos();

        /************ Start the game initialization *****/

        // First create all the cards we have.
        // - 36 Evidence cards
        // - 12 Suspects
        // - 10 Crimes
        // - 14 Locations

        $cards = array();
        // Create Evidence cards
        foreach ($this->cardBasis as $card_id => $card) {
            $cards[] = array('type' => 'evidence',
                             'type_arg' => $this->getCardTypeArg('evidence', $card_id),
                             'nbr' => 1);
        }
        // Create Case cards
        foreach ($this->cardBasis as $card_id => $card) {
            $cards[] = array('type' => $card['casetype'],
                             'type_arg' => $this->getCardTypeArg($card['casetype'], $card_id),
                             'nbr' => 1);
        }
        // Create tiles -- not actual cards, but handled similarly
        foreach ($this->tiles as $tile_id => $tile) {
            $cards[] = array('type' => 'tile_' . $tile['tiletype'],
                             'type_arg' => $tile_id,
                             'nbr' => 1);
        }

        // Create all, but don't put them into 'deck' yet, the piles have to be
        // sorted first.
        $this->cards->createCards($cards, 'offtable');

        // TODO: Init game statistics
        // (note: statistics used in this file must be defined in your stats.inc.php file)
        //self::initStat( 'table', 'table_teststat1', 0 );    // Init a table statistics
        //self::initStat( 'player', 'player_teststat1', 0 );  // Init a player statistics (for all players)
        
        // Setup the initial game situation here
        // We start with minigame number 1. There will be 3 minigames in total.
        self::setGameStateInitialValue( 'minigame', 1 );
        $this->startNewMinigame(1);

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

        // Get information about players
        // Note: you can retrieve some extra field you added for "player" table in "dbmodel.sql" if you need it.
        $sql = "SELECT player_id id, player_score score FROM player";
        $result['players'] = self::getCollectionFromDb($sql);

        // Gather all information about current game situation (visible by player $current_player_id).

        // Global / static information
        $result['cardinfos'] = $this->cardBasis;
        $result['tileinfos'] = $this->tiles;

        // Cards in player hand (the other player's case cards)
        $result['hand'] = $this->cards->getCardsInLocation('hand', $current_player_id);
        
        // Evidence cards on display
        $result['evidence_display'] = $this->cards->getCardsInLocation('evidence_display');
        $result['evidence_discard'] = $this->cards->getCardsInLocation('discard');
        $result['player_display_cards'] = $this->cards->getCardsInLocation('player_display');
        $result['tiles'] = $this->cards->getCardsInLocation('locslot');

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
        $max = self::getGameStateValue("minigame") * 33;

        // Very naive, but we rarely should exhaust the deck in one minigame, so
        // let's treat the drawn cards as an indicator.
        $progress = ($this->cards->countCardInLocation('discard')
                     + $this->cards->countCardInLocation('player_display')) / $this->constants['EVIDENCE_DECK_SIZE'];
        $progress *= $max;
        return floor($progress);
    }


//////////////////////////////////////////////////////////////////////////////
//////////// Utility functions
////////////

    /*
        In this space, you can put any utility methods useful for your game logic
    */

    function startNewMinigame($n)
    {
        self::setGameStateValue('minigame', $n);

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
        // $this->cards->moveCards(array_pluck($this->cards->getCardsOfType('tile_location'), 'id'), 'loc_tile_d'); // the fake one
        $this->cards->moveCards(array_pluck($this->cards->getCardsOfType('tile_suspect'), 'id'), 'sus_tile_d');
        $this->cards->shuffle('cri_tile_d');
        $this->cards->shuffle('sus_tile_d');
        foreach($this->locations as $loc_id => $loc) {
            $this->cards->pickCardForLocation('cri_tile_d', 'locslot', $loc['slots']['crime']['id']);
            // $this->cards->pickCardForLocation('loc_tile_d', 'locslot', $loc['slots']['location']['id']);
            $this->cards->pickCardForLocation('sus_tile_d', 'locslot', $loc['slots']['suspect']['id']);

            // Get the specific location tile (also currently 'offboard') and
            // put it into its fixed location slot.
            //
            // (28 + $loc_id) is the "material id" aka type_arg of the fake
            // location tile.
            // var_dump("TILE LOCATIONS");
            // var_dump($this->cards->getCardsOfType('tile_location', 28 + $loc_id));
            $temp = $this->cards->getCardsOfType('tile_location', 28 + $loc_id); // required, as array_shift does not want a direct reference
            $location_tile = array_shift($temp);
            $this->cards->moveCard($location_tile['id'], 'locslot', $loc['slots']['location']['id']);
        }
        // foreach($this->cards->getCardsOfType('tile_location') as $tile_id => $tile) {
        // }

        // Main display of evidence cards
        $this->cards->pickCardsForLocation($this->constants['EVIDENCE_DISPLAY_SIZE'], 'deck', 'evidence_display');

        // Set up a case for every player and distribute the case cards to their
        // right neighbor.
        $players = self::loadPlayersBasicInfos();
        foreach($players as $player_id => $player) {
            $this->cards->pickCard('crime_deck', $player_id);
            $this->cards->pickCard('location_deck', $player_id);
            $this->cards->pickCard('suspect_deck', $player_id);
        }

        // Select a new first player. In minigame 1 it's player_no 1, in
        // minigame 2 player_no 2 etc.; using module to cover the 'more rounds
        // than players' case.
        $next_player_no = ((self::getGameStateValue("minigame") - 1) % count($players)) + 1;
        $next_player_id = self::getUniqueValueFromDB("SELECT player_id FROM player WHERE player_no = $next_player_no");
        $this->gamestate->changeActivePlayer($next_player_id);
    }

    function getCardTypeArg($type, $i) {
        if ($type == 'evidence') {
            $offset = 0;
        } else if (in_array($type, array('crime', 'location', 'suspect'))) {
            $offset = 36;
        }
        return $offset + $i;
    }

    /**
     * Return if it is the last turn for this minigame.
     *
     * It's the last turn if all other players have already solved their cases.
     */
    function isLastTurn() {
        // TODO
    }

    // function getCardNames($ids)
    // {
    //     return array_pluck(
    //         array_filter($this->cardBasis, function($k) {
    //             return in_array($k, $ids);
    //         }, ARRAY_FILTER_USE_KEY),
    //         'name'
    //     );
    // }

    /**
     * Return the cards cards that represent the solution for the given player.
     * These are the player's right neighbors hand cards.
     */
    function getPlayerCaseCards($player_id)
    {
        return $this->cards->getPlayerHand(self::getPlayerBefore($player_id));
    }

//////////////////////////////////////////////////////////////////////////////
//////////// Player actions
////////////

    /*
        Each time a player is doing some game action, one of the methods below is called.
        (note: each method below must match an input method in fabiantest.action.php)
    */

    /*

    Example:

    function playCard( $card_id )
    {
        // Check that this is the player's turn and that it is a "possible action" at this game state (see states.inc.php)
        self::checkAction( 'playCard' );

        $player_id = self::getActivePlayerId();

        // Add your game logic to play a card there
        ...

        // Notify all players about the card played
        self::notifyAllPlayers( "cardPlayed", clienttranslate( '${player_name} plays ${card_name}' ), array(
            'player_id' => $player_id,
            'player_name' => self::getActivePlayerName(),
            'card_name' => $card_name,
            'card_id' => $card_id
        ) );

    }

    */

    function selectEvidence($card_id) {
        self::checkAction("selectEvidence");
        $player_id = self::getActivePlayerId();
        $currentCard = $this->cards->getCard($card_id);

        // Should not happen; also anti-cheat
        if ($currentCard['location'] != "evidence_display") {
            throw new BgaUserException(self::_("Card is not on display. Press F5 in case of problems."));
        }

        // TODO: implement rules
        $case_card_ids = array_pluck($this->getPlayerCaseCards($player_id), 'id');
        $evidenceIsUseful = boolval(rand(0, 1));

        if ($evidenceIsUseful) {
            // Put card on discard
            $this->cards->insertCardOnExtremePosition($card_id, "discard", true);
            self::notifyAllPlayers(
                'evidenceSelected',
                clienttranslate('${player_name} found a useful evidence: ${card_name}'), array (
                    'i18n' => array ('card_name'),
                    'card_id' => $card_id,
                    'card_name' => $this->cardBasis[$currentCard['type_arg']]['name'],
                    'card_type' => $currentCard['type_arg'],
                    'useful' => true,
                    'player_id' => $player_id,
                    'player_name' => self::getActivePlayerName(),
                ));
            } else {
            // Put card in front of user to remember the "useless evidence".
            $this->cards->moveCard($card_id, "player_display", $player_id);
            self::notifyAllPlayers(
                'evidenceSelected',
                clienttranslate('${player_name} had no luck following evidence ${card_name}'), array (
                    'i18n' => array ('card_name'),
                    'useful' => false,
                    'card_id' => $card_id,
                    'card_name' => $this->cardBasis[$currentCard['type_arg']]['name'],
                    'card_type' => $currentCard['type_arg'],
                    'player_id' => $player_id,
                    'player_name' => self::getActivePlayerName(),
                ));
            }

        // Next player
        $this->gamestate->nextState('selectEvidence');
    }

//////////////////////////////////////////////////////////////////////////////
//////////// Game state arguments
////////////

    /*
        Here, you can create methods defined as "game state arguments" (see "args" property in states.inc.php).
        These methods function is to return some additional information that is specific to the current
        game state.
    */

    function argGiveCards() {
        return array ();
    }

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

    function st_gameTurn() {
        // Standard case
        // Draw a new card for evidence display

        // While auto-reshuffle is still a mystery to me, check here manually.
        if ($this->cards->countCardInLocation('deck') == 0
                && $this->cards->countCardInLocation('discard') > 0) {
            $this->cards->moveAllCardsInLocation('discard', 'deck');
            $this->cards->shuffle('deck');
        }

        if ($this->cards->countCardInLocation('deck') > 0) {
            $newCard = $this->cards->pickCardForLocation("deck", "evidence_display");
            self::notifyAllPlayers(
                'newEvidence', '',
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
                'newEvidence',
                clienttranslate('Evidence cards are exhausted and cannot be played anymore.'),
                array(
                    'deck_is_empty' => $this->cards->countCardInLocation('discard') == 0,
                    'discard_is_empty' => $this->cards->countCardInLocation('discard') == 0,
                ));
        }

        // Case: Player solved
        // Notify: {Player} solved ...

        $player_id = self::activeNextPlayer();
        self::giveExtraTime($player_id);
        $this->gamestate->nextState('next');
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
