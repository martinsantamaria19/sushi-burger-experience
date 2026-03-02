<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // Hero
            ['key' => 'hero_title', 'value' => 'El mayor complejo de depósitos seguros del país', 'type' => 'text', 'group' => 'hero'],
            ['key' => 'hero_subtitle', 'value' => 'Boxes desde 1.5m² hasta 1,500m² con seguridad y vigilancia 24/7.', 'type' => 'textarea', 'group' => 'hero'],
            ['key' => 'hero_description', 'value' => 'Soluciones de depósito para particulares, empresas y oficinas.', 'type' => 'textarea', 'group' => 'hero'],
            ['key' => 'hero_image', 'value' => '', 'type' => 'image', 'group' => 'hero'],

            // Features
            ['key' => 'feature_1_title', 'value' => 'Acceso 24/7', 'type' => 'text', 'group' => 'features'],
            ['key' => 'feature_2_title', 'value' => 'Vigilancia 24hs', 'type' => 'text', 'group' => 'features'],
            ['key' => 'feature_3_title', 'value' => 'Doble cerradura', 'type' => 'text', 'group' => 'features'],

            // Quote section
            ['key' => 'quote_title', 'value' => 'Solicitá tu cotización', 'type' => 'text', 'group' => 'quote'],
            ['key' => 'quote_subtitle', 'value' => 'Te contactamos en menos de 24 horas', 'type' => 'text', 'group' => 'quote'],

            // Intro paragraph
            ['key' => 'intro_text', 'value' => 'En BoxCenter Uruguay ofrecemos soluciones de self storage en Montevideo ideales para guardar muebles, herramientas o mercadería de forma segura. Nuestros depósitos privados cuentan con acceso controlado, cámaras de seguridad y vigilancia 24/7. Contratá tu depósito flexible sin permanencia y facilitá tu mudanza con nuestro servicio de carga y descarga.', 'type' => 'textarea', 'group' => 'general'],
            ['key' => 'intro_cta', 'value' => 'Descubrí nuestras soluciones', 'type' => 'text', 'group' => 'general'],

            // Solutions section
            ['key' => 'solutions_title', 'value' => 'Boxes Privados Para Particulares y Empresas', 'type' => 'text', 'group' => 'solutions'],
            ['key' => 'solutions_subtitle', 'value' => 'Ofrecemos soluciones de almacenamiento seguro adaptadas a tus necesidades específicas, desde uso personal hasta requerimientos empresariales.', 'type' => 'textarea', 'group' => 'solutions'],

            ['key' => 'solution_particulares_title', 'value' => 'Particulares', 'type' => 'text', 'group' => 'solutions'],
            ['key' => 'solution_particulares_tagline', 'value' => 'Cuando lo necesites, por el tiempo que lo necesites.', 'type' => 'text', 'group' => 'solutions'],
            ['key' => 'solution_particulares_points', 'value' => "Seguridad garantizada\nTarifas flexibles mensuales\nAcceso 24/7 con tu propia llave\nBoxes desde 1.5m² hasta 50m²", 'type' => 'textarea', 'group' => 'solutions'],
            ['key' => 'solution_particulares_description', 'value' => 'Ideal para mudanzas, renovaciones o almacenamiento personal', 'type' => 'textarea', 'group' => 'solutions'],
            ['key' => 'solution_particulares_image', 'value' => '', 'type' => 'image', 'group' => 'solutions'],

            ['key' => 'solution_empresas_title', 'value' => 'Empresas', 'type' => 'text', 'group' => 'solutions'],
            ['key' => 'solution_empresas_tagline', 'value' => 'Solución de depósito práctica, segura y confiable.', 'type' => 'text', 'group' => 'solutions'],
            ['key' => 'solution_empresas_points', 'value' => "Carga y descarga facilitada\nFacturación mensual\nContratos empresariales\nBoxes desde 5m² hasta 1,500m²", 'type' => 'textarea', 'group' => 'solutions'],
            ['key' => 'solution_empresas_description', 'value' => 'Perfecto para inventario, archivo documental o equipos', 'type' => 'textarea', 'group' => 'solutions'],
            ['key' => 'solution_empresas_image', 'value' => '', 'type' => 'image', 'group' => 'solutions'],

            ['key' => 'solution_oficinas_title', 'value' => 'Oficinas y Showrooms', 'type' => 'text', 'group' => 'solutions'],
            ['key' => 'solution_oficinas_tagline', 'value' => 'Cómodas salas de reuniones con WiFi y exhibición de productos.', 'type' => 'text', 'group' => 'solutions'],
            ['key' => 'solution_oficinas_points', 'value' => "Ubicación estratégica\nExhibición de productos\nSalas de reuniones privadas\nEspacios equipados con WiFi", 'type' => 'textarea', 'group' => 'solutions'],
            ['key' => 'solution_oficinas_description', 'value' => 'Expande tu negocio sin inversión en infraestructura', 'type' => 'textarea', 'group' => 'solutions'],
            ['key' => 'solution_oficinas_image', 'value' => '', 'type' => 'image', 'group' => 'solutions'],

            // Installations (instalaciones)
            ['key' => 'installations_title', 'value' => 'Instalaciones', 'type' => 'text', 'group' => 'installations'],
            ['key' => 'installations_subtitle', 'value' => 'Boxes – Guardamuebles – Bauleras – Depósitos', 'type' => 'text', 'group' => 'installations'],
            ['key' => 'install_boxes_title', 'value' => 'Boxes cerrados', 'type' => 'text', 'group' => 'installations'],
            ['key' => 'install_boxes_text', 'value' => 'Acceso 24/7. Abertura metálica, doble cerradura (únicas llaves en tu poder).', 'type' => 'textarea', 'group' => 'installations'],
            ['key' => 'install_perimeter_title', 'value' => 'Perímetro cercado', 'type' => 'text', 'group' => 'installations'],
            ['key' => 'install_perimeter_text', 'value' => 'Perímetro cercado, alarma contra robo e incendio, sensores de movimiento en perímetro y circulación.', 'type' => 'textarea', 'group' => 'installations'],
            ['key' => 'install_showroom_title', 'value' => 'Showroom', 'type' => 'text', 'group' => 'installations'],
            ['key' => 'install_showroom_text', 'value' => 'Acceso a showroom para uso propio', 'type' => 'textarea', 'group' => 'installations'],
            ['key' => 'install_cameras_title', 'value' => 'Monitoreo sistema de cámaras', 'type' => 'text', 'group' => 'installations'],
            ['key' => 'install_cameras_text', 'value' => 'Vigilancia 24 horas, con circuito cerrado de monitoreo interno y externo. Grabación de últimos 45 días.', 'type' => 'textarea', 'group' => 'installations'],

            // Location / stats
            ['key' => 'location_title', 'value' => 'Ubicación ideal', 'type' => 'text', 'group' => 'location'],
            ['key' => 'stat_years', 'value' => '1', 'type' => 'text', 'group' => 'location'],
            ['key' => 'stat_days', 'value' => '1', 'type' => 'text', 'group' => 'location'],
            ['key' => 'stat_hours', 'value' => '1', 'type' => 'text', 'group' => 'location'],
            ['key' => 'stat_years_label', 'value' => 'Años de trayectoria', 'type' => 'text', 'group' => 'location'],
            ['key' => 'stat_days_label', 'value' => 'Días abiertos', 'type' => 'text', 'group' => 'location'],
            ['key' => 'stat_hours_label', 'value' => 'Horas de vigilancia', 'type' => 'text', 'group' => 'location'],
            ['key' => 'location_center_min', 'value' => '5', 'type' => 'text', 'group' => 'location'],
            ['key' => 'location_pocitos_min', 'value' => '13', 'type' => 'text', 'group' => 'location'],
            ['key' => 'location_airport_min', 'value' => '22', 'type' => 'text', 'group' => 'location'],

            // Contact
            ['key' => 'contact_title', 'value' => 'Contacto', 'type' => 'text', 'group' => 'contact'],
            ['key' => 'contact_email', 'value' => 'info@boxcenter.com.uy', 'type' => 'text', 'group' => 'contact'],
            ['key' => 'contact_phone', 'value' => '', 'type' => 'text', 'group' => 'contact'],
            ['key' => 'contact_address', 'value' => 'Montevideo, Uruguay', 'type' => 'textarea', 'group' => 'contact'],
            ['key' => 'contact_cta', 'value' => 'Consultá por nuestros planes a medida', 'type' => 'text', 'group' => 'contact'],

            // Footer / meta
            ['key' => 'site_name', 'value' => 'BoxCenter Uruguay', 'type' => 'text', 'group' => 'meta'],
            ['key' => 'meta_description', 'value' => 'Depósitos seguros 24/7 en Uruguay. Boxes desde 1.5m² hasta 1,500m². Soluciones para particulares, empresas y oficinas.', 'type' => 'textarea', 'group' => 'meta'],
        ];

        foreach ($settings as $setting) {
            SiteSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
