<?php

function xerar_lista_atributos( string $attr ) {

	$attrarr  = [];
	$mode     = 0;
	$nome     = '';
	$uris     = html_permitido( 'uri' );
	$allowed_protocols = protocolos_permitidos();

	while ( strlen( $attr ) != 0 ) {

		$working = 0;

		switch ( $mode ) {

			case 0:

				if ( preg_match( '/^([_a-zA-Z][-_a-zA-Z0-9:.]*)/', $attr, $match ) ) {

					$nome = $match[1];
					$working  = 1;
					$mode     = 1;
					$attr     = preg_replace( '/^[_a-zA-Z][-_a-zA-Z0-9:.]*/', '', $attr );

				}

				break;

			case 1:

				if ( preg_match( '/^\s*=\s*/', $attr ) ) { // Equals sign.

					$working = 1;
					$mode    = 2;
					$attr    = preg_replace( '/^\s*=\s*/', '', $attr );
					break;

				}

				if ( preg_match( '/^\s+/', $attr ) ) { // Valueless.

					$working = 1;
					$mode    = 0;

					if ( false === array_key_exists( $nome, $attrarr ) ) {
						$attrarr[ $nome ] = $nome;
					}

					$attr = preg_replace( '/^\s+/', '', $attr );

				}

				break;

			case 2:

				if ( preg_match( '%^"([^"]*)"(\s+|/?$)%', $attr, $match ) ) {

					$valor = $match[1];

					if ( in_array( strtolower( $nome ), $uris, true ) ) {
						$valor = verificar_protocolo( $valor, $allowed_protocols );
					}

					if ( false === array_key_exists( $nome, $attrarr ) ) {
						$attrarr[ $nome ] = "$nome=\"$valor\"";
					}

					$working = 1;
					$mode    = 0;
					$attr    = preg_replace( '/^"[^"]*"(\s+|$)/', '', $attr );

					break;

				}

				if ( preg_match( "%^'([^']*)'(\s+|/?$)%", $attr, $match ) ) {

					$valor = $match[1];

					if ( in_array( strtolower( $nome ), $uris, true ) ) {
						$valor = verificar_protocolo( $valor, $allowed_protocols );
					}

					if ( false === array_key_exists( $nome, $attrarr ) ) {
						$attrarr[ $nome ] = "$nome=\"$valor\"";
					}

					$working = 1;
					$mode    = 0;
					$attr    = preg_replace( "/^'[^']*'(\s+|$)/", '', $attr );

					break;

				}

				if ( preg_match( "%^([^\s\"']+)(\s+|/?$)%", $attr, $match ) ) {

					$valor = $match[1];

					if ( in_array( strtolower( $nome ), $uris, true ) ) {
						$valor = verificar_protocolo( $valor, $allowed_protocols );
					}

					if ( false === array_key_exists( $nome, $attrarr ) ) {
						$attrarr[ $nome ] = "$nome=\"$valor\"";
					}

					$working = 1;
					$mode    = 0;
					$attr    = preg_replace( "%^[^\s\"']+(\s+|$)%", '', $attr );

				}

				break;

		}

		if ( 0 == $working ) { // Not well-formed, remove and try again.
			$attr = preg_replace( '/^("[^"]*("|$)|\'[^\']*(\'|$)|\S)*\s*/', '', $attr );
			$mode = 0;
		}

	}

	//Atributos sen valor (selected, disabled)
	if ( 1 == $mode && false === array_key_exists( $nome, $attrarr ) ) {
		$attrarr[ $nome ] = $nome;
	}

	return $attrarr;

}
