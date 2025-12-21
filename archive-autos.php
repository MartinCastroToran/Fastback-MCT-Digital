<?php get_header(); ?>

<div class="ford-archive-container">
    
    <aside class="ford-sidebar">
        <form action="<?php echo get_post_type_archive_link('autos'); ?>" method="GET">
            
            <h3 class="filter-title">Filtros</h3>
            
            <div class="filter-group">
                <h4>Precio Máximo</h4>
                <div class="range-slider-mock">
                    <input type="range" name="precio_max" min="0" max="150000000" step="1000000" 
                           value="<?php echo isset($_GET['precio_max']) ? $_GET['precio_max'] : '150000000'; ?>" 
                           oninput="document.getElementById('price-output').innerText = '$' + (parseInt(this.value)/1000000).toFixed(0) + 'M'">
                    
                    <div class="price-labels">
                        <span>$0</span>
                        <span id="price-output">
                            <?php echo isset($_GET['precio_max']) ? '$' . (intval($_GET['precio_max'])/1000000) . 'M' : '$150M'; ?>
                        </span>
                    </div>
                </div>
            </div>

            <div class="filter-group">
                <h4>Carrocería</h4>
                <?php 
                $terms = get_terms(array('taxonomy' => 'carroceria', 'hide_empty' => true));
                
                if (!empty($terms) && !is_wp_error($terms)):
                    foreach ($terms as $term): 
                        // Verificamos si estaba marcado para mantener el tilde puesto
                        $checked = (isset($_GET['carroceria']) && $_GET['carroceria'] == $term->slug) ? 'checked' : '';
                ?>
                    <label class="chk-container">
                        <?php echo $term->name; ?>
                        <input type="radio" name="carroceria" value="<?php echo $term->slug; ?>" <?php echo $checked; ?>>
                        <span class="checkmark"></span>
                    </label>
                <?php endforeach; endif; ?>
            </div>
            
            <button type="submit" class="btn-filtrar" style="background:#003478; color:#fff; padding:10px 20px; border:none; border-radius:4px; width:100%; cursor:pointer; font-weight:800; margin-top:10px;">APLICAR FILTROS</button>
            
            <?php if(isset($_GET['carroceria']) || isset($_GET['precio_max'])): ?>
                <a href="<?php echo get_post_type_archive_link('autos'); ?>" class="btn-clean-filters" style="display:block; text-align:center; margin-top:10px; font-size:12px;">Borrar filtros</a>
            <?php endif; ?>

        </form>
    </aside>

    <main class="ford-grid-content">
        <?php 
        // A. DETECTAR SI HAY FILTROS ACTIVOS EN LA URL
        $filtro_carroceria = isset($_GET['carroceria']) ? $_GET['carroceria'] : '';
        $filtro_precio = isset($_GET['precio_max']) ? $_GET['precio_max'] : '';
        $hay_filtros = !empty($filtro_carroceria) || !empty($filtro_precio);

        // --- ESCENARIO A: HAY FILTROS ACTIVOS (Lista única filtrada) ---
        if ($hay_filtros) : 
            
            // Query de Taxonomía (Categoría)
            $tax_query = array();
            if ($filtro_carroceria) {
                $tax_query[] = array(
                    'taxonomy' => 'carroceria',
                    'field'    => 'slug',
                    'terms'    => $filtro_carroceria,
                );
            }

            // Query de Campos Personalizados (Precio)
            $meta_query = array();
            if ($filtro_precio) {
                $meta_query[] = array(
                    'key'     => 'precio', // Asegúrate que en ACF el campo se llame 'precio'
                    'value'   => $filtro_precio,
                    'type'    => 'NUMERIC',
                    'compare' => '<=' 
                );
            }

            $args_filter = array(
                'post_type' => 'autos',
                'posts_per_page' => -1,
                'tax_query' => $tax_query,
                'meta_query' => $meta_query
            );
            $query_filtrada = new WP_Query($args_filter);
            ?>

            <h2 class="cat-title">Resultados de búsqueda</h2>

            <?php if ($query_filtrada->have_posts()) : ?>
                <div class="ford-autos-grid">
                    <?php while ($query_filtrada->have_posts()) : $query_filtrada->the_post(); 
                        // Lógica de Imagen Inteligente
                        $img_card = get_field('v1_c1_foto');
                        if(empty($img_card)) $img_card = get_the_post_thumbnail_url(get_the_ID(), 'medium_large');
                        
                        // Lógica de Precio Inteligente
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
                    <?php endwhile; wp_reset_postdata(); ?>
                </div>

            <?php else : ?>
                <div class="no-results" style="padding: 40px; text-align: center; background: #f9f9f9; border-radius: 8px;">
                    <h3 style="margin-top:0;">No encontramos autos con esos filtros.</h3>
                    <p>Prueba ampliando el rango de precios o cambiando la carrocería.</p>
                    <a href="<?php echo get_post_type_archive_link('autos'); ?>" style="color: #003478; font-weight: 800; text-transform: uppercase;">Ver todos los autos</a>
                </div>
            <?php endif; ?>

        <?php 
        // --- ESCENARIO B: NO HAY FILTROS (Vista original categorizada) ---
        else :
            
            // 1. Obtenemos las categorías
            $carrocerias = get_terms( array(
                'taxonomy'   => 'carroceria',
                'hide_empty' => true,
            ) );

            if ( ! empty( $carrocerias ) && ! is_wp_error( $carrocerias ) ) :

                foreach ($carrocerias as $cat) : 
                    // Validación Objeto/Array
                    $cat_slug = is_object($cat) ? $cat->slug : $cat['slug'];
                    $cat_name = is_object($cat) ? $cat->name : $cat['name'];
                    
                    $query = new WP_Query(array(
                        'post_type' => 'autos',
                        'tax_query' => array(
                            array(
                                'taxonomy' => 'carroceria',
                                'field'    => 'slug',
                                'terms'    => $cat_slug,
                            ),
                        ),
                    ));

                    if ($query->have_posts()) : ?>
                        
                        <div class="category-block">
                            <h2 class="cat-title"><?php echo $cat_name; ?></h2>
                            <div class="ford-autos-grid">
                                <?php while ($query->have_posts()) : $query->the_post(); 
                                    // Misma lógica de imagen/precio
                                    $img_card = get_field('v1_c1_foto');
                                    if(empty($img_card)) $img_card = get_field('v1_foto');
                                    if(empty($img_card)) $img_card = get_the_post_thumbnail_url(get_the_ID(), 'medium_large');
                                    
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

                                <?php endwhile; wp_reset_postdata(); ?>
                            </div>
                        </div>

                    <?php endif; // Fin if have_posts
                endforeach; 
            
            else : ?>
                <p>No se encontraron categorías de vehículos.</p>
            <?php endif; // Fin if empty carrocerias ?>
            
        <?php endif; // FIN DEL IF PRINCIPAL ($hay_filtros) ?>
    </main>
</div>

<?php get_footer(); ?>