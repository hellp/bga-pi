<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * fabiantest implementation : © Fabian Neumann <fabian.neumann@posteo.de>
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

$this->constants = array(
  "EVIDENCE_DISPLAY_SIZE" => 9,
  "MINIGAMES" => 3,
  "DISCS_PER_PLAYER" => 3,
  "CUBES_PER_PLAYER" => 10,
);

$this->evidence_cards = array(
  1 => array('name' => clienttranslate('Murder'), 'nametr' => self::_('Murder')),
  2 => array('name' => clienttranslate('Shorty'), 'nametr' => self::_('Shorty')),
  3 => array('name' => clienttranslate('Doc'), 'nametr' => self::_('Doc')),
  4 => array('name' => clienttranslate('Rusty'), 'nametr' => self::_('Rusty')),
  5 => array('name' => clienttranslate('Bubbles'), 'nametr' => self::_('Bubbles')),
  6 => array('name' => clienttranslate('Frenchy'), 'nametr' => self::_('Frenchy')),
  7 => array('name' => clienttranslate('Maurice'), 'nametr' => self::_('Maurice')),
  8 => array('name' => clienttranslate('Main Street'), 'nametr' => self::_('Main Street')),
  9 => array('name' => clienttranslate('Waterfront'), 'nametr' => self::_('Waterfront')),
  10 => array('name' => clienttranslate('Mob Killing'), 'nametr' => self::_('Mob Killing')),
  11 => array('name' => clienttranslate('Pop'), 'nametr' => self::_('Pop')),
  12 => array('name' => clienttranslate('Dutch'), 'nametr' => self::_('Dutch')),
  13 => array('name' => clienttranslate('Knuckles'), 'nametr' => self::_('Knuckles')),
  14 => array('name' => clienttranslate('Pinky'), 'nametr' => self::_('Pinky')),
  15 => array('name' => clienttranslate('Queenie'), 'nametr' => self::_('Queenie')),
  16 => array('name' => clienttranslate('Earl'), 'nametr' => self::_('Earl')),
  17 => array('name' => clienttranslate('Ocean Drive'), 'nametr' => self::_('Ocean Drive')),
  18 => array('name' => clienttranslate('Union Square'), 'nametr' => self::_('Union Square')),
  19 => array('name' => clienttranslate('Little Italy'), 'nametr' => self::_('Little Italy')),
  20 => array('name' => clienttranslate('Bank Job'), 'nametr' => self::_('Bank Job')),
  21 => array('name' => clienttranslate('Blackmail'), 'nametr' => self::_('Blackmail')),
  22 => array('name' => clienttranslate('Police Corruption'), 'nametr' => self::_('Police Corruption')),
  23 => array('name' => clienttranslate('Forgery'), 'nametr' => self::_('Forgery')),
  24 => array('name' => clienttranslate('Downtown'), 'nametr' => self::_('Downtown')),
  25 => array('name' => clienttranslate('China Town'), 'nametr' => self::_('China Town')),
  26 => array('name' => clienttranslate('Forest Park'), 'nametr' => self::_('Forest Park')),
  27 => array('name' => clienttranslate('Central Station'), 'nametr' => self::_('Central Station')),
  28 => array('name' => clienttranslate('Rick’s Cafe'), 'nametr' => self::_('Rick’s Cafe')),
  29 => array('name' => clienttranslate('Kidnapping'), 'nametr' => self::_('Kidnapping')),
  30 => array('name' => clienttranslate('Jewellery Heist'), 'nametr' => self::_('Jewellery Heist')),
  31 => array('name' => clienttranslate('Smuggling'), 'nametr' => self::_('Smuggling')),
  32 => array('name' => clienttranslate('Protection Racket'), 'nametr' => self::_('Protection Racket')),
  33 => array('name' => clienttranslate('Roadhouse'), 'nametr' => self::_('Roadhouse')),
  34 => array('name' => clienttranslate('Trocadero'), 'nametr' => self::_('Trocadero')),
  35 => array('name' => clienttranslate('Skid Row'), 'nametr' => self::_('Skid Row')),
  36 => array('name' => clienttranslate('Lakeside'), 'nametr' => self::_('Lakeside')),
);
