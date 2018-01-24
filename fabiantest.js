/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * fabiantest implementation: © Fabian Neumann <fabian.neumann@posteo.de>
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
            this.CARD_WIDTH = 102;
            this.CARD_HEIGHT = 156;
            this.TILE_HEIGHT = 60;
            this.TILE_WIDTH = 60;
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
            var CARD_ITEM_MARGIN = 3;
            var CARD_ITEMS_PER_ROW = 9;
            var TILE_ITEMS_PER_ROW = 7;

            // Set up player hand, i.e. the secret case cards of their left neighbor
            this.playerHand = new ebg.stock();
            this.playerHand.create(this, $('myhand'), this.CARD_WIDTH, this.CARD_HEIGHT);
            this.playerHand.setSelectionMode(0); // no selection possible
            this.playerHand.image_items_per_row = CARD_ITEMS_PER_ROW;
            this.playerHand.item_margin = CARD_ITEM_MARGIN;
            
            // The stocks where evidence cards can live: display, discard, player displays
            this.evidenceDisplay = new ebg.stock();
            this.evidenceDisplay.setSelectionMode(1); // max 1 card can be selected
            this.evidenceDisplay.create(this, $('evidence'), this.CARD_WIDTH, this.CARD_HEIGHT);
            this.evidenceDisplay.image_items_per_row = CARD_ITEMS_PER_ROW;
            this.evidenceDisplay.item_margin = CARD_ITEM_MARGIN;
            this.evidenceDiscard = new ebg.stock();
            this.evidenceDiscard.setSelectionMode(0); // no selection possible
            this.evidenceDiscard.create(this, $('evidence_discard'), this.CARD_WIDTH, this.CARD_HEIGHT);
            this.evidenceDiscard.image_items_per_row = CARD_ITEMS_PER_ROW;
            this.evidenceDiscard.setOverlap(.01, 0); // basically on top of eachother
            this.evidenceDiscard.item_margin = 0;
            this.addTooltip('evidence_discard', _("Discard pile"), _('View discarded cards.'));
            // TODO: onclick expand the discard pile, so it can be inspected

            this.playerDisplays = {};
            for (var player_id in gamedatas.players) {
                var pdstock = new ebg.stock();
                this.playerDisplays[player_id] = pdstock;
                var node_id = (player_id == this.player_id) ? 'myevidence' : 'playerdisplay_' + player_id;
                pdstock.setSelectionMode(0); // no selection possible
                pdstock.create(this, $(node_id), this.CARD_WIDTH, this.CARD_HEIGHT);
                pdstock.setOverlap(66, 0); // basically on top of eachother
                pdstock.image_items_per_row = CARD_ITEMS_PER_ROW;
                pdstock.item_margin = CARD_ITEM_MARGIN;
            }
            this.playerDisplay = this.playerDisplays[this.player_id];  // shortcut for current player

            // Create cards types:
            for (var i = 1; i <= 36; i++) {
                var pos_in_img = i - 1;  // it's zero-based
                // weight is 0 for all as they have no inherent weight
                this.playerHand.addItemType(i + 36, 0, g_gamethemeurl + 'img/casecards.jpg', pos_in_img);
                this.evidenceDisplay.addItemType(i, 0, g_gamethemeurl + 'img/evidencecards.jpg', pos_in_img);
                this.evidenceDiscard.addItemType(i, 0, g_gamethemeurl + 'img/evidencecards.jpg', pos_in_img);
                for (var player_id in gamedatas.players) {
                    this.playerDisplays[player_id].addItemType(i, 0, g_gamethemeurl + 'img/evidencecards.jpg', pos_in_img);
                }
            }

            // Cards in player's hand
            for (i in this.gamedatas.hand) {
                var card = this.gamedatas.hand[i];
                this.playerHand.addToStockWithId(card.type_arg, card.id);
            }

            // Put evidence cards on the table
            for (i in this.gamedatas.evidence_display) {
                var card = this.gamedatas.evidence_display[i];
                this.evidenceDisplay.addToStockWithId(card.type_arg, card.id);
                this.addTooltip('evidence_item_' + card.id, _(this.gamedatas.cardinfos[card.type_arg].name), _('Follow this evidence…'));
            }
            for (i in this.gamedatas.evidence_discard) {
                var card = this.gamedatas.evidence_discard[i];
                this.evidenceDiscard.addToStockWithId(card.type_arg, card.id);
            }
            for (i in this.gamedatas.player_display_cards) {
                // All cards have same location 'player_display' with `location_arg` being the player_id
                var card = this.gamedatas.player_display_cards[i];
                this.playerDisplays[card.location_arg].addToStockWithId(card.type_arg, card.id);
            }

            console.log(this.gamedatas.tiles);
            // for( var id in gamedatas.intersections )
            // {
            //     var intersection = gamedatas.intersections[id];

            // Set up tiles
            this.tiles = new ebg.stock();
            this.tiles.setSelectionMode(0); // Initially not selectable; later during solve action yes.
            this.tiles.create(this, $('tilestock'), this.TILE_WIDTH, this.TILE_HEIGHT);
            this.tiles.setOverlap(0.01, 0); // basically on top of eachother
            this.tiles.image_items_per_row = TILE_ITEMS_PER_ROW;
            this.tiles.item_margin = 0;
            // Create cards types:
            for (var i = 1; i <= 28; i++) {
                var pos_in_img = i - 1;  // it's zero-based
                // weight is 0 for all as they have no inherent weight
                this.tiles.addItemType(i, 0, g_gamethemeurl + 'img/tiles-front.jpg', pos_in_img);
                // for (var player_id in gamedatas.players) {
                //     this.playerDisplays[player_id].addItemType(i, 0, g_gamethemeurl + 'img/evidencecards.jpg', pos_in_img);
                // }
            }

            for (i in this.gamedatas.tiles) {
                var tile = this.gamedatas.tiles[i];
                this.tiles.addToStockWithId(tile.type_arg, tile.id);
                dojo.place($(this.tiles.getItemDivId(tile.id)), $('locslot_' + tile.location_arg));
                // this.slideToObject($('tile_' + tile.id), $('locslot_' + tile.location_arg)).play();
                
                // dojo.place(
                //     this.format_block('jstpl_tile', {tile: tile, name: this.gamedatas.tileinfos[tile.type_arg].name}),
                //     $('board'));
                // dojo.place(
                //     this.format_block('jstpl_tile', {tile: tile, name: this.gamedatas.tileinfos[tile.type_arg].name}),
                //     $('board'));
                // this.slideToObject( $('tile_' + tile.id), $('locslot_' + tile.location_arg) ).play();
            }
            
            // var slide = this.slideToObject( $( 'stone_' + notif.args.coord_x + '_' + notif.args.coord_y ), $( 'intersection_' + notif.args.coord_x + '_' + notif.args.coord_y ), 1000 );
            //     var x_pix = this.getXPixelCoordinates(intersection.coord_x);
            //     var y_pix = this.getYPixelCoordinates(intersection.coord_y);
                

            //     if (intersection.stone_color != null) {
            //         // This intersection is taken, it shouldn't appear as clickable anymore
            //         dojo.removeClass( 'intersection_' + intersection.coord_x + '_' + intersection.coord_y, 'clickable' );
            //     }
            // } 


            // // Place it on the player panel
            // this.placeOnObject( $( 'stone_' + notif.args.coord_x + '_' + notif.args.coord_y ), $( 'player_board_' + notif.args.player_id ) );

            // // Animate a slide from the player panel to the intersection
            // dojo.style( 'stone_' + notif.args.coord_x + '_' + notif.args.coord_y, 'zIndex', 1 );
            // var slide = this.slideToObject( $( 'stone_' + notif.args.coord_x + '_' + notif.args.coord_y ), $( 'intersection_' + notif.args.coord_x + '_' + notif.args.coord_y ), 1000 );
            // dojo.connect( slide, 'onEnd', this, dojo.hitch( this, function() {
            //             // At the end of the slide, update the intersection 
            //             dojo.removeClass( 'intersection_' + notif.args.coord_x + '_' + notif.args.coord_y, 'no_stone' );
            //             dojo.addClass( 'intersection_' + notif.args.coord_x + '_' + notif.args.coord_y, 'stone_'  + notif.args.color );
            //             dojo.removeClass( 'intersection_' + notif.args.coord_x + '_' + notif.args.coord_y, 'clickable' );
        			
            //             // We can now destroy the stone since it is now visible through the change in style of the intersection
            //             dojo.destroy( 'stone_' + notif.args.coord_x + '_' + notif.args.coord_y );
       	    // }));
            // slide.play();



            // Connect user actions
            dojo.connect(this.evidenceDisplay, 'onChangeSelection', this, 'onEvidenceDisplaySelectionChanged');
            
            // "Static" quick help tooltips
            this.addTooltip('myhand-wrap', _("TOP SECRET! Only you can see these."), '');
            this.addTooltip('myevidence-wrap', _("These cards remind you which evidence is unrelated to your case. They are visible to everybody."), '');

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
        onUpdateActionButtons: function(stateName, args)
        {
            if (this.isCurrentPlayerActive()) {
                switch (stateName) {
                    case "playerTurn":
                        // TODO: if player.hasInvestigatorsLeft ... else show
                        // inactive button that simply spits a warning if
                        // clicked
                        this.addActionButton(
                            'btn_send_investigator',
                            dojo.string.substitute(_('Send investigator (${n} left)'), {n: 99}),
                            'onSendInvestigatorClicked');
                        this.addActionButton(
                            'btn_solve_case',
                            _('Solve case'),
                            'onSolveCaseClicked');
                        break;
                    case "client_playerPicksSolution":
                        // Add Cancel button for the 'player picks solution' client state
                        if (this.on_client_state && !$('button_cancel')) {
                            this.tiles.setSelectionMode(2); // put into own function; check on each selection: no more than 3, correct type etc
                            this.addActionButton(
                                'button_confirm',
                                _('Confirm'),
                                'onConfirmCaseSelection');
                            this.addActionButton(
                                'button_cancel',
                                _('Cancel'),
                                dojo.hitch(this, function() {this.restoreServerGameState();}),
                                null, null, 'gray');
                        }
                        break;
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

        onEvidenceDisplaySelectionChanged: function() {
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

        onConfirmCaseSelection: function () {
            // TODO
        },

        onSendInvestigatorClicked: function () {
            // TODO
            // Alert: "Click on a location to send your investigator (X left)."
            console.log('sending investigator')
        },
        
        onSolveCaseClicked: function () {
            // TODO
            // Alert: "Click the correct location, crime, and suspect to solve your case..."
            console.log('trying to solve the case')
            this.setClientState(
                "client_playerPicksSolution", {
                    descriptionmyturn: _("Solve Case: ${you} must select the correct location, crime, and suspect…"),
                });
        },

        ///////////////////////////////////////////////////
        //// Reaction to cometD notifications

        /**
            setupNotifications:

            In this method, you associate each of your game notifications with
            your local method to handle it.

            Note: game notification names correspond to "notifyAllPlayers" and
            "notifyPlayer" calls in your fabiantest.game.php file.
        */
        setupNotifications: function()
        {
            dojo.subscribe('evidenceSelected', this, "notif_evidenceSelected");
            this.notifqueue.setSynchronous('evidenceSelected', 500);

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
            if (notif.args.card_id) {
                this.evidenceDisplay.addToStockWithId(notif.args.card_type, notif.args.card_id);
            }
            if (notif.args.discard_is_empty) {
                this.evidenceDiscard.removeAll();
            }
        },
        
        notif_evidenceSelected: function(notif)
        {
            // Useless evidences goes to player display; valueable evidence to discard.
            var targetStock = (notif.args.useful) ? this.evidenceDiscard : this.playerDisplays[notif.args.player_id];
            targetStock.addToStockWithId(notif.args.card_type, notif.args.card_id, 'evidence');
            this.evidenceDisplay.removeFromStockById(notif.args.card_id);
        },
   });
});
