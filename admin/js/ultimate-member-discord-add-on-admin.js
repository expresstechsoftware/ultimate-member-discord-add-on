(function( $ ) {
	//'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */
       // console.log(etsUltimateMemberParams);
        if (etsUltimateMemberParams.is_admin) {
            
		$.ajax({
			type: "POST",
			dataType: "JSON",
			url: etsUltimateMemberParams.admin_ajax,
			data: { 'action': 'ets_ultimatemember_discord_load_discord_roles', 'ets_ultimatemember_discord_nonce': etsUltimateMemberParams.ets_ultimatemember_discord_nonce },
			beforeSend: function () {
				$(".ultimate-member-discord-roles .spinner").addClass("is-active");
				$(".initialtab.spinner").addClass("is-active");
                                
			},
			success: function (response) {
                            
                            //console.log(response);
                           // return;
				if (response != null && response.hasOwnProperty('code') && response.code == 50001 && response.message == 'Missing Access') {
					$(".btn-connect-to-bot").show();
				} else if (response == null || response.message == '401: Unauthorized' || response.hasOwnProperty('code') || response == 0) {
					$("#ultimatemember-connect-discord-bot").show().html("Error: Please check all details are correct").addClass('error-bk');
				} else {
					if ($('.ets-tabs button[data-identity="level-mapping"]').length) {
						$('.ets-tabs button[data-identity="level-mapping"]').show();
					}
					$("#ultimatemember-connect-discord-bot").show().html("Bot Connected <i class='fab fa-discord'></i>").addClass('not-active');

					var activeTab = localStorage.getItem('activeTab');
					if ($('.ets-tabs button[data-identity="level-mapping"]').length == 0 && activeTab == 'level-mapping') {
						$('.ets-tabs button[data-identity="settings"]').trigger('click');
					}
					$.each(response, function (key, val) {
                                            //console.log(val.name);
						var isbot = false;
						if (val.hasOwnProperty('tags')) {
							if (val.tags.hasOwnProperty('bot_id')) {
								isbot = true;
							}
						}
                                                //console.log(isbot);

						if (key != 'previous_mapping' && isbot == false && val.name != '@everyone') {
							$('.ultimate-member-discord-roles').append('<div class="makeMeDraggable" style="background-color:#'+val.color.toString(16)+'" data-pmpro_role_id="' + val.id + '" >' + val.name + '</div>');
							//$('#pmpro-defaultRole').append('<option value="' + val.id + '" >' + val.name + '</option>');
							//makeDrag($('.makeMeDraggable'));
						}
					});
					var defaultRole = $('#selected_default_role').val();
					if (defaultRole) {
						$('#pmpro-defaultRole option[value=' + defaultRole + ']').prop('selected', true);
					}

					if (response.previous_mapping) {
						var mapjson = response.previous_mapping;
					} else {
						var mapjson = localStorage.getItem('pmpro_mappingjson');
					}

					$("#pmpro_maaping_json_val").html(mapjson);
					$.each(JSON.parse(mapjson), function (key, val) {
						var arrayofkey = key.split('id_');
						$('*[data-pmpro_level_id="' + arrayofkey[1] + '"]').append($('*[data-pmpro_role_id="' + val + '"]')).attr('data-drop-pmpro_role_id', val).find('span').css({ 'order': '2' });
						if (jQuery('*[data-pmpro_level_id="' + arrayofkey[1] + '"]').find('.makeMeDraggable').length >= 1) {
							$('*[data-pmpro_level_id="' + arrayofkey[1] + '"]').droppable("destroy");
						}
						$('*[data-pmpro_role_id="' + val + '"]').css({ 'width': '100%', 'left': '0', 'top': '0', 'margin-bottom': '0px', 'order': '1' }).attr('data-pmpro_level_id', arrayofkey[1]);
					});
				}

			},
			error: function (response) {
                            console.log('erreur');
                            //return;
				//$("#ultimatemember-connect-discord-bot").show().html("Error: Please check all details are correct").addClass('error-bk');
				console.error(response);
                                
			},
			complete: function () {
				$(".ultimate-member-discord-roles .spinner").removeClass("is-active").css({ "float": "right" });
				$("#skeletabsTab1 .spinner").removeClass("is-active").css({ "float": "right", "display": "none" });
			}
		});
		/*Create droppable element*/
		function init() {
			$('.makeMeDroppable').droppable({
				drop: handleDropEvent,
				hoverClass: 'hoverActive',
			});
//			$('.pmpro-discord-roles-col').droppable({
//				drop: handlePreviousDropEvent,
//				hoverClass: 'hoverActive',
//			});
		}

		$(init);                

                /*Create draggable element*/
		function makeDrag(el) {
			// Pass me an object, and I will make it draggable
			el.draggable({
				revert: "invalid"
			});
		}
        }

})( jQuery );
