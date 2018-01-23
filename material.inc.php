<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * fabiantest implementation: © Fabian Neumann <fabian.neumann@posteo.de>
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
  "EVIDENCE_DECK_SIZE" => 36,
  "EVIDENCE_DISPLAY_SIZE" => 9,
  "MINIGAMES" => 3,
  "DISCS_PER_PLAYER" => 3,
  "CUBES_PER_PLAYER" => 10,
  "BOARD_H" => 740,
  "BOARD_W" => 980,
);

// We use this as the basis to create all cards, evidence and base cards, but
// not all of the info is used in each deck. But as there is a 1:1 relation
// between cases and evidences, let's don't repeat ourselves.
$this->cardBasis = array(
  1 => array('name' => clienttranslate('Murder'),
             'nametr' => self::_('Murder'),
             'casetype' => 'crime',
            ),
  2 => array('name' => clienttranslate('Shorty'),
             'nametr' => self::_('Shorty'),
             'casetype' => 'suspect',
            ),
  3 => array('name' => clienttranslate('Doc'),
             'nametr' => self::_('Doc'),
             'casetype' => 'suspect',
            ),
  4 => array('name' => clienttranslate('Rusty'),
             'nametr' => self::_('Rusty'),
             'casetype' => 'suspect',
            ),
  5 => array('name' => clienttranslate('Bubbles'),
             'nametr' => self::_('Bubbles'),
             'casetype' => 'suspect',
            ),
  6 => array('name' => clienttranslate('Frenchy'),
             'nametr' => self::_('Frenchy'),
             'casetype' => 'suspect',
            ),
  7 => array('name' => clienttranslate('Maurice'),
             'nametr' => self::_('Maurice'),
             'casetype' => 'suspect',
            ),
  8 => array('name' => clienttranslate('Main Street'),
             'nametr' => self::_('Main Street'),
             'casetype' => 'location',
            ),
  9 => array('name' => clienttranslate('Waterfront'),
             'nametr' => self::_('Waterfront'),
             'casetype' => 'location',
            ),
  10 => array('name' => clienttranslate('Mob Killing'),
             'nametr' => self::_('Mob Killing'),
             'casetype' => 'crime',
            ),
  11 => array('name' => clienttranslate('Pop'),
             'nametr' => self::_('Pop'),
             'casetype' => 'suspect',
            ),
  12 => array('name' => clienttranslate('Dutch'),
             'nametr' => self::_('Dutch'),
             'casetype' => 'suspect',
            ),
  13 => array('name' => clienttranslate('Knuckles'),
             'nametr' => self::_('Knuckles'),
             'casetype' => 'suspect',
            ),
  14 => array('name' => clienttranslate('Pinky'),
             'nametr' => self::_('Pinky'),
             'casetype' => 'suspect',
            ),
  15 => array('name' => clienttranslate('Queenie'),
             'nametr' => self::_('Queenie'),
             'casetype' => 'suspect',
            ),
  16 => array('name' => clienttranslate('Earl'),
             'nametr' => self::_('Earl'),
             'casetype' => 'suspect',
            ),
  17 => array('name' => clienttranslate('Ocean Drive'),
             'nametr' => self::_('Ocean Drive'),
             'casetype' => 'location',
            ),
  18 => array('name' => clienttranslate('Union Square'),
             'nametr' => self::_('Union Square'),
             'casetype' => 'location',
            ),
  19 => array('name' => clienttranslate('Little Italy'),
             'nametr' => self::_('Little Italy'),
             'casetype' => 'location',
            ),
  20 => array('name' => clienttranslate('Bank Job'),
             'nametr' => self::_('Bank Job'),
             'casetype' => 'crime',
            ),
  21 => array('name' => clienttranslate('Blackmail'),
             'nametr' => self::_('Blackmail'),
             'casetype' => 'crime',
            ),
  22 => array('name' => clienttranslate('Police Corruption'),
             'nametr' => self::_('Police Corruption'),
             'casetype' => 'crime',
            ),
  23 => array('name' => clienttranslate('Forgery'),
             'nametr' => self::_('Forgery'),
             'casetype' => 'crime',
            ),
  24 => array('name' => clienttranslate('Downtown'),
             'nametr' => self::_('Downtown'),
             'casetype' => 'location',
            ),
  25 => array('name' => clienttranslate('China Town'),
             'nametr' => self::_('China Town'),
             'casetype' => 'location',
            ),
  26 => array('name' => clienttranslate('Forest Park'),
             'nametr' => self::_('Forest Park'),
             'casetype' => 'location',
            ),
  27 => array('name' => clienttranslate('Central Station'),
             'nametr' => self::_('Central Station'),
             'casetype' => 'location',
            ),
  28 => array('name' => clienttranslate('Rick’s Cafe'),
             'nametr' => self::_('Rick’s Cafe'),
             'casetype' => 'location',
            ),
  29 => array('name' => clienttranslate('Kidnapping'),
             'nametr' => self::_('Kidnapping'),
             'casetype' => 'crime',
            ),
  30 => array('name' => clienttranslate('Jewellery Heist'),
             'nametr' => self::_('Jewellery Heist'),
             'casetype' => 'crime',
            ),
  31 => array('name' => clienttranslate('Smuggling'),
             'nametr' => self::_('Smuggling'),
             'casetype' => 'crime',
            ),
  32 => array('name' => clienttranslate('Protection Racket'),
             'nametr' => self::_('Protection Racket'),
             'casetype' => 'crime',
            ),
  33 => array('name' => clienttranslate('Roadhouse'),
             'nametr' => self::_('Roadhouse'),
             'casetype' => 'location',
            ),
  34 => array('name' => clienttranslate('Trocadero'),
             'nametr' => self::_('Trocadero'),
             'casetype' => 'location',
            ),
  35 => array('name' => clienttranslate('Skid Row'),
             'nametr' => self::_('Skid Row'),
             'casetype' => 'location',
            ),
  36 => array('name' => clienttranslate('Lakeside'),
             'nametr' => self::_('Lakeside'),
             'casetype' => 'location',
            ),
);

