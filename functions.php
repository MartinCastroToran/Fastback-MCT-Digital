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
				get_stylesheet_directory_uri() . '/js/funciones.js', 
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

	// 1. AJAX para obtener Modelos según la Marca
	add_action('wp_ajax_get_modelos_auto', 'mct_get_modelos_auto');
	add_action('wp_ajax_nopriv_get_modelos_auto', 'mct_get_modelos_auto');
	function mct_get_modelos_auto() {
		global $wpdb;
		$marca = sanitize_text_field($_POST['marca']);
		
		// CAMBIO AQUI: cotizador_autos
		$resultados = $wpdb->get_results( $wpdb->prepare(
			"SELECT DISTINCT modelo FROM cotizador_autos WHERE marca = %s ORDER BY modelo ASC", 
			$marca
		));

		wp_send_json($resultados);
	}

	// 2. AJAX para obtener Versiones según Marca y Modelo
	add_action('wp_ajax_get_versiones_auto', 'mct_get_versiones_auto');
	add_action('wp_ajax_nopriv_get_versiones_auto', 'mct_get_versiones_auto');
	function mct_get_versiones_auto() {
		global $wpdb;
		$marca = sanitize_text_field($_POST['marca']);
		$modelo = sanitize_text_field($_POST['modelo']);
		
		// CAMBIO AQUI: cotizador_autos
		$resultados = $wpdb->get_results( $wpdb->prepare(
			"SELECT version, precio_base FROM cotizador_autos WHERE marca = %s AND modelo = %s ORDER BY version ASC", 
			$marca, $modelo
		));

		wp_send_json($resultados);
	}

	function mct_cotizador_autos() {
		global $wpdb;
		// 1. SOLUCIÓN: Aseguramos que jQuery se cargue
    	wp_enqueue_script('jquery');		
		// Obtener todas las marcas al cargar la página (Carga inicial)
		$marcas = $wpdb->get_results("SELECT DISTINCT marca FROM cotizador_autos ORDER BY marca ASC");
		
		ob_start();
		?>
		<div class="cotizador-wrapper" style="background:#f9f9f9; padding:25px; border-radius:8px; max-width:850px; margin:0 auto;">
			<h3 style="text-align:center;">Cotiza tu Vehículo</h3>
			
			<form id="form-cotizador">
				
				<div class="form-group">
					<label>Marca:</label>
					<select id="select_marca" class="mct-input">
						<option value="">Selecciona una marca</option>
						<?php foreach($marcas as $m): ?>
							<option value="<?php echo esc_attr($m->marca); ?>"><?php echo esc_html($m->marca); ?></option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="form-group">
					<label>Modelo:</label>
					<select id="select_modelo" class="mct-input" disabled>
						<option value="">Selecciona primero la marca</option>
					</select>
				</div>

				<div class="form-group">
					<label>Versión:</label>
					<select id="select_version" class="mct-input" disabled>
						<option value="">Selecciona primero el modelo</option>
					</select>
				</div>

				<input type="hidden" id="precio_base_hidden" value="0">

				<hr>

				<div class="form-group">
					<label>Año:</label>
					<input type="number" id="anio_auto" placeholder="Ej: 2021" class="mct-input">
				</div>
				
				<div class="form-group">
					<label>Kilometraje:</label>
					<input type="number" id="kilometraje" placeholder="Ej: 45000" class="mct-input">
				</div>

				<button type="button" id="btn-calcular" onclick="calcularCotizacion()" disabled 
						style="width:100%; margin-top:15px; background:#0073aa; color:white; padding:12px; border:none; cursor:pointer; opacity:0.5;">
					Completar datos para calcular
				</button>
			</form>

			<div id="resultado-cotizacion" style="margin-top:20px; text-align:center; font-size:1.4em; font-weight:bold; color:#333;"></div>
			<div id="contenedor-boton-wa" style="margin-top:20px; display:none;">
				
				<div style="display: flex; gap: 10px; justify-content: center; flex-wrap: wrap;">
						
					<button type="button" onclick="window.location.reload()" 
							style="background:#f0f0f1; color:#333; padding:10px 20px; border:1px solid #ccc; border-radius:50px; cursor:pointer; font-weight:bold; font-size:16px;">
							↻ Recalcular
					</button>

					<a id="btn-whatsapp" href="#" target="_blank" 
					style="background:#25D366; color:white; padding:10px 20px; border-radius:50px; text-decoration:none; font-weight:bold; font-size:16px; display:inline-flex; align-items:center; border:1px solid #25D366;">
						<span style="margin-right:8px;">📱</span> Vender por WhatsApp
					</a>

				</div>
				<p style="font-size:12px; color:#666; margin-top:10px; text-align:center;">Un asesor revisará tu auto para confirmar el precio final.</p>
			</div>
		</div>

		<style>
			.mct-input { width: 100%; padding: 8px; margin-bottom: 10px; border:1px solid #ddd; border-radius:4px; }
		</style>

		<script>
			// 2. SOLUCIÓN: Usamos 'window.addEventListener' (Javascript nativo) para esperar
			// a que todo cargue (incluido jQuery) antes de intentar usarlo.
			window.addEventListener('load', function() {
				
				// Ahora sí es seguro llamar a jQuery
				jQuery(document).ready(function($) {
					
					// LOGICA MARCA -> MODELO
					$('#select_marca').change(function(){
						var marca = $(this).val();
						$('#select_modelo').html('<option value="">Cargando...</option>').prop('disabled', true);
						$('#select_version').html('<option value="">Selecciona primero el modelo</option>').prop('disabled', true);
						$('#precio_base_hidden').val(0);

						if(marca){
							$.post('<?php echo admin_url('admin-ajax.php'); ?>', {
								action: 'get_modelos_auto', // Asegurate que este action coincida con tu add_action en PHP
								marca: marca
							}, function(response) {
								var options = '<option value="">Selecciona un Modelo</option>';
								$.each(response, function(index, item){
									options += '<option value="' + item.modelo + '">' + item.modelo + '</option>';
								});
								$('#select_modelo').html(options).prop('disabled', false);
							});
						}
					});

					// LOGICA MODELO -> VERSION
					$('#select_modelo').change(function(){
						var modelo = $(this).val();
						var marca = $('#select_marca').val();
						
						$('#select_version').html('<option value="">Cargando...</option>').prop('disabled', true);

						if(modelo){
							$.post('<?php echo admin_url('admin-ajax.php'); ?>', {
								action: 'get_versiones_auto',
								marca: marca,
								modelo: modelo
							}, function(response) {
								var options = '<option value="">Selecciona la Versión</option>';
								$.each(response, function(index, item){
									options += '<option value="' + item.version + '" data-precio="' + item.precio_base + '">' + item.version + '</option>';
								});
								$('#select_version').html(options).prop('disabled', false);
							});
						}
					});

					// LOGICA VERSION -> PRECIO
					$('#select_version').change(function(){
						var precio = $(this).find(':selected').data('precio');
						$('#precio_base_hidden').val(precio);
						if(precio) {
							$('#btn-calcular').prop('disabled', false).css('opacity', '1');
						}
					});

				}); // Fin jQuery ready

			}); // Fin Window Load

			// FUNCION DE CALCULO (Fuera del scope de jQuery para que el onclick del botón la encuentre)
			function calcularCotizacion() {
				var precioBase = parseFloat(document.getElementById('precio_base_hidden').value);
				var anioAuto = parseInt(document.getElementById('anio_auto').value);
				var km = parseInt(document.getElementById('kilometraje').value);
				var anioActual = new Date().getFullYear();

				if (!precioBase || !anioAuto || isNaN(km)) {
					alert("Por favor completa todos los campos correctamente.");
					return;
				}
				
				// Logica de descuento
				let valor = precioBase * 0.70; // 30% inicial
				let antiguedad = anioActual - anioAuto;
				if (antiguedad < 0) antiguedad = 0; 
				
				for (let i = 0; i < antiguedad; i++) {
					valor = valor * 0.95; // 5% anual compuesto
				}

				if (km > 100000 && km <= 150000) {
					valor = valor * 0.95;
				} else if (km > 150000 && km <= 200000) {
					valor = valor * 0.90;
				} else if (km > 200000) {
					valor = valor * 0.85; 
				}

				var formatter = new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'USD', minimumFractionDigits: 0 });
				var precioFinal = formatter.format(valor);
				document.getElementById('resultado-cotizacion').innerHTML = "Valor estimado de toma: " + precioFinal;

				// Obtener textos para el mensaje
				var selMarca = document.getElementById('select_marca');
				var txtMarca = selMarca.options[selMarca.selectedIndex].text;
				var selModelo = document.getElementById('select_modelo');
				var txtModelo = selModelo.options[selModelo.selectedIndex].text;
				var selVersion = document.getElementById('select_version');
				var txtVersion = selVersion.options[selVersion.selectedIndex].text;
				// Preparar WhatsApp
				var telefono = "5493816032728";
				var mensaje = "Hola, coticé mi auto en la web y quiero validarlo. \n" +
							"🚘 Auto: " + txtMarca + " " + txtModelo + " " + txtVersion + "\n" +
							"📅 Año: " + anioAuto + "\n" +
							"🛣️ Km: " + km + "\n" +
							"💰 Cotización Web: " + precioFinal;
				
				document.getElementById('btn-whatsapp').href = "https://wa.me/" + telefono + "?text=" + encodeURIComponent(mensaje);

				// MOSTRAR LOS BOTONES
				document.getElementById('contenedor-boton-wa').style.display = 'block';
			}
		</script>
		<?php
		return ob_get_clean();
	}
	add_shortcode('cotizador_autos', 'mct_cotizador_autos');

?>