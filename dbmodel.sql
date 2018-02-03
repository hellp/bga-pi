-- ------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- P.I. implementation: © Fabian Neumann <fabian.neumann@posteo.de>
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-- -----

-- dbmodel.sql

-- This is the file where you are describing the database schema of your game
-- Basically, you just have to export from PhpMyAdmin your table structure and copy/paste
-- this export here.
-- Note that the database itself and the standard tables ("global", "stats", "gamelog" and "player") are
-- already created and must not be created here

-- Note: The database schema is created from this file when the game starts. If you modify this file,
--       you have to restart a game to see your changes in database.

-- TODO: do we need `player_penalty`?? For the tie-breaker we keep track of it
-- in `player_score_aux`. For the UI we have the position of the token.
ALTER TABLE `player` ADD `player_penalty` tinyint(1) NOT NULL DEFAULT 0;

-- To calculate the winnable points for players solving later then others.
ALTER TABLE `player` ADD `player_solved_in_round` tinyint(1) UNSIGNED;


CREATE TABLE IF NOT EXISTS `card` (
  `card_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `card_type` varchar(16) NOT NULL,
  `card_type_arg` int(11) NOT NULL,
  `card_location` varchar(16) NOT NULL,
  `card_location_arg` int(11) NOT NULL,
  PRIMARY KEY (`card_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;



CREATE TABLE IF NOT EXISTS `token` (
  `token_key` varchar(32) NOT NULL,
  `token_location` varchar(32) NOT NULL,
  `token_state` int(10),
  PRIMARY KEY (`token_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
