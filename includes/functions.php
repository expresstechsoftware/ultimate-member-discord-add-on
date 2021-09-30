<?php
/*
Common functions
*/

// function to get formated redirect url
function ets_get_ultimatemember_discord_formated_discord_redirect_url() {
	$parsed = parse_url( $url, PHP_URL_QUERY );
	if ( $parsed === null ) {
		return $url .= '?via=discord';
	} else {
		if ( stristr( $url, 'via=discord' ) !== false ) {
			return $url;
		} else {
			return $url .= '&via=discord';
		}
	}
}


