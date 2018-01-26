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
  28 => array('name' => clienttranslate('Rick’s Café'),
             'nametr' => self::_('Rick’s Café'),
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
  33 => array('name' => clienttranslate('Road House'),
             'nametr' => self::_('Road House'),
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


// The case cards; data-wise a copy of the evidence cards
for ($i=1; $i<=36; $i++) {
  $this->cardBasis[36 + $i] = $this->cardBasis[$i];
}


$this->tiles = array(
  1 => array('name' => clienttranslate('NO CRIME'), 'nametr' => self::_('NO CRIME'), 'tiletype' => 'crime'),
  2 => array('name' => clienttranslate('NO CRIME'), 'nametr' => self::_('NO CRIME'), 'tiletype' => 'crime'),
  3 => array('name' => clienttranslate('NO CRIME'), 'nametr' => self::_('NO CRIME'), 'tiletype' => 'crime'),
  4 => array('name' => clienttranslate('NO CRIME'), 'nametr' => self::_('NO CRIME'), 'tiletype' => 'crime'),
  5 => array('name' => clienttranslate('NO SUSPECT'), 'nametr' => self::_('NO SUSPECT'), 'tiletype' => 'suspect'),
  6 => array('name' => clienttranslate('NO SUSPECT'), 'nametr' => self::_('NO SUSPECT'), 'tiletype' => 'suspect'),
  7 => array('name' => clienttranslate('Forgery'), 'nametr' => self::_('Forgery'), 'tiletype' => 'crime'),

  8 => array('name' => clienttranslate('Jewellery Heist'), 'nametr' => self::_('Jewellery Heist'), 'tiletype' => 'crime'),
  9 => array('name' => clienttranslate('Kidnapping'), 'nametr' => self::_('Kidnapping'), 'tiletype' => 'crime'),
  10 => array('name' => clienttranslate('Police Corruption'), 'nametr' => self::_('Police Corruption'), 'tiletype' => 'crime'),
  11 => array('name' => clienttranslate('Mob Killing'), 'nametr' => self::_('Mob Killing'), 'tiletype' => 'crime'),
  12 => array('name' => clienttranslate('Protection Racket'), 'nametr' => self::_('Protection Racket'), 'tiletype' => 'crime'),
  13 => array('name' => clienttranslate('Murder'), 'nametr' => self::_('Murder'), 'tiletype' => 'crime'),
  14 => array('name' => clienttranslate('Blackmail'), 'nametr' => self::_('Blackmail'), 'tiletype' => 'crime'),

  15 => array('name' => clienttranslate('Smuggling'), 'nametr' => self::_('Smuggling'), 'tiletype' => 'crime'),
  16 => array('name' => clienttranslate('Bank Job'), 'nametr' => self::_('Bank Job'), 'tiletype' => 'crime'),
  17 => array('name' => clienttranslate('Rusty'), 'nametr' => self::_('Rusty'), 'tiletype' => 'suspect'),
  18 => array('name' => clienttranslate('Dutch'), 'nametr' => self::_('Dutch'), 'tiletype' => 'suspect'),
  19 => array('name' => clienttranslate('Doc'), 'nametr' => self::_('Doc'), 'tiletype' => 'suspect'),
  20 => array('name' => clienttranslate('Earl'), 'nametr' => self::_('Earl'), 'tiletype' => 'suspect'),
  21 => array('name' => clienttranslate('Pop'), 'nametr' => self::_('Pop'), 'tiletype' => 'suspect'),

  22 => array('name' => clienttranslate('Knuckles'), 'nametr' => self::_('Knuckles'), 'tiletype' => 'suspect'),
  23 => array('name' => clienttranslate('Queenie'), 'nametr' => self::_('Queenie'), 'tiletype' => 'suspect'),
  24 => array('name' => clienttranslate('Bubbles'), 'nametr' => self::_('Bubbles'), 'tiletype' => 'suspect'),
  25 => array('name' => clienttranslate('Maurice'), 'nametr' => self::_('Maurice'), 'tiletype' => 'suspect'),
  26 => array('name' => clienttranslate('Pinky'), 'nametr' => self::_('Pinky'), 'tiletype' => 'suspect'),
  27 => array('name' => clienttranslate('Frenchy'), 'nametr' => self::_('Frenchy'), 'tiletype' => 'suspect'),
  28 => array('name' => clienttranslate('Shorty'), 'nametr' => self::_('Shorty'), 'tiletype' => 'suspect'),

  // FAKE TILES for the fixed on-board locations. But having them being handled
  // the same way as normal tiles makes it easier for us. In the UI these will
  // be invisible DIVs for the most part, except when highlighted for the Case
  // solving action.
  29 => array('name' => clienttranslate('Lakeside'), 'nametr' => self::_('Lakeside'), 'tiletype' => 'location'),
  30 => array('name' => clienttranslate('Forest Park'), 'nametr' => self::_('Forest Park'), 'tiletype' => 'location'),
  31 => array('name' => clienttranslate('Little Italy'), 'nametr' => self::_('Little Italy'), 'tiletype' => 'location'),
  32 => array('name' => clienttranslate('Trocadero'), 'nametr' => self::_('Trocadero'), 'tiletype' => 'location'),
  33 => array('name' => clienttranslate('Ocean Drive'), 'nametr' => self::_('Ocean Drive'), 'tiletype' => 'location'),
  34 => array('name' => clienttranslate('China Town'), 'nametr' => self::_('China Town'), 'tiletype' => 'location'),
  35 => array('name' => clienttranslate('Central Station'), 'nametr' => self::_('Central Station'), 'tiletype' => 'location'),
  36 => array('name' => clienttranslate('Main Street'), 'nametr' => self::_('Main Street'), 'tiletype' => 'location'),
  37 => array('name' => clienttranslate('Road House'), 'nametr' => self::_('Road House'), 'tiletype' => 'location'),
  38 => array('name' => clienttranslate('Union Square'), 'nametr' => self::_('Union Square'), 'tiletype' => 'location'),
  39 => array('name' => clienttranslate('Downtown'), 'nametr' => self::_('Downtown'), 'tiletype' => 'location'),
  40 => array('name' => clienttranslate('Rick’s Café'), 'nametr' => self::_('Rick’s Café'), 'tiletype' => 'location'),
  41 => array('name' => clienttranslate('Waterfront'), 'nametr' => self::_('Waterfront'), 'tiletype' => 'location'),
  42 => array('name' => clienttranslate('Skid Row'), 'nametr' => self::_('Skid Row'), 'tiletype' => 'location'),
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

if (!defined('LOC_LAKESIDE')) { // guard since this included multiple times
  define("LOC_LAKESIDE", 1);
  define("LOC_FORESTPARK", 2);
  define('LOC_LITTLEITALY', 3);
  define('LOC_TROCADERO', 4);
  define('LOC_OCEANDRIVE', 5);
  define('LOC_CHINATOWN', 6);
  define('LOC_CENTRALSTATION', 7);
  define('LOC_MAINSTREET', 8);
  define('LOC_ROADHOUSE', 9);
  define('LOC_UNIONSQUARE', 10);
  define('LOC_DOWNTOWN', 11);
  define('LOC_RICKSCAFE', 12);
  define('LOC_WATERFRONT', 13);
  define('LOC_SKIDROW', 14);
}

$this->locations = array(
  LOC_LAKESIDE => array(
    'strid' => 'lakeside',
    'neighbors' => array(LOC_FORESTPARK, LOC_TROCADERO, LOC_LITTLEITALY),
    'coords' => array(4.2, 27.8, 0),
  ),
  LOC_FORESTPARK => array(
    'strid' => 'forestpark',
    'neighbors' => array(LOC_OCEANDRIVE, LOC_TROCADERO, LOC_LAKESIDE),
    'coords' => array(3.8, 51.9, 2.5),
  ),
  LOC_LITTLEITALY => array(
    'strid' => 'littleitaly',
    'neighbors' => array(LOC_LAKESIDE, LOC_TROCADERO, LOC_CENTRALSTATION, LOC_CHINATOWN),
    'coords' => array(23.5, 15.0, 2.0),
  ),
  LOC_TROCADERO => array(
    'strid' => 'trocadero',
    'neighbors' => array(LOC_LAKESIDE, LOC_FORESTPARK, LOC_OCEANDRIVE, LOC_MAINSTREET, LOC_CENTRALSTATION, LOC_LITTLEITALY),
    'coords' => array(23.2, 39.7, 0),
  ),
  LOC_OCEANDRIVE => array(
    'strid' => 'oceandrive',
    'neighbors' => array(LOC_FORESTPARK, LOC_ROADHOUSE, LOC_MAINSTREET, LOC_TROCADERO),
    'coords' => array(23.9, 64.2, 1.3),
  ),
  LOC_CHINATOWN => array(
    'strid' => 'chinatown',
    'neighbors' => array(LOC_LITTLEITALY, LOC_CENTRALSTATION, LOC_UNIONSQUARE),
    'coords' => array(43.2, 2.8, 0),
  ),
  LOC_CENTRALSTATION => array(
    'strid' => 'centralstation',
    'neighbors' => array(LOC_LITTLEITALY, LOC_TROCADERO, LOC_MAINSTREET, LOC_DOWNTOWN, LOC_UNIONSQUARE, LOC_CHINATOWN),
    'coords' => array(43.2, 27.5, 1.15),
  ),
  LOC_MAINSTREET => array(
    'strid' => 'mainstreet',
    'neighbors' => array(LOC_TROCADERO, LOC_OCEANDRIVE, LOC_ROADHOUSE, LOC_RICKSCAFE, LOC_DOWNTOWN, LOC_CENTRALSTATION),
    'coords' => array(42.5, 51.8, 0),
  ),
  LOC_ROADHOUSE => array(
    'strid' => 'roadhouse',
    'neighbors' => array(LOC_OCEANDRIVE, LOC_RICKSCAFE, LOC_MAINSTREET),
    'coords' => array(43.4, 76.2, 1.7),
  ),
  LOC_UNIONSQUARE => array(
    'strid' => 'unionsquare',
    'neighbors' => array(LOC_CHINATOWN, LOC_CENTRALSTATION, LOC_DOWNTOWN, LOC_WATERFRONT),
    'coords' => array(63.0, 15.3, -1.5),
  ),
  LOC_DOWNTOWN => array(
    'strid' => 'downtown',
    'neighbors' => array(LOC_CENTRALSTATION, LOC_MAINSTREET, LOC_RICKSCAFE, LOC_SKIDROW, LOC_WATERFRONT, LOC_UNIONSQUARE),
    'coords' => array(63.1, 39.4, 0),
  ),
  LOC_RICKSCAFE => array(
    'strid' => 'rickscafe',
    'neighbors' => array(LOC_MAINSTREET, LOC_ROADHOUSE, LOC_SKIDROW, LOC_DOWNTOWN),
    'coords' => array(63.1, 64.1, 0),
  ),
  LOC_WATERFRONT => array(
    'strid' => 'waterfront',
    'neighbors' => array(LOC_UNIONSQUARE, LOC_DOWNTOWN, LOC_SKIDROW),
    'coords' => array(82.3, 27.4, 1.5),
  ),
  LOC_SKIDROW => array(
    'strid' => 'skidrow',
    'neighbors' => array(LOC_DOWNTOWN, LOC_RICKSCAFE, LOC_WATERFRONT),
    'coords' => array(82.5, 51.7, 0),
  ),
);

// For each location create 3 slots; in the UI these will be anchors to orient
// our tiles on.
foreach ($this->locations as $loc_id => $loc) {
  list($top, $left, $angle) = $loc['coords'];
  $this->locations[$loc_id]['slots'] = array(
    'crime' => array('id' => $loc_id * 100 + 1, 'strid' => $loc['strid'] . '_crime'),
    'location' => array('id' => $loc_id * 100 + 2, 'strid' => $loc['strid'] . '_location'),
    'suspect' => array('id' => $loc_id * 100 + 3, 'strid' => $loc['strid'] . '_suspect'),
  );
}
