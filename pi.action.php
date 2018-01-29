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
 * pi.action.php
 *
 * pi main action entry point
 *
 *
 * In this file, you are describing all the methods that can be called from your
 * user interface logic (javascript).
 *
 * If you define a method "myAction" here, then you can call it from your javascript code with:
 * this.ajaxcall( "/pi/pi/myAction.html", ...)
 *
 */

class action_pi extends APP_GameAction
{
    // Constructor: please do not modify
    public function __default()
    {
        if( self::isArg( 'notifwindow') )
        {
            $this->view = "common_notifwindow";
           $this->viewArgs['table'] = self::getArg( "table", AT_posint, true );
        }
        else
        {
            $this->view = "pi_pi";
            self::trace( "Complete reinitialization of board game" );
        }
    }

    private function numberlistToArray($s)
    {
        // Removing last ';' if exists
        if (substr($s, -1 ) == ';') $s = substr($s, 0, -1);
        if ($s == '') {
            return array();
        } else {
            return explode(';', $s);
        }
    }

    public function placeInvestigator() {
        self::setAjaxMode();
        $location_id = self::getArg("location_id", AT_posint, true);
        $this->game->placeInvestigator($location_id);
        self::ajaxResponse();
    }

    public function selectEvidence() {
        self::setAjaxMode();
        $card_id = self::getArg("id", AT_posint, true);
        $this->game->selectEvidence($card_id);
        self::ajaxResponse();
    }

    public function solveCase() {
        self::setAjaxMode();
        $tile_ids = self::getArg("tile_ids", AT_numberlist, true);
        $this->game->solveCase(self::numberlistToArray($tile_ids));
        self::ajaxResponse();
    }
}
