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
                        <?php echo isset($_GET['precio_max']) ? '$' . ($_GET['precio_max']/1000000) . 'M' : '$150M'; ?>
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
                    // Verificamos si estaba marcado antes
                    $checked = (isset($_GET['carroceria']) && $_GET['carroceria'] == $term->slug) ? 'checked' : '';
            ?>
                <label class="chk-container">
                    <?php echo $term->name; ?>
                    <input type="radio" name="carroceria" value="<?php echo $term->slug; ?>" <?php echo $checked; ?>>
                    <span class="checkmark"></span>
                </label>
            <?php endforeach; endif; ?>
        </div>
        
        <button type="submit" class="btn-filtrar" style="background:#003478; color:#fff; padding:10px 20px; border:none; border-radius:4px; width:100%; cursor:pointer; font-weight:bold; margin-top:10px;">APLICAR FILTROS</button>
        
        <?php if(isset($_GET['carroceria']) || isset($_GET['precio_max'])): ?>
            <a href="<?php echo get_post_type_archive_link('autos'); ?>" class="btn-clean-filters" style="display:block; text-align:center; margin-top:10px;">Limpiar filtros</a>
        <?php endif; ?>

    </form>
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
                // Detectamos si es Objeto o Array
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
                                // Busca la V1_C1, si no la V1, si no la Destacada
                                $img_card = get_field('v1_c1_foto');
                                if(empty($img_card)) $img_card = get_field('v1_foto');
                                if(empty($img_card)) $img_card = get_the_post_thumbnail_url(get_the_ID(), 'medium_large');
                                
                                // Precio: precio general o el de la V1
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