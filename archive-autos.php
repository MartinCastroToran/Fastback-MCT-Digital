<?php get_header(); ?>

<div class="ford-archive-container">
    
    <aside class="ford-sidebar">
        <h3 class="filter-title">Filtros</h3>
        
        <div class="filter-group">
            <h4>Precio sugerido</h4>
            <div class="range-slider-mock">
                <input type="range" min="0" max="100" value="50">
                <div class="price-labels">
                    <span>$10M</span>
                    <span>$80M</span>
                </div>
            </div>
        </div>

        <div class="filter-group">
            <h4>Carrocería</h4>
            <label class="chk-container">Pick-up
                <input type="checkbox">
                <span class="checkmark"></span>
            </label>
            <label class="chk-container">SUV
                <input type="checkbox" checked>
                <span class="checkmark"></span>
            </label>
            <label class="chk-container">Sedán
                <input type="checkbox">
                <span class="checkmark"></span>
            </label>
        </div>
        
        <button class="btn-clean-filters">Limpiar filtros</button>
    </aside>

    <main class="ford-grid-content">
        <?php 
        // 1. Obtenemos las categorías de forma segura
        $carrocerias = get_terms( array(
            'taxonomy'   => 'carroceria',
            'hide_empty' => true,
        ) );

        // Verificamos si hubo error o si está vacío
        if ( ! empty( $carrocerias ) && ! is_wp_error( $carrocerias ) ) :

            foreach ($carrocerias as $cat) : 
                // SOLUCIÓN AL ERROR: Detectamos si es Objeto o Array
                $cat_slug = is_object($cat) ? $cat->slug : $cat['slug'];
                $cat_name = is_object($cat) ? $cat->name : $cat['name'];
                
                // Query para buscar autos de ESTA categoría
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

                // Si hay autos en esta categoría, mostramos el título y la grilla
                if ($query->have_posts()) : ?>
                    
                    <div class="category-block">
                        <h2 class="cat-title"><?php echo $cat_name; ?></h2>

                        <div class="ford-autos-grid">
                            <?php while ($query->have_posts()) : $query->the_post(); 
                                // Lógica de imagen: busca la V1_C1, si no la V1, si no la Destacada
                                $img_card = get_field('v1_c1_foto');
                                if(empty($img_card)) $img_card = get_field('v1_foto');
                                if(empty($img_card)) $img_card = get_the_post_thumbnail_url(get_the_ID(), 'medium_large');
                                
                                // Precio: Usamos el precio general o el de la V1
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
        <?php endif; ?>
    </main>
</div>

<?php get_footer(); ?>