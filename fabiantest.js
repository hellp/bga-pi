/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * fabiantest implementation : © Fabian Neumann <fabian.neumann@posteo.de>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * fabiantest.js
 *
 * fabiantest user interface script
 *
 * In this file, you are describing the logic of your user interface, in Javascript language.
 *
 */

define([
    "dojo","dojo/_base/declare",
    "ebg/core/gamegui",
    "ebg/counter",
    "ebg/stock"
],
function (dojo, declare) {
    return declare("bgagame.fabiantest", ebg.core.gamegui, {
        constructor: function(){
            // Here, you can init the global variables of your user interface
            this.cardwidth = 102;
            this.cardheight = 156;
        },

        /*
            setup:

            This method must set up the game user interface according to current game situation specified
            in parameters.

            The method is called each time the game interface is displayed to a player, ie:
            _ when the game starts
            _ when a player refreshes the game page (F5)

            "gamedatas" argument contains all datas retrieved by your "getAllDatas" PHP method.
        */

        setup: function(gamedatas)
        {
            console.log( "Starting game setup" );

            // Setting up player boards
            for( var player_id in gamedatas.players )
            {
                var player = gamedatas.players[player_id];

                // TODO: Setting up players boards if needed
            }

            // TODO: Set up your game interface here, according to "gamedatas"

            // Player hand; the secret case cards for your left neighbor This
            // hand is "not actionable", i.e. read-only, during a whole round.
            // It could probably be handled in the template only, without JS.
            // this.playerHand = new ebg.stock(); // new stock object for hand
            // this.playerHand.create( this, $('myhand'), this.cardwidth, this.cardheight );
            // this.playerHand.image_items_per_row = 12;

            // The evidence card display
            this.evidenceDisplay = new ebg.stock();
            this.evidenceDisplay.setSelectionMode(1); // max 1 card can be selected
            this.evidenceDisplay.create( this, $('evidence'), this.cardwidth, this.cardheight );
            this.evidenceDisplay.image_items_per_row = 9;

            this.evidenceDiscard = new ebg.stock();
            this.evidenceDiscard.setSelectionMode(0); // max 1 card can be selected
            this.evidenceDiscard.setOverlap(2, 0);
            this.evidenceDiscard.create( this, $('evidence_discard'), this.cardwidth, this.cardheight );
            this.evidenceDiscard.image_items_per_row = 9;

            // Create cards types:
            for (var value = 1; value <= 36; value++) {
                var pos_in_img = value - 1;  // it's zero-based
                // weight is 0 for all as they have no inherent value
                this.evidenceDisplay.addItemType(value, 0, g_gamethemeurl + 'img/evidencecards.jpg', pos_in_img);
                this.evidenceDiscard.addItemType(value, 0, g_gamethemeurl + 'img/evidencecards.jpg', pos_in_img);
            }

            // // Cards in player's hand
            // for ( var i in this.gamedatas.hand) {
            //     var card = this.gamedatas.hand[i];
            //     var color = card.type;
            //     var value = card.type_arg;
            //     this.playerHand.addToStockWithId(this.getCardUniqueId(color, value), card.id);
            // }

            // Put evidence cards on the table
            for (i in this.gamedatas.evidence_display) {
                var card = this.gamedatas.evidence_display[i];
                this.evidenceDisplay.addToStockWithId(card.type_arg, card.id);
                this.addTooltip('evidence_item_' + card.id, _(this.gamedatas.card_basis[card.type_arg].name), _('Follow this evidence…'));
            }
            for (i in this.gamedatas.evidence_discard) {
                var card = this.gamedatas.evidence_discard[i];
                this.evidenceDiscard.addToStockWithId(card.type_arg, card.id);
            }

            dojo.connect(this.evidenceDisplay, 'onChangeSelection', this, 'onEvidenceDisplaySelectionChanged');

            // Setup game notifications to handle (see "setupNotifications" method below)
            this.setupNotifications();
        },


        ///////////////////////////////////////////////////
        //// Game & client states

        // onEnteringState: this method is called each time we are entering into a new game state.
        //                  You can use this method to perform some user interface changes at this moment.
        //
        onEnteringState: function( stateName, args )
        {
            console.log( 'Entering state: '+stateName );

            switch( stateName )
            {

            /* Example:

            case 'myGameState':

                // Show some HTML block at this game state
                dojo.style( 'my_html_block_id', 'display', 'block' );

                break;
           */

            case 'dummmy':
                break;
            }
        },

        // onLeavingState: this method is called each time we are leaving a game state.
        //                 You can use this method to perform some user interface changes at this moment.
        //
        onLeavingState: function( stateName )
        {
            console.log('Leaving state: ' + stateName);

            switch( stateName )
            {

            /* Example:

            case 'myGameState':

                // Hide the HTML block we are displaying only during this game state
                dojo.style( 'my_html_block_id', 'display', 'none' );

                break;
           */


            case 'dummmy':
                break;
            }
        },

        // onUpdateActionButtons: in this method you can manage "action buttons" that are displayed in the
        //                        action status bar (ie: the HTML links in the status bar).
        //
        onUpdateActionButtons: function( stateName, args )
        {
            console.log( 'onUpdateActionButtons: '+stateName );

            if( this.isCurrentPlayerActive() )
            {
                switch( stateName )
                {
/*
                 Example:

                 case 'myGameState':

                    // Add 3 action buttons in the action status bar:

                    this.addActionButton( 'button_1_id', _('Button 1 label'), 'onMyMethodToCall1' );
                    this.addActionButton( 'button_2_id', _('Button 2 label'), 'onMyMethodToCall2' );
                    this.addActionButton( 'button_3_id', _('Button 3 label'), 'onMyMethodToCall3' );
                    break;
*/
                }
            }
        },

        ///////////////////////////////////////////////////
        //// Utility methods

        /*

            Here, you can defines some utility methods that you can use everywhere in your javascript
            script.

        */


        ///////////////////////////////////////////////////
        //// Player's action

        /*

            Here, you are defining methods to handle player's action (ex: results of mouse click on
            game objects).

            Most of the time, these methods:
            _ check the action is possible at this game state.
            _ make a call to the game server

        */

        onEvidenceDisplaySelectionChanged : function() {
            var items = this.evidenceDisplay.getSelectedItems();
            if (items.length > 0) {
                var action = 'selectEvidence';
                if (this.checkAction(action, true)) {
                    // Can play a card
                    var card_id = items[0].id;
                    this.ajaxcall("/" + this.game_name + "/" + this.game_name + "/" + action + ".html", {
                        id : card_id,
                        lock : true
                    }, this, function(result) {
                    }, function(is_error) {
                    });
                    this.evidenceDisplay.unselectAll();
                } else {
                    this.evidenceDisplay.unselectAll();
                }
            }
        },

        /* Example:

        
        onMyMethodToCall1: function( evt )
        {
            console.log( 'onMyMethodToCall1' );

            // Preventing default browser reaction
            dojo.stopEvent( evt );

            // Check that this action is possible (see "possibleactions" in states.inc.php)
            if( ! this.checkAction( 'myAction' ) )
            {   return; }

            this.ajaxcall( "/fabiantest/fabiantest/myAction.html", {
                                                                    lock: true,
                                                                    myArgument1: arg1,
                                                                    myArgument2: arg2,
                                                                    ...
                                                                 },
                         this, function( result ) {

                            // What to do after the server call if it succeeded
                            // (most of the time: nothing)

                         }, function( is_error) {

                            // What to do after the server call in anyway (success or failure)
                            // (most of the time: nothing)

                         } );
        },

        */


        ///////////////////////////////////////////////////
        //// Reaction to cometD notifications

        /*
            setupNotifications:

            In this method, you associate each of your game notifications with your local method to handle it.

            Note: game notification names correspond to "notifyAllPlayers" and "notifyPlayer" calls in
                  your fabiantest.game.php file.

        */
        setupNotifications: function()
        {
            console.log( 'notifications subscriptions setup' );

            dojo.subscribe('evidenceSelected', this, "notif_evidenceSelected");
            dojo.subscribe('newEvidence', this, "notif_newEvidence");

            // TODO: here, associate your game notifications with local methods

            // Example 1: standard notification handling
            // dojo.subscribe( 'cardPlayed', this, "notif_cardPlayed" );

            // Example 2: standard notification handling + tell the user interface to wait
            //            during 3 seconds after calling the method in order to let the players
            //            see what is happening in the game.
            // dojo.subscribe( 'cardPlayed', this, "notif_cardPlayed" );
            // this.notifqueue.setSynchronous( 'cardPlayed', 3000 );
            //
        },

        notif_newEvidence: function(notif)
        {
            // Note: notif.args contains the arguments specified during you "notifyAllPlayers" / "notifyPlayer" PHP call
            console.log("NEW EV", notif);
            if (notif.args.card_id) {
                this.evidenceDisplay.addToStockWithId(notif.args.card_type, notif.args.card_id);
            }
            if (notif.args.discard_is_empty) {
                this.evidenceDiscard.removeAll();
            }
        },
        
        notif_evidenceSelected: function(notif)
        {
            if (notif.args.useful) {
                this.evidenceDiscard.addToStockWithId(notif.args.card_type, notif.args.card_id, 'evidence');
                // this.evidenceDisplay.removeFromStockById(notif.args.card_id, 'evidence_discard');
            } else {
                // TODO: move to player display
            }
            this.evidenceDisplay.removeFromStockById(notif.args.card_id);
        },
   });
});
