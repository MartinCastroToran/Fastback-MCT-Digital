<?php
	function my_child_theme_enqueue_styles() {
	$parent_style = 'astra-style'; // Reemplazar con el identificador principal de estilos de tu tema padre
	wp_enqueue_style( $parent_style, get_template_directory_uri() .'/style.css' );
	wp_enqueue_style( 'child-style',
		get_stylesheet_directory_uri() . '/style.css', 
		array( $parent_style),
		wp_get_theme()->get('Version')
	);
	}
	add_action( 'wp_enqueue_scripts', 'my_child_theme_enqueue_styles' );

	// CARGA DE SCRIPTS - FASTBACK
	function mct_enqueue_fastback_scripts() {
		// Verificar si estamos en el single del CPT 'autos'
		if ( is_singular('autos') ) {
			
			// Encolar el script
			// handle: 'mct-financiamiento'
			// src: busca en la carpeta del child theme
			// deps: array() (sin dependencias, es JS nativo)
			// ver: time() (usar fecha para evitar caché en desarrollo, luego poner '1.0')
			// in_footer: true (importante para que cargue al final)
			
			wp_enqueue_script( 
				'mct-financiamiento', 
				get_stylesheet_directory_uri() . '/js/funciones_single.js', 
				array(), 
				time(), 
				true 
			);

			// Opcional: Si en el futuro necesitas pasar variables de PHP a JS (como la Tasa de Interés)
			// puedes usar wp_localize_script así:
			/*
			wp_localize_script( 'mct-financiamiento', 'mctData', array(
				'tna' => 0.52,
				'ajax_url' => admin_url( 'admin-ajax.php' )
			));
			*/
		}
	}
	add_action( 'wp_enqueue_scripts', 'mct_enqueue_fastback_scripts' );
?>