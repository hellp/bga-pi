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
 * stats.inc.php
 *
 * pi game statistics description
 *
 */

/*
    In this file, you are describing game statistics, that will be displayed at the end of the
    game.
    
    !! After modifying this file, you must use "Reload  statistics configuration" in BGA Studio backoffice
    ("Control Panel" / "Manage Game" / "Your Game")
    
    There are 2 types of statistics:
    _ table statistics, that are not associated to a specific player (ie: 1 value for each game).
    _ player statistics, that are associated to each players (ie: 1 value for each player in the game).

    Statistics types can be "int" for integer, "float" for floating point values, and "bool" for boolean
    
    Once you defined your statistics there, you can start using "initStat", "setStat" and "incStat" method
    in your game logic, using statistics names defined below.
    
    !! It is not a good idea to modify this file when a game is running !!

    If your game is already public on BGA, please read the following before any change:
    http://en.doc.boardgamearena.com/Post-release_phase#Changes_that_breaks_the_games_in_progress
    
    Notes:
    * Statistic index is the reference used in setStat/incStat/initStat PHP method
    * Statistic index must contains alphanumerical characters and no space. Example: 'turn_played'
    * Statistics IDs must be >=10
    * Two table statistics can't share the same ID, two player statistics can't share the same ID
    * A table statistic can have the same ID than a player statistics
    * Statistics ID is the reference used by BGA website. If you change the ID, you lost all historical statistic data. Do NOT re-use an ID of a deleted statistic
    * Statistic name is the English description of the statistic as shown to players
    
*/

$stats_type = array(

    // Statistics global to table
    "table" => array(

        "turns_number" => array("id"=> 10,
            "name" => totranslate("Number of turns"),
            "type" => "int"),

        "rounds_1" => array("id"=> 20,
            "name" => totranslate("Number of rounds (1. mini-game)"),
            "type" => "int"),
        "rounds_2" => array("id"=> 21,
            "name" => totranslate("Number of rounds (2. mini-game)"),
            "type" => "int"),
        "rounds_3" => array("id"=> 22,
            "name" => totranslate("Number of rounds (3. mini-game)"),
            "type" => "int"),

    ),
    
    // Statistics existing for each player
    "player" => array(

        "turns_number" => array("id"=> 10,
            "name" => totranslate("Number of turns"),
            "type" => "int"),

        "investigators_used_1" => array("id"=> 20,
            "name" => totranslate("Investigators used (1. mini-game)"),
            "type" => "int"),
        "investigators_used_2" => array("id"=> 21,
            "name" => totranslate("Investigators used (2. mini-game)"),
            "type" => "int"),
        "investigators_used_3" => array("id"=> 22,
            "name" => totranslate("Investigators used (3. mini-game)"),
            "type" => "int"),

        "cards_taken_1" => array("id"=> 30,
            "name" => totranslate("Cards taken (1. mini-game)"),
            "type" => "int"),
        "cards_taken_2" => array("id"=> 31,
            "name" => totranslate("Cards taken (2. mini-game)"),
            "type" => "int"),
        "cards_taken_3" => array("id"=> 32,
            "name" => totranslate("Cards taken (3. mini-game)"),
            "type" => "int"),

        "penalty_1" => array("id"=> 40,
            "name" => totranslate("Penalty points (1. mini-game)"),
            "type" => "int"),
        "penalty_2" => array("id"=> 41,
            "name" => totranslate("Penalty points (2. mini-game)"),
            "type" => "int"),
        "penalty_3" => array("id"=> 42,
            "name" => totranslate("Penalty points (3. mini-game)"),
            "type" => "int"),

        // Achievable VPs are dependent on player count, so this is not too
        // expressive.
        "vp_1" => array("id"=> 50,
            "name" => totranslate("VP scored, ignoring penalty (1. mini-game)"),
            "type" => "int"),
        "vp_2" => array("id"=> 51,
            "name" => totranslate("VP scored, ignoring penalty (2. mini-game)"),
            "type" => "int"),
        "vp_3" => array("id"=> 52,
            "name" => totranslate("VP scored, ignoring penalty (3. mini-game)"),
            "type" => "int"),

        // *How* the player plays (strategy?)
        "avg_investigator_neighborhood" => array("id"=> 80,
            "name" => totranslate("Avg. neighborhood size of investigator placements (3-6; 4.143 means 'no preference')"),
            "type" => "float"),
        "neighbor_case_cards_taken" => array("id"=> 81,
            "name" => totranslate("Cards taken that are part of the neighbor's case solution"),
            "type" => "int"),

        // How quick the player can solve...
        "solved_minigames" => array("id"=> 90,
            "name" => totranslate("Solved mini-games"),
            "type" => "int"),
        "avg_cubes_to_solve" => array("id"=> 91,
            "name" => totranslate("Cubes on board before able to solve"),
            "type" => "float"),
        "avg_discs_to_solve" => array("id"=> 92,
            "name" => totranslate("Discs on board before able to solve"),
            "type" => "float"),

    )
);
