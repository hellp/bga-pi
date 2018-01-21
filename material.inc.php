<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * fabiantest implementation : © <Your name here> <Your email address here>
 * 
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * material.inc.php
 *
 * fabiantest game material description
 *
 * Here, you can describe the material of your game with PHP variables.
 *   
 * This file is loaded in your game logic class constructor, ie these variables
 * are available everywhere in your game logic code.
 *
 */

$this->evidence_cards = array(
  1 => array( 'name' => clienttranslate('Shorty'), 'nametr' => self::_('Shorty') ),
  2 => array( 'name' => clienttranslate('Queeny'), 'nametr' => self::_('Queeny') ),
  3 => array( 'name' => clienttranslate('Maurice'), 'nametr' => self::_('Maurice') ),
);

// TODO DUMMY
for ($i = 4; $i <= 36; $i++) {
  $this->evidence_cards[$i] = array( 'name' => clienttranslate("Evidence {$i}s"), 'nametr' => self::_("Evidence {$i}s") );
}
