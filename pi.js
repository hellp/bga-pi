/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * P.I. implementation: © Fabian Neumann <fabian.neumann@posteo.de>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * pi.js
 *
 * pi user interface script
 *
 * In this file, you are describing the logic of your user interface, in Javascript language.
 *
 */

define([
    "dojo",
    "dojo/_base/declare",
    "ebg/core/gamegui",
    "ebg/counter",
    "ebg/stock"
],
function (dojo, declare) {
    return declare("bgagame.pi", ebg.core.gamegui, {
        constructor: function(){
            // Here, you can init the global variables of your user interface
            this.CARD_WIDTH = 102;
            this.CARD_HEIGHT = 156;
            this.TILE_HEIGHT = 64;
            this.TILE_WIDTH = 64;
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

            // Set up player boards
            for (var player_id in gamedatas.players) {
                // Gray out players who have already solved in this minigame.
                var player = gamedatas.players[player_id];
                if (gamedatas.players[player_id].solved_in_round != null) {
                    this.disablePlayerPanel(player_id);
                }
                dojo.place(
                    this.format_block('jstpl_player_board', player),
                    $('player_board_' + player_id));
                if (player.is_startplayer == 1) {
                    dojo.addClass('sp_marker_' + player_id, 'visible');
                }
            }
            this.addTooltipToClass('sp_marker', _("Start player of the current mini-game"), '');

            // Set up player hand, i.e. the secret case cards of their left neighbor
            this.playerHand = new ebg.stock();
            this.playerHand.create(this, $('myhand'), this.CARD_WIDTH, this.CARD_HEIGHT);
            this.playerHand.setSelectionMode(0); // no selection possible
            this.playerHand.image_items_per_row = CARD_ITEMS_PER_ROW;
            this.playerHand.item_margin = CARD_ITEM_MARGIN;
            this.playerHand.onItemCreate = dojo.hitch(this, 'setupCaseCard');

            // The stocks where evidence cards can live: display, discard, player displays
            this.evidenceDisplay = new ebg.stock();
            this.evidenceDisplay.setSelectionMode(1); // max 1 card can be selected
            this.evidenceDisplay.create(this, $('evidence'), this.CARD_WIDTH, this.CARD_HEIGHT);
            this.evidenceDisplay.image_items_per_row = CARD_ITEMS_PER_ROW;
            this.evidenceDisplay.item_margin = CARD_ITEM_MARGIN;
            this.evidenceDisplay.onItemCreate = dojo.hitch(this, 'setupEvidenceDisplayCard');
            this.evidenceDiscard = new ebg.stock();
            this.evidenceDiscard.setSelectionMode(0); // no selection possible
            this.evidenceDiscard.create(this, $('evidence_discard'), this.CARD_WIDTH, this.CARD_HEIGHT);
            this.evidenceDiscard.image_items_per_row = CARD_ITEMS_PER_ROW;
            this.evidenceDiscard.setOverlap(.01, 0); // basically on top of eachother
            this.evidenceDiscard.item_margin = 0;
            this.addTooltip('evidence_discard', _("Discard pile"), '');

            this.playerDisplays = {};
            for (var player_id in gamedatas.players) {
                var pdstock = new ebg.stock();
                this.playerDisplays[player_id] = pdstock;
                var node_id = (player_id == this.player_id) ? 'myevidence' : 'playerdisplay_' + player_id;
                pdstock.setSelectionMode(0); // no selection possible
                pdstock.create(this, $(node_id), this.CARD_WIDTH, this.CARD_HEIGHT);
                pdstock.setOverlap(50, 0);
                pdstock.image_items_per_row = CARD_ITEMS_PER_ROW;
                pdstock.item_margin = CARD_ITEM_MARGIN;
                pdstock.onItemCreate = dojo.hitch(this, 'setupEvidenceCard');
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

            // Create tiles
            this.tiles = new ebg.stock();
            this.tiles.setSelectionMode(0); // Initially not selectable; later during solve action yes.
            this.tiles.setSelectionAppearance('class');
            this.tiles.create(this, $('tilestock'), this.TILE_WIDTH, this.TILE_HEIGHT);
            this.tiles.setOverlap(0.01, 0); // basically on top of eachother
            this.tiles.image_items_per_row = TILE_ITEMS_PER_ROW;
            this.tiles.item_margin = 0;
            this.tiles.onItemCreate = dojo.hitch(this, 'setupTile');
            for (var i = 1; i <= 42; i++) {
                var pos_in_img = i - 1;  // it's zero-based
                // weight is 0 for all as they have no inherent weight
                this.tiles.addItemType(i, 0, g_gamethemeurl + 'img/tiles_64_2x.jpg', pos_in_img);
            }

            // Place all the created stuff on the table.
            this.placePlayerHand(this.gamedatas.hand);
            this.placeEvidenceCards(
                this.gamedatas.evidence_display,
                this.gamedatas.evidence_discard,
                this.gamedatas.player_display_cards,
            );
            this.placeTiles(this.gamedatas.tiles);

            // Sort tokens: investigators need to be placed before cubes/discs.
            var tokens = this.gamedatas.tokens;
            tokens.sort(function (a, b) { return b.key.startsWith('pi_') - a.key.startsWith('pi_') });
            this.placeTokens(tokens);

            this.updateCounters(this.gamedatas.counters);

            // Connect user actions
            dojo.connect(this.evidenceDisplay, 'onChangeSelection', this, 'onEvidenceDisplaySelectionChanged');

            // "Static" quick help tooltips
            this.addTooltip('casecards_help', _("The card cards of your left neighbour. TOP SECRET! Only you can see these."), '');
            this.addTooltip('myevidence_help', _("These cards remind you which evidence is unrelated to your case. They are visible to everybody."), '');

            // Setup game notifications to handle (see "setupNotifications" method below)
            this.setupNotifications();
        },

        setupCaseCard: function(card_div, card_type_id, card_id) {
            this.addTooltip(card_div.id, _(this.gamedatas.cardinfos[card_type_id].name), '');
        },

        setupEvidenceCard: function(card_div, card_type_id, card_id) {
            dojo.place('<div class="cardname">' + _(this.gamedatas.cardinfos[card_type_id].name) + '</div>', card_div.id);
            this.addTooltip(card_div.id, _(this.gamedatas.cardinfos[card_type_id].name), '');
        },

        setupEvidenceDisplayCard: function(card_div, card_type_id, card_id) {
            this.addTooltip(card_div.id, _(this.gamedatas.cardinfos[card_type_id].name), _('Follow this evidence…'));
        },

        setupTile: function (card_div, card_type_id, card_id) {
            // The first 6 tiles are "NO XXX" tiles. Mark them as such.
            if (card_type_id <= 6) {
                dojo.addClass(card_id, "no_x_tile");
            }
            // Delete the background from our "fake tiles" for the locations.
            if (card_type_id >= 29) {
                dojo.addClass(card_id, "fake_tile");
                dojo.setStyle(card_id, "background", "none");
            }
            var name = _(this.gamedatas.tileinfos[card_type_id].name);
            if (!name.startsWith('NO ')) {
                dojo.place('<div class="tilename">' + _(name) + '</div>', card_div.id);
            }
            this.addTooltip(card_div.id, _(name), '');
        },

        ///////////////////////////////////////////////////
        //// Game & client states

        // onEnteringState: this method is called each time we are entering into a new game state.
        //                  You can use this method to perform some user interface changes at this moment.
        //
        onEnteringState: function(stateName, args)
        {
            this.stateEventHandles = this.stateEventHandles || {};
            var ehdls = this.stateEventHandles[stateName] = [];

            switch (stateName)
            {
                case 'startMinigame':
                    args = args.args;
                    // Re-enable player panels that may have been disabled in previous mini-game.
                    this.enableAllPlayerPanels();

                    // Reset active player marker
                    dojo.query(".sp_marker").removeClass('visible');
                    for (var player_id in args.players) {
                        var player = args.players[player_id];
                        dojo.toggleClass('sp_marker_' + player_id, 'visible', player.is_startplayer == 1);
                    }

                    // Counters
                    this.updateCounters(args.counters);

                    // Cards + tiles
                    this.placeEvidenceCards(args.evidence_display, [], []);
                    this.placeTiles(args.tiles);

                    // Tokens
                    this.placeTokens(args.tokens, null, 100);

                    // Player hand
                    this.placePlayerHand(args._private.hand);
                    break;
                case 'client_playerPicksSolution':
                    // Enable tile selection and wire up validation callback
                    this.tiles.setSelectionMode(2);
                    ehdls.push(dojo.connect(this.tiles, 'onChangeSelection', this, 'onCaseSelectionChanged'));

                    // Highlight clickable tiles
                    dojo.query('.locslot .stockitem').addClass('active_slot');

                    // Clear UI
                    this.hideCardDisplay();
                    break;
                case 'client_playerPlacesInvestigator':
                    dojo.query('.agentarea').addClass('active_slot');
                    this.addEventToClass("agentarea", 'onclick', 'onAgentAreaClicked');
                    for (var i=1; i<=14; i++) {
                        this.addTooltip(
                            'agentarea_' + i,
                            '',
                            dojo.string.substitute(_("Send investigator to ${location_name}."), {
                                location_name: this.gamedatas.locationinfos[i].name
                            })
                        );
                    }
                    // Clear UI
                    this.hideCardDisplay();
                    break;
            }
        },

        // onLeavingState: this method is called each time we are leaving a game state.
        //                 You can use this method to perform some user interface changes at this moment.
        //
        onLeavingState: function(stateName)
        {
            switch (stateName)
            {
                case 'client_playerPicksSolution':
                    this.tiles.setSelectionMode(0);
                    this.showCardDisplay();
                    break;
                case 'client_playerPlacesInvestigator':
                    this.showCardDisplay();
                    for (var i=1; i<=14; i++) { this.removeTooltip('agentarea_' + i) }
                    break;
            }

            // Remove clickable indicator
            dojo.query('.active_slot').removeClass('active_slot');

            // Disconnect from events
            (this.stateEventHandles[stateName] || []).forEach(function (handle, idx) {
                dojo.disconnect(handle);
            });;
        },

        // onUpdateActionButtons: in this method you can manage "action buttons" that are displayed in the
        //                        action status bar (ie: the HTML links in the status bar).
        //
        onUpdateActionButtons: function(stateName, args)
        {
            if (this.isCurrentPlayerActive()) {
                switch (stateName) {
                    case "playerTurn":
                        if (args.remainingInvestigators != 0) {
                            this.addActionButton(
                                'btn_place_investigator',
                                dojo.string.substitute(
                                    _('Place an investigator (${nbr} left)'),
                                    {nbr: args.remainingInvestigators}),
                                'onPlaceInvestigatorClicked');
                        }
                        this.addActionButton(
                            'btn_solve_case',
                            _('Solve case'),
                            'onSolveCaseClicked');
                        break;
                    case "client_playerPicksSolution":
                        // Add Cancel button for the 'player picks solution' client state
                        if (this.on_client_state && !$('button_cancel')) {
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
                    case "client_playerPlacesInvestigator":
                        if (this.on_client_state && !$('button_cancel')) {
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

        checkActivePlayer: function() {
            if (!this.isCurrentPlayerActive()) {
                this.showMessage(__("lang_mainsite", "This is not your turn"), "error");
                return false;
            }
            return true;
        },

        isActiveSlot: function(id) {
            if (!dojo.hasClass(id, 'active_slot')) { return false; }
            return true;
        },

        checkActiveSlot: function(id) {
            if (!this.isActiveSlot(id)) {
                this.showMoveUnauthorized();
                return false;
            }
            return true;
        },

        checkActivePlayerAndSlot: function(id) {
            if (!this.checkActivePlayer()) { return false; }
            if (!this.checkActiveSlot(id)) { return false; }
            return true;
        },

        hideCardDisplay: function () {
            // hide cards, so user doesn't accidentally click there
            dojo.fx.wipeOut({node: $('carddisplay')}).play();
        },

        showCardDisplay: function () {
            dojo.fx.wipeIn({node: $('carddisplay')}).play();
            // "bug fix": if window was resized during this cards are in wrong
            // positions; reset.
            this.evidenceDisplay.resetItemsPosition();
            this.evidenceDiscard.resetItemsPosition();
        },

        /**
         * Place evidence cards (all public cards). Used during setup and on newMinigame.
         */
        placeEvidenceCards: function(display, discard, player_display_cards) {
            // Put evidence cards on the table
            this.evidenceDisplay.removeAll();
            for (i in display) {
                var card = display[i];
                this.evidenceDisplay.addToStockWithId(card.type_arg, card.id);
            }
            this.evidenceDiscard.removeAll();
            for (i in discard) {
                var card = discard[i];
                this.evidenceDiscard.addToStockWithId(card.type_arg, card.id);
            }
            for (var player_id in this.gamedatas.players) {
                this.playerDisplays[player_id].removeAll();
            }
            for (i in player_display_cards) {
                // All cards have same location 'player_display' with `location_arg` being the player_id
                var card = player_display_cards[i];
                this.playerDisplays[card.location_arg].addToStockWithId(card.type_arg, card.id);
            }
        },

        /**
         * Place cards in player's (private) hand.
         */
        placePlayerHand: function (hand) {
            this.playerHand.removeAll();
            for (i in hand) {
                var card = hand[i];
                this.playerHand.addToStockWithId(card.type_arg, card.id);
            }
        },

        /**
         * Place tiles on the table.
         */
        placeTiles: function (tiles) {
            for (i in tiles) {
                var tile = tiles[i];
                this.tiles.addToStockWithId(tile.type_arg, tile.id);
                dojo.place($(this.tiles.getItemDivId(tile.id)), $('locslot_' + tile.location_arg));
            }
        },

        /**
         * Place token on the table.
         */
        placeToken: function (token, target_id, delay) {
            var key = token.key;
            var keyparts = key.split('_');
            var ttype = keyparts[0]; // cube, disc
            var color = keyparts[1];
            var duration = 500;

            // If no explicit target given; use the location from the DB.
            target_id = target_id || token.location;

            var placeOfftable = false;
            // Two standard locations that mean: "remove from UI": offtable, box
            if (target_id == 'box' || target_id == 'offtable') {
                placeOfftable = true;
            } else if (!$(target_id)) {
                // Also if target_id cannot be found: warn, but treat as 'offtable'.
                console.warn('Node \'' + target_id + '\' not found.' )
                placeOfftable = true;
            }

            if (placeOfftable) {
                if ($(key)) this.fadeOutAndDestroy(key, duration, delay);
                return;
            }

            // Cubes/discs for 'agentarea_X' actually go onto the corresponding investigator there.
            if ((ttype == 'cube' || ttype == 'disc') && target_id.startsWith('agentarea')) {
                target_id = dojo.query('#' + target_id + ' .investigator_' + color)[0].id;
            }

            // Cubes for 'locslot_X' go actually to 'locslot_X_cubes'
            if (ttype == 'cube' && target_id.startsWith('locslot_')) {
                target_id += '_cubes';
            }

            var $el = $(key);
            if (!$el) {
                var html = this.format_block('jstpl_token_' + ttype, {key: key, color: color});
                dojo.place(html, target_id);
            } else {
                // If it already exists *at* the target_id, then do nothing
                if ($el.parentNode.id == target_id) return;

                // Else: move it, destroy it, put it into the target
                var html = $el.outerHTML;
                delay = delay || 500;
                this.slideToObjectAndDestroy(key, target_id, duration, delay);
                window.setTimeout(dojo.hitch(this, function () {
                    dojo.place(html, target_id);
                }), delay + duration);
            }
        },

        /**
         * Place tokens on the table.
         */
        placeTokens: function (tokens, target_id, delay) {
            delay = delay || 500;
            for (i in tokens) { this.placeToken(tokens[i], target_id, i * delay) }
        },

        /**
         * Validate the case selection.
         *
         * Opts has (optional) keys:
         *
         * - `strict`: validate, i.e. check that *exactly* 3 tiles are chosen
         * - `clicked_id`: the id of the stock item that was just checked, and
         *   wants to be validated (and potentially unselected)
         */
        validateCaseSelection: function(opts) {
            var opts = opts || {};
            var selectedTiles = this.tiles.getSelectedItems() || [];
            tileinfos = this.gamedatas.tileinfos;
            // Validation
            var hasSelected = {'crime': false, 'suspect': false, 'location': false};
            for (var i in selectedTiles) {
                var tile = selectedTiles[i];
                var tile_type_arg = parseInt(tile.type, 10);

                // Tiles 1-6 are "NO CRIME/SUSPECT" tiles.
                if (parseInt(tile_type_arg, 10) <= 6) {
                    this.showMessage(_("You cannot select NO CRIME or NO SUSPECT tiles."), "error");
                    if (opts.clicked_id) this.tiles.unselectItem(opts.clicked_id);
                    return false;
                }

                if (hasSelected[tileinfos[tile_type_arg].tiletype]) {
                    this.showMessage(_("You must not select more than one of each type (crime/location/suspect)."), "error");
                    if (opts.clicked_id) this.tiles.unselectItem(opts.clicked_id);
                    return false;
                }
                hasSelected[tileinfos[tile_type_arg].tiletype] = true;
            }
            if (opts.strict && selectedTiles.length != 3) {
                this.showMessage(_("You must select exactly 3 tiles."), "error");
                return false;
            }
            return true;
        },

        ///////////////////////////////////////////////////
        //// Player's action

        /*

            Here, you are defining methods to handle player's action (ex: results of mouse click on
            game objects).

            Most of the time, these methods:
            _ check the action is possible at this game state.
            _ make a call to the game server

        */

        onAgentAreaClicked: function(e) {
            var id = e.currentTarget.id;
            if (!this.checkActivePlayerAndSlot(id)) return;

            var location_id = e.currentTarget.id.split('_')[1];
            var action = 'placeInvestigator';
            if (this.checkAction(action, true)) {
                this.ajaxcall("/" + this.game_name + "/" + this.game_name + "/" + action + ".html", {
                    location_id: location_id,
                    lock : true
                }, this, function(result) {
                }, function(is_error) {
                });
            }
        },

        onEvidenceDisplaySelectionChanged: function() {
            var items = this.evidenceDisplay.getSelectedItems();
            if (items.length > 0) {
                var action = 'selectEvidence';
                if (this.checkAction(action, true)) {
                    // Can play a card
                    var card_id = items[0].id;
                    this.ajaxcall("/" + this.game_name + "/" + this.game_name + "/" + action + ".html", {
                        id: card_id,
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

        onCaseSelectionChanged: function (control_name, id) {
            this.validateCaseSelection({clicked_id: id, strict: false});
        },

        onConfirmCaseSelection: function () {
            if (!this.validateCaseSelection({strict: true})) return;
            // Send the solving request to the server
            var action = 'solveCase';
            if (this.checkAction(action, true)) {
                this.ajaxcall("/" + this.game_name + "/" + this.game_name + "/" + action + ".html", {
                    tile_ids : this.tiles.getSelectedItems().map(function (tile) { return tile.id }).join(';'),
                    lock : true
                }, this, function(result) {
                }, function(is_error) {
                });
            } else {
            }
        },

        onPlaceInvestigatorClicked: function () {
            this.setClientState(
                "client_playerPlacesInvestigator", {
                    descriptionmyturn: _("Place investigator: ${you} must select a location…"),
                });
        },

        onSolveCaseClicked: function () {
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
            "notifyPlayer" calls in your pi.game.php file.
        */
        setupNotifications: function()
        {
            dojo.subscribe('evidenceClose', this, "notif_evidenceClose");
            this.notifqueue.setSynchronous('evidenceClose', 800);
            dojo.subscribe('evidenceCorrect', this, "notif_evidenceCorrect");
            this.notifqueue.setSynchronous('evidenceCorrect', 800);
            dojo.subscribe('evidenceWrong', this, "notif_evidenceWrong");
            this.notifqueue.setSynchronous('evidenceWrong', 500);

            dojo.subscribe('evidenceExhausted', this, "notif_evidenceExhausted");
            dojo.subscribe('evidenceReplenished', this, "notif_evidenceReplenished");
            dojo.subscribe('newScores', this, "notif_newScores");

            dojo.subscribe('playerFailed', this, function () {});
            this.notifqueue.setSynchronous('playerFailed', 1000);

            dojo.subscribe('placeToken', this, "notif_placeToken");
            this.notifqueue.setSynchronous('placeToken', 300);
            dojo.subscribe('placeTokens', this, "notif_placeTokens");
            this.notifqueue.setSynchronous('placeTokens', 300);

            dojo.subscribe('playerSolved', this, "notif_playerSolved");
        },

        notif_evidenceExhausted: function(notif) {
            this.evidenceDiscard.removeAll();
        },

        notif_evidenceReplenished: function(notif) {
            this.evidenceDisplay.addToStockWithId(notif.args.card_type, notif.args.card_id);
            if (notif.args.discard_is_empty) {
                this.evidenceDiscard.removeAll();
            }
        },

        notif_evidenceClose: function(notif) {
            // Move card to discard
            this.evidenceDiscard.addToStockWithId(notif.args.card_type, notif.args.card_id, 'evidence');
            this.evidenceDisplay.removeFromStockById(notif.args.card_id);
        },

        notif_evidenceCorrect: function(notif) {
            // Move card to discard
            this.evidenceDiscard.addToStockWithId(notif.args.card_type, notif.args.card_id, 'evidence');
            this.evidenceDisplay.removeFromStockById(notif.args.card_id);
        },

        notif_evidenceWrong: function(notif) {
            // Move card to player display
            this.playerDisplays[notif.args.player_id].addToStockWithId(notif.args.card_type, notif.args.card_id, 'evidence');
            this.evidenceDisplay.removeFromStockById(notif.args.card_id);
        },

        notif_newScores: function(notif) {
            for (var player_id in notif.args.scores) {
                this.scoreCtrl[player_id].toValue(notif.args.scores[player_id]);
            }
        },

        notif_placeToken: function(notif) {
            this.placeToken(notif.args.token, notif.args.target_id);
            if (notif.args.counters) this.updateCounters(notif.args.counters);
        },

        notif_placeTokens: function(notif) {
            this.placeTokens(notif.args.tokens, notif.args.target_id);
            if (notif.args.counters) this.updateCounters(notif.args.counters);
        },

        notif_playerSolved: function(notif) {
            this.disablePlayerPanel(notif.args.player_id);
        },
    });
});
