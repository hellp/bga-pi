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
 * pi.view.php
 *
 * This is your "view" file.
 *
 * The method "build_page" below is called each time the game interface is displayed to a player, ie:
 * _ when the game starts
 * _ when a player refreshes the game page (F5)
 *
 * "build_page" method allows you to dynamically modify the HTML generated for the game interface. In
 * particular, you can set here the values of variables elements defined in pi_pi.tpl (elements
 * like {MY_VARIABLE_ELEMENT}), and insert HTML block elements (also defined in your HTML template file)
 *
 * Note: if the HTML of your game interface is always the same, you don't have to place anything here.
 *
 */

  require_once( APP_BASE_PATH."view/common/game.view.php" );

  class view_pi_pi extends game_view
  {
    function getGameName() {
        return "pi";
    }

    function build_page($viewArgs)
    {
        global $g_user;
        $template = self::getGameName() . "_" . self::getGameName();

        $current_player_id = $g_user->get_id();

        // Get players & players number
        $players = $this->game->loadPlayersBasicInfos();
        $players_nbr = count($players);
        $spectatorMode = !isset($players[$current_player_id]);

        /*********** Place your code below:  ************/

        // Display a translated version of "My hand" at the place of the variable in the template
        $this->tpl['MY_EVIDENCE_CARDS'] = self::_("Evidence cards I collected");
        $this->tpl['EVIDENCE_CARDS_OF_PLAYER'] = self::_("Evidence cards collected by");
        $this->tpl['CASE_CARDS_OF_PLAYER'] = self::_("Case cards of player");

        // Display player name to which my hand cards belong
        if (!$spectatorMode) {
            $left_neighbor = $this->game->getPlayerAfter($current_player_id);
            $this->tpl['LEFT_NEIGHBOR_NAME'] = $players[$left_neighbor]['player_name'];
            $this->tpl['LEFT_NEIGHBOR_COLOR'] = $players[$left_neighbor]['player_color'];
        }

        // The location containers and the location slots, where the tiles we be
        // player later (by JS).
        $this->page->begin_block($template, "locslot");
        $this->page->begin_block($template, "loccont");
        foreach ($this->game->locations as $loc_id => $loc) {
            $this->page->reset_subblocks("locslot");
            foreach ($loc['slots'] as $type => $slot) {
                $this->page->insert_block("locslot", array("ID" => $slot['id'], "TYPE" => $type));
            }
            $this->page->insert_block(
                "loccont",
                array("ID" => $loc_id,
                      "TOP" => ($loc['coords'][0] / 100) * $this->game->constants['BOARD_H'],
                      "LEFT" => ($loc['coords'][1] / 100) * $this->game->constants['BOARD_W'],
                      "ROTATION" => $loc['coords'][2])
            );
        }

        // this will inflate our player block with actual players data
        $this->page->begin_block($template, "player");
        foreach ($players as $player_id => $info) {
            if ($player_id == $current_player_id) continue;
            $this->page->insert_block(
                "player",
                array (
                    "PLAYER_ID" => $player_id,
                    "PLAYER_NAME" => $players[$player_id]['player_name'],
                    "PLAYER_COLOR" => $players[$player_id]['player_color'],
                )
            );
        }

        /*

        // Examples: set the value of some element defined in your tpl file like this: {MY_VARIABLE_ELEMENT}

        // Display a specific number / string
        $this->tpl['MY_VARIABLE_ELEMENT'] = $number_to_display;

        // Display a string to be translated in all languages:
        $this->tpl['MY_VARIABLE_ELEMENT'] = self::_("A string to be translated");

        // Display some HTML content of your own:
        $this->tpl['MY_VARIABLE_ELEMENT'] = self::raw( $some_html_code );

        */

        /*

        // Example: display a specific HTML block for each player in this game.
        // (note: the block is defined in your .tpl file like this:
        //      <!-- BEGIN myblock -->
        //          ... my HTML code ...
        //      <!-- END myblock -->


        $this->page->begin_block( "pi_pi", "myblock" );
        foreach( $players as $player )
        {
            $this->page->insert_block( "myblock", array(
                                                    "PLAYER_NAME" => $player['player_name'],
                                                    "SOME_VARIABLE" => $some_value
                                                    ...
                                                     ) );
        }

        */



        /*********** Do not change anything below this line  ************/
    }
  }


