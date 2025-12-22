<?php get_header(); 

    $img_inicial = get_field('v1_c1_foto');

?>

<div class="ford-container-single">
    
    <div class="ford-col-sticky">
        <div class="sticky-wrapper">
            <div class="ford-main-gallery">
                <div class="main-image-container">
                    <img id="main-car-image" src="<?php echo esc_url($img_inicial); ?>" alt="<?php the_title(); ?>">
                </div>
            </div>

            <div class="ford-quick-specs">
                <div class="spec-item">
                    <span class="dashicons dashicons-dashboard"></span>
                    <span id="spec-motor"><?php the_field('motor'); ?></span>
                </div>
                <div class="spec-item">
                    <span class="dashicons dashicons-admin-generic"></span>
                    <span id="spec-transmision"><?php the_field('transmision'); ?></span>
                </div>
            </div>
            
            <p class="disclaimer-legal">Imágenes de carácter ilustrativo. Los colores pueden variar.</p>
        </div>
    </div>

    <div class="ford-col-scroll">
        
        <header class="ford-single-header">
            <h1 class="model-title"><?php the_title(); ?></h1>
            <div class="price-box">
                <span class="label">Precio sugerido desde</span>
                <span class="price" id="dynamic-price">$<?php echo number_format(get_field('precio'), 0, ',', '.'); ?></span>
            </div>
        </header>

        <hr class="ford-divider">

        <section class="config-section">
            <h3>Elegí tu versión</h3>
            <div class="version-selector">
                <?php 
                $max_versiones = 3; 
                $max_colores = 3; 
                $primero_activo = false;

                for ($i = 1; $i <= $max_versiones; $i++) {
                    $v_nombre = get_field('v' . $i . '_nombre');
                    $v_precio = get_field('v' . $i . '_precio');
                    
                    // Si la versión existe, recopilamos sus colores
                    if ( !empty($v_nombre) ) {
                        $colores_array = array();
                        
                        // Recorremos los slots de colores de ESTA versión
                        for ($k = 1; $k <= $max_colores; $k++) {
                            $c_nombre = get_field('v' . $i . '_c' . $k . '_nombre');
                            $c_hex = get_field('v' . $i . '_c' . $k . '_hex');
                            $c_foto = get_field('v' . $i . '_c' . $k . '_foto');

                            // Si hay foto y hex, añadimos al array
                            if( !empty($c_foto) && !empty($c_hex) ) {
                                $colores_array[] = array(
                                    'nombre' => $c_nombre,
                                    'hex'    => $c_hex,
                                    'foto'   => $c_foto
                                );
                            }
                        }

                        // Convertimos el array de colores a JSON para pasarlo al JS
                        $json_colores = json_encode($colores_array);
                        
                        // Determinamos la clase active e imagen inicial (usamos la del primer color como default)
                        $clase_active = '';
                        $img_inicial = !empty($colores_array[0]['foto']) ? $colores_array[0]['foto'] : '';
                        
                        if ( !$primero_activo ) {
                            $clase_active = 'active';
                            $primero_activo = true;
                            // Guardamos la data del primero para renderizar los colores al cargar la página
                            $first_load_colors = $json_colores;
                        }
                        ?>
                        
                        <button class="btn-version <?php echo $clase_active; ?>" 
                            data-price="<?php echo $v_precio; ?>" 
                            data-img="<?php echo $img_inicial; ?>"
                            data-colors='<?php echo $json_colores; ?>'>
                            <?php echo $v_nombre; ?>
                        </button>

                    <?php 
                    } 
                } 
                ?>
            </div>
        </section>

        <section class="config-section">
            <h3>Elegí tu color</h3>
            <div class="color-selector" id="color-container"></div>
            <p id="color-name-display">Seleccioná un color</p>
        </section>

        <section class="ford-calculator-box">
            <div class="calc-header">
                <h4>Simulá tu financiación</h4>
            </div>
            
            <div class="calc-body">
                <label>Capital a financiar: <strong id="monto-display">$ 10.000.000</strong></label>
                <input type="range" id="monto-slider" min="1000000" max="25000000" step="500000" value="10000000">
                
                <label>Plazo (Meses)</label>
                <div class="plazo-buttons">
                    <button class="btn-plazo" data-meses="12">12</button>
                    <button class="btn-plazo active" data-meses="24">24</button>
                    <button class="btn-plazo" data-meses="48">48</button>
                </div>

                <div class="resultado-cuota">
                    <span>Cuota promedio estimada</span>
                    <strong id="cuota-final">$ 650.000</strong>
                </div>
                <small class="calc-legal">TNA 52%. Sujeto a aprobación crediticia.</small>
            </div>
        </section>

        <section class="lead-form-section">
            <h3>Solicitar cotización</h3>
            <form class="ford-form">
                <div class="form-row">
                    <input type="text" placeholder="Nombre" required>
                    <input type="text" placeholder="Apellido" required>
                </div>
                <input type="email" placeholder="Correo electrónico" required>
                <input type="tel" placeholder="Teléfono" required>
                <button type="submit" class="btn-ford-primary">ENVIAR SOLICITUD</button>
            </form>
        </section>

    </div>
</div>
<section class="related-autos">
    <div class="ford-container">
        <h2>
            También te puede interesar
        </h2>
        
        <div class="ford-autos-grid">
            <?php
            $related = new WP_Query(array(
                'post_type' => 'autos',
                'posts_per_page' => 3,
                'post__not_in' => array(get_the_ID()), // Excluir auto actual
                'orderby' => 'rand' // Aleatorio
            ));

            if($related->have_posts()): while($related->have_posts()): $related->the_post(); 
                
                // --- LÓGICA DE IMAGEN (Misma que Archive) ---
                $img_card = get_field('v1_c1_foto');
                if(empty($img_card)) $img_card = get_field('v1_foto');
                if(empty($img_card)) $img_card = get_the_post_thumbnail_url(get_the_ID(), 'medium_large');
                
                // --- LÓGICA DE PRECIO (Misma que Archive) ---
                $precio_card = get_field('precio');
                if(empty($precio_card)) $precio_card = get_field('v1_precio');
            ?>
                
                <article class="ford-card">
                    <a href="<?php the_permalink(); ?>">
                        <div class="card-img-wrapper">
                            <img src="<?php echo esc_url($img_card); ?>" alt="<?php the_title(); ?>">
                        </div>
                        <div class="card-body">
                            <h3><?php the_title(); ?></h3>
                            <div class="card-footer">
                                <span class="lbl">Precio desde</span>
                                <span class="val">$<?php echo number_format($precio_card, 0, ',', '.'); ?></span>
                            </div>
                            <span class="btn-ver-mas">Ver detalles</span>
                        </div>
                    </a>
                </article>

            <?php endwhile; endif; wp_reset_postdata(); ?>
        </div>
    </div>
</section>
<script>
    var mctInitialColors = <?php echo isset($first_load_colors) ? $first_load_colors : '[]'; ?>;
</script>
<?php get_footer(); ?>