// DUMMY FOR NOW
$this->tiles = array(
  1 => array('name' => clienttranslate('Murder'),
             'nametr' => self::_('Murder'),
             'tiletype' => 'crime',
            ),
  2 => array('name' => clienttranslate('Shorty'),
             'nametr' => self::_('Shorty'),
             'tiletype' => 'suspect',
            ),
  3 => array('name' => clienttranslate('NO CRIME'),
             'nametr' => self::_('NO CRIME'),
             'tiletype' => 'crime',
            ),
  4 => array('name' => clienttranslate('NO SUSPECT'),
             'nametr' => self::_('NO SUSPECT'),
             'tiletype' => 'suspect',
            ),
  5 => array('name' => clienttranslate('Smuggling'),
             'nametr' => self::_('Smuggling'),
             'tiletype' => 'crime',
            ),
  6 => array('name' => clienttranslate('Frenchy'),
             'nametr' => self::_('Frenchy'),
             'tiletype' => 'suspect',
            ),
);

/**
 * THE LOCATIONS
 *
 * Backend and frontend data.
 *
 * `sid`: string id, for easier overview here
 *
 * `coords`: The (top, left, rotation) coords (in percent) of the location slot
 * (middle); the crime and suspect slot locations can be calculated from this.
 *
 * `neighbors`: ids of adjacent locations
 *
 * slots: 1 => crime, 2 => suspect
 */
$this->locations = array(
  1 => array(
    'strid' => 'centralstation',
    'nametr' => self::_('Central Station'),
    'neighbors_by_strid' => array('littleitaly', 'trocadero', 'chinatown', 'mainstreet', 'unionsquare', 'downtown'),
    'coords' => array(50.0, 35.1, -10.5),
  ),
  2 => array(
    'strid' => 'mainstreet',
    'nametr' => self::_('Main Street'),
    'neighbors_by_strid' => array('trocadero', 'oceandrive', 'roadhouse', 'rickscafe', 'downtown', 'centralstation'),
    'coords' => array(49.4, 59.0, 0),
  ),
  3 => array(
    'strid' => 'rickscafe',
    'nametr' => self::_('Ricks Café'),
    // TODO
    'neighbors_by_strid' => array('trocadero', 'oceandrive', 'roadhouse', 'rickscafe', 'downtown', 'centralstation'),
    'coords' => array(69.2, 71.0, 0),
  ),
  4 => array(
    'strid' => 'forestpark',
    'nametr' => self::_('Forest Park'),
    // TODO
    'neighbors_by_strid' => array('trocadero', 'oceandrive', 'roadhouse', 'rickscafe', 'downtown', 'centralstation'),
    'coords' => array(12.0, 59.0, 2.5),
  ),
);

foreach ($this->locations as $loc_id => $loc) {
  list($top, $left, $angle) = $loc['coords'];
  $this->locations[$loc_id]['slots'] = array(
    'crime' => array(
      'id' => $loc_id * 100 + 1,
      'strid' => $loc['strid'] . '_crime',
      'coords' => array(calcY($this->constants['BOARD_H'] * ($top / 100), $angle, $this->constants['BOARD_H'] * 0.07),
      calcX($this->constants['BOARD_W'] * ($left / 100), $angle, $this->constants['BOARD_W'] * 0.07),
      $angle)),
    'suspect' => array(
      'id' => $loc_id * 100 + 3,
      'strid' => $loc['strid'] . '_suspect',
      'coords' => array(calcY($this->constants['BOARD_H'] * ($top / 100), $angle, $this->constants['BOARD_H'] * -0.07),
                        calcX($this->constants['BOARD_W'] * ($left / 100), $angle, $this->constants['BOARD_W'] * -0.07),
                        $angle)),
  );
}
