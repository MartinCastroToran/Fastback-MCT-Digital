/**
 * Lógica de Calculadora y Configurador - Proyecto Fastback
 * MCT Digital
 */

document.addEventListener('DOMContentLoaded', function() {
    

    const versionBtns = document.querySelectorAll('.btn-version');
    const colorContainer = document.getElementById('color-container');
    const colorNameDisplay = document.getElementById('color-name-display');
    const mainImage = document.getElementById('main-car-image');
    const priceDisplay = document.getElementById('dynamic-price');
    const colorBtns = document.querySelectorAll('.color-btn');
    const specMotor = document.getElementById('spec-motor'); // Si se desea cambiar specs dinámicamente
    // Función para dibujar los círculos de colores
    function renderColors(colorsArray) {
        colorContainer.innerHTML = ''; // Limpiar contenedor
        
        if(colorsArray.length === 0) {
            colorNameDisplay.innerText = "No hay colores disponibles";
            return;
        }

        // Resetear nombre
        colorNameDisplay.innerText = colorsArray[0].nombre;

        colorsArray.forEach((color, index) => {
            const btn = document.createElement('button');
            btn.className = 'color-btn';
            btn.style.backgroundColor = color.hex;
            btn.setAttribute('data-img', color.foto);
            btn.setAttribute('data-name', color.nombre);
            
            // Marcar el primero como seleccionado visualmente (opcional)
            if(index === 0) btn.style.transform = 'scale(1.2)';

            btn.addEventListener('click', function() {
                // Efecto visual selección
                document.querySelectorAll('.color-btn').forEach(b => b.style.transform = 'scale(1)');
                this.style.transform = 'scale(1.2)';

                // Cambiar Texto y Foto
                colorNameDisplay.innerText = color.nombre;
                changeMainImage(color.foto);
            });

            colorContainer.appendChild(btn);
        });
    }

    // Función auxiliar para cambiar imagen con fade
    function changeMainImage(url) {
        if(!mainImage || !url) return;
        mainImage.style.opacity = 0.5;
        setTimeout(() => {
            mainImage.src = url;
            mainImage.style.opacity = 1;
        }, 200);
    }

    // --- LÓGICA VERSIÓN ---
    versionBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            versionBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            const price = this.getAttribute('data-price');
            const baseImg = this.getAttribute('data-img');
            
            // El atributo data-colors es un string JSON, hay que parsearlo
            const colorsJson = JSON.parse(this.getAttribute('data-colors'));

            // 1. Actualizar Precio
            if(priceDisplay) {
                priceDisplay.style.opacity = 0;
                setTimeout(() => {
                    priceDisplay.innerText = '$ ' + parseInt(price).toLocaleString('es-AR');
                    priceDisplay.style.opacity = 1;
                }, 200);
            }

            // 2. Actualizar Imagen (volvemos a la imagen por defecto de esa versión/color)
            changeMainImage(baseImg);

            // 3. RE-RENDERIZAR COLORES
            renderColors(colorsJson);
            
            // 4. Actualizar calculadora (función existente)
            if(typeof updateCalculatorBase === 'function') {
                updateCalculatorBase(price);
            }
        });
    });

    // --- INICIALIZACIÓN ---
    // Cargar los colores de la primera versión activa al abrir la página
    if(typeof mctInitialColors !== 'undefined' && mctInitialColors.length > 0) {
        renderColors(mctInitialColors);
    }

    /* =========================================
       1. LÓGICA DE VERSIONES Y COLORES
       ========================================= */


    // --- Selector de Versión ---
    if (versionBtns.length > 0) {
        versionBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                // 1. Estilos visuales (Active state)
                versionBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');

                // 2. Obtener datos del botón (Data Attributes)
                const newPrice = this.getAttribute('data-price');
                const newImg = this.getAttribute('data-img');
                // const newMotor = this.getAttribute('data-motor'); // Opcional

                // 3. Actualizar Precio con animación de opacidad
                if(priceDisplay) {
                    priceDisplay.style.opacity = 0;
                    setTimeout(() => {
                        priceDisplay.innerText = '$ ' + parseInt(newPrice).toLocaleString('es-AR');
                        priceDisplay.style.opacity = 1;
                    }, 200);
                }

                // 4. Actualizar Imagen Principal
                if(newImg && mainImage) {
                    mainImage.style.opacity = 0.5;
                    setTimeout(() => {
                        mainImage.src = newImg;
                        mainImage.style.opacity = 1;
                    }, 200);
                }

                // 5. Recalcular topes de la calculadora
                updateCalculatorBase(newPrice);
            });
        });
    }

    // --- Selector de Color ---
    if (colorBtns.length > 0) {
        colorBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                // Lógica visual de selección (borde o escala)
                colorBtns.forEach(b => b.style.transform = 'scale(1)');
                this.style.transform = 'scale(1.2)';

                const colorImg = this.getAttribute('data-color-img');
                const colorName = this.getAttribute('data-color-name'); // Si agregas nombre del color
                
                if(colorImg && mainImage) {
                    mainImage.style.opacity = 0.5;
                    setTimeout(() => {
                        mainImage.src = colorImg;
                        mainImage.style.opacity = 1;
                    }, 200);
                }
                
                // Actualizar texto del nombre del color si existe el elemento
                const colorNameDisplay = document.getElementById('color-name-display');
                if(colorNameDisplay && colorName) {
                    colorNameDisplay.innerText = colorName;
                }
            });
        });
    }

    /* =========================================
       2. LÓGICA DE CALCULADORA FINANCIERA
       ========================================= */
    const slider = document.getElementById('monto-slider');
    const montoDisplay = document.getElementById('monto-display');
    const plazoBtns = document.querySelectorAll('.btn-plazo');
    const cuotaDisplay = document.getElementById('cuota-final');
    
    // Configuración Financiera (Variables Globales)
    const TNA = 0.52; // Tasa Nominal Anual (52%)
    const interesMensual = TNA / 12;

    function calcularCuota() {
        if(!slider) return;

        let capital = parseInt(slider.value);
        
        // Buscar plazo activo
        let plazoActivo = document.querySelector('.btn-plazo.active');
        let meses = plazoActivo ? parseInt(plazoActivo.getAttribute('data-meses')) : 24;
        
        // Fórmula Sistema Francés
        let factor = Math.pow(1 + interesMensual, -meses);
        let cuota = (capital * interesMensual) / (1 - factor);
        
        // Actualizar UI
        if(montoDisplay) montoDisplay.innerText = '$ ' + capital.toLocaleString('es-AR');
        if(cuotaDisplay) cuotaDisplay.innerText = '$ ' + Math.ceil(cuota).toLocaleString('es-AR');
    }

    // Event Listeners
    if(slider) {
        slider.addEventListener('input', calcularCuota);
    }

    if(plazoBtns.length > 0) {
        plazoBtns.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                plazoBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                calcularCuota();
            });
        });
    }

    /**
     * Actualiza el máximo financiable según el precio del auto seleccionado.
     * Se llama cada vez que cambia la versión.
     */
    function updateCalculatorBase(precioVehiculo) {
        if(!slider) return;
        
        // Regla de negocio: Financian hasta el 70%
        let maxFinanciable = parseInt(precioVehiculo) * 0.7; 
        
        slider.max = maxFinanciable;
        
        // Si el valor seleccionado previamente es mayor al nuevo máximo, corregirlo
        if(parseInt(slider.value) > maxFinanciable) {
            slider.value = maxFinanciable;
        }
        
        // Volver a calcular con los nuevos valores
        calcularCuota();
    }

    // Inicialización al cargar la página
    calcularCuota();
});