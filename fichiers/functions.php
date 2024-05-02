<?php
/**
 * Theme functions and definitions
 *
 * @package HelloElementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'HELLO_ELEMENTOR_VERSION', '3.0.0' );

if ( ! isset( $content_width ) ) {
	$content_width = 800; // Pixels.
}

if ( ! function_exists( 'hello_elementor_setup' ) ) {
	/**
	 * Set up theme support.
	 *
	 * @return void
	 */
	function hello_elementor_setup() {
		if ( is_admin() ) {
			hello_maybe_update_theme_version_in_db();
		}

		if ( apply_filters( 'hello_elementor_register_menus', true ) ) {
			register_nav_menus( [ 'menu-1' => esc_html__( 'Header', 'hello-elementor' ) ] );
			register_nav_menus( [ 'menu-2' => esc_html__( 'Footer', 'hello-elementor' ) ] );
		}

		if ( apply_filters( 'hello_elementor_post_type_support', true ) ) {
			add_post_type_support( 'page', 'excerpt' );
		}

		if ( apply_filters( 'hello_elementor_add_theme_support', true ) ) {
			add_theme_support( 'post-thumbnails' );
			add_theme_support( 'automatic-feed-links' );
			add_theme_support( 'title-tag' );
			add_theme_support(
				'html5',
				[
					'search-form',
					'comment-form',
					'comment-list',
					'gallery',
					'caption',
					'script',
					'style',
				]
			);
			add_theme_support(
				'custom-logo',
				[
					'height'      => 100,
					'width'       => 350,
					'flex-height' => true,
					'flex-width'  => true,
				]
			);

			/*
			 * Editor Style.
			 */
			add_editor_style( 'classic-editor.css' );

			/*
			 * Gutenberg wide images.
			 */
			add_theme_support( 'align-wide' );

			/*
			 * WooCommerce.
			 */
			if ( apply_filters( 'hello_elementor_add_woocommerce_support', true ) ) {
				// WooCommerce in general.
				add_theme_support( 'woocommerce' );
				// Enabling WooCommerce product gallery features (are off by default since WC 3.0.0).
				// zoom.
				add_theme_support( 'wc-product-gallery-zoom' );
				// lightbox.
				add_theme_support( 'wc-product-gallery-lightbox' );
				// swipe.
				add_theme_support( 'wc-product-gallery-slider' );
			}
		}
	}
}
add_action( 'after_setup_theme', 'hello_elementor_setup' );

function hello_maybe_update_theme_version_in_db() {
	$theme_version_option_name = 'hello_theme_version';
	// The theme version saved in the database.
	$hello_theme_db_version = get_option( $theme_version_option_name );

	// If the 'hello_theme_version' option does not exist in the DB, or the version needs to be updated, do the update.
	if ( ! $hello_theme_db_version || version_compare( $hello_theme_db_version, HELLO_ELEMENTOR_VERSION, '<' ) ) {
		update_option( $theme_version_option_name, HELLO_ELEMENTOR_VERSION );
	}
}

if ( ! function_exists( 'hello_elementor_display_header_footer' ) ) {
	/**
	 * Check whether to display header footer.
	 *
	 * @return bool
	 */
	function hello_elementor_display_header_footer() {
		$hello_elementor_header_footer = true;

		return apply_filters( 'hello_elementor_header_footer', $hello_elementor_header_footer );
	}
}

if ( ! function_exists( 'hello_elementor_scripts_styles' ) ) {
	/**
	 * Theme Scripts & Styles.
	 *
	 * @return void
	 */
	function hello_elementor_scripts_styles() {
		$min_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		if ( apply_filters( 'hello_elementor_enqueue_style', true ) ) {
			wp_enqueue_style(
				'hello-elementor',
				get_template_directory_uri() . '/style' . $min_suffix . '.css',
				[],
				HELLO_ELEMENTOR_VERSION
			);
		}

		if ( apply_filters( 'hello_elementor_enqueue_theme_style', true ) ) {
			wp_enqueue_style(
				'hello-elementor-theme-style',
				get_template_directory_uri() . '/theme' . $min_suffix . '.css',
				[],
				HELLO_ELEMENTOR_VERSION
			);
		}

		if ( hello_elementor_display_header_footer() ) {
			wp_enqueue_style(
				'hello-elementor-header-footer',
				get_template_directory_uri() . '/header-footer' . $min_suffix . '.css',
				[],
				HELLO_ELEMENTOR_VERSION
			);
		}
	}
}
add_action( 'wp_enqueue_scripts', 'hello_elementor_scripts_styles' );

if ( ! function_exists( 'hello_elementor_register_elementor_locations' ) ) {
	/**
	 * Register Elementor Locations.
	 *
	 * @param ElementorPro\Modules\ThemeBuilder\Classes\Locations_Manager $elementor_theme_manager theme manager.
	 *
	 * @return void
	 */
	function hello_elementor_register_elementor_locations( $elementor_theme_manager ) {
		if ( apply_filters( 'hello_elementor_register_elementor_locations', true ) ) {
			$elementor_theme_manager->register_all_core_location();
		}
	}
}
add_action( 'elementor/theme/register_locations', 'hello_elementor_register_elementor_locations' );

if ( ! function_exists( 'hello_elementor_content_width' ) ) {
	/**
	 * Set default content width.
	 *
	 * @return void
	 */
	function hello_elementor_content_width() {
		$GLOBALS['content_width'] = apply_filters( 'hello_elementor_content_width', 800 );
	}
}
add_action( 'after_setup_theme', 'hello_elementor_content_width', 0 );

if ( ! function_exists( 'hello_elementor_add_description_meta_tag' ) ) {
	/**
	 * Add description meta tag with excerpt text.
	 *
	 * @return void
	 */
	function hello_elementor_add_description_meta_tag() {
		if ( ! apply_filters( 'hello_elementor_description_meta_tag', true ) ) {
			return;
		}

		if ( ! is_singular() ) {
			return;
		}

		$post = get_queried_object();
		if ( empty( $post->post_excerpt ) ) {
			return;
		}

		echo '<meta name="description" content="' . esc_attr( wp_strip_all_tags( $post->post_excerpt ) ) . '">' . "\n";
	}
}
add_action( 'wp_head', 'hello_elementor_add_description_meta_tag' );

// Admin notice
if ( is_admin() ) {
	require get_template_directory() . '/includes/admin-functions.php';
}

// Settings page
require get_template_directory() . '/includes/settings-functions.php';

// Header & footer styling option, inside Elementor
require get_template_directory() . '/includes/elementor-functions.php';

if ( ! function_exists( 'hello_elementor_customizer' ) ) {
	// Customizer controls
	function hello_elementor_customizer() {
		if ( ! is_customize_preview() ) {
			return;
		}

		if ( ! hello_elementor_display_header_footer() ) {
			return;
		}

		require get_template_directory() . '/includes/customizer-functions.php';
	}
}
add_action( 'init', 'hello_elementor_customizer' );

if ( ! function_exists( 'hello_elementor_check_hide_title' ) ) {
	/**
	 * Check whether to display the page title.
	 *
	 * @param bool $val default value.
	 *
	 * @return bool
	 */
	function hello_elementor_check_hide_title( $val ) {
		if ( defined( 'ELEMENTOR_VERSION' ) ) {
			$current_doc = Elementor\Plugin::instance()->documents->get( get_the_ID() );
			if ( $current_doc && 'yes' === $current_doc->get_settings( 'hide_title' ) ) {
				$val = false;
			}
		}
		return $val;
	}
}
add_filter( 'hello_elementor_page_title', 'hello_elementor_check_hide_title' );

/**
 * BC:
 * In v2.7.0 the theme removed the `hello_elementor_body_open()` from `header.php` replacing it with `wp_body_open()`.
 * The following code prevents fatal errors in child themes that still use this function.
 */
if ( ! function_exists( 'hello_elementor_body_open' ) ) {
	function hello_elementor_body_open() {
		wp_body_open();
	}
}


function add_minute_interval($schedules) {
    $schedules['every_minute'] = array(
        'interval' => 60, // Intervalle en secondes
        'display'  => __('Every Minute')
    );
    return $schedules;
}
add_filter('cron_schedules', 'add_minute_interval');


// PHOTO NOTIF passe de draft à publish et ENVOIE PUSH NOTIF

function check_and_publish_cpt() {
    $args = array(
        'post_type' => 'photo-notif', // Votre CPT
        'post_status' => 'draft',
        'posts_per_page' => -1,
    );

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $post_id = get_the_ID();
            $post_timestamp = get_post_meta($post_id, 'date-notif', true); // Timestamp Unix
            $post_timestamp_plus_10min = $post_timestamp + 600; // Ajouter 10 minutes

            // Convertir en date/heure locale
            $post_datetime_local = date_i18n('Y-m-d H:i:s', $post_timestamp);
            $post_datetime_plus_10min_local = date_i18n('Y-m-d H:i:s', $post_timestamp_plus_10min);
            $current_datetime_local = date_i18n('Y-m-d H:i:s', current_time('timestamp'));

            // Logs pour le débogage
            error_log("Post ID: $post_id, Scheduled Local Time: $post_datetime_local, Scheduled Local Time + 10min: $post_datetime_plus_10min_local, Current Local Time: $current_datetime_local");

            // Comparaison en utilisant les timestamps
            if (current_time('timestamp') >= $post_timestamp_plus_10min) {
                wp_update_post(array(
                    'ID' => $post_id,
                    'post_status' => 'publish'
                ));
			
                // Vérifier si la notification a déjà été envoyée
                $notification_sent = get_post_meta($post_id, 'push-notification-envoyee', true);
                if ($notification_sent !== 'oui') {
                    // Envoyer la notification si le CPT est passé en "publish"
                    $employe_id = get_post_meta($post_id, 'employe', true);
                    if (!empty($employe_id)) {
                        envoyer_notification_photo_notif($employe_id);
                        // Mettre à jour le meta field après l'envoi de la notification
                        update_post_meta($post_id, 'push-notification-envoyee', 'oui');
                    }
                }
            }
        }
    }
}


function envoyer_notification_photo_notif($user_id) {
    $tokens = get_user_meta($user_id, 'push-tkn', true);
    if (!empty($tokens)) {
        foreach ($tokens as $token) {
            $response = wp_remote_post('https://exp.host/--/api/v2/push/send', array(
                'headers' => array('Content-Type' => 'application/json'),
                'body' => json_encode(array(
                    'to' => $token,
                    'title' => 'Selfie avec ton partenaire de pause ?',
                    'body' => 'La pause touche à sa fin, immortalisez ce moment !'
                ))
            ));
            // Mettre à jour le meta field avec le timestamp actuel
            update_post_meta(get_the_ID(), 'notification-sent-time', time());
        }
    }
}


// Passer la photo-notif de publish à trash 10 minutes après l'envoie de la notif
function check_and_draft_cpt_photo_notif() {
	
date_default_timezone_set('Europe/Paris'); 
	
    $args = array(
        'post_type' => 'photo-notif',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'meta_query' => array(
            array(
                'key' => 'push-notification-envoyee',
                'value' => 'oui',
                'compare' => '='
            )
        ),
    );

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $post_id = get_the_ID();
            $notification_sent_time = get_post_meta($post_id, 'notification-sent-time', true);
            $current_time = time();

            if (($current_time - $notification_sent_time) >= 600) { // 600 secondes = 10 minutes
                wp_update_post(array(
                    'ID' => $post_id,
                    'post_status' => 'trash'
                ));
            }
        }
    }
date_default_timezone_set('UTC'); // Remettre à UTC pour ne pas affecter d'autres opérations
}


if (!wp_next_scheduled('check_and_revert_cpt_to_draft_hook')) {
    wp_schedule_event(time(), 'every_minute', 'check_and_revert_cpt_to_draft_hook');
}

add_action('check_and_revert_cpt_to_draft_hook', 'check_and_draft_cpt_photo_notif');






// Passer la PAUSE de publish à draft  15 minutes après

if (!wp_next_scheduled('check_and_publish_cpt_hook')) {
    wp_schedule_event(time(), 'every_minute', 'check_and_publish_cpt_hook');
}

add_action('check_and_publish_cpt_hook', 'check_and_publish_cpt');


function check_and_draft_pose_cpt() {
    $args = array(
        'post_type' => 'pose', // Remplacez par le slug de votre CPT "pose"
        'post_status' => 'publish',
        'posts_per_page' => -1,
    );

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $post_id = get_the_ID();
            $quand_timestamp = get_post_meta($post_id, 'quand', true); // Récupérer le timestamp
            $quand_timestamp_plus_15min = $quand_timestamp + (15 * 60); // Ajouter 15 minutes

            if (current_time('timestamp') >= $quand_timestamp_plus_15min) {
                // Passer le CPT en mode draft
                wp_update_post(array(
                    'ID' => $post_id,
                    'post_status' => 'draft'
                ));
            }
        }
    }
}

if (!wp_next_scheduled('check_and_draft_pose_cpt_hook')) {
    wp_schedule_event(time(), 'every_minute', 'check_and_draft_pose_cpt_hook');
}

add_action('check_and_draft_pose_cpt_hook', 'check_and_draft_pose_cpt');

///////// PUSH NOTIFICATION /////////

/// PUSH NOTIFICATION 1
add_action('publish_demande-en-cours', 'send_notification_on_demande_publish', 10, 2);

function send_notification_on_demande_publish($ID, $post) {
    $etat = get_post_meta($ID, 'etat', true);
    $pose_id = get_post_meta($ID, 'pose-concernee', true);
    $auteur_pose_id = get_post_field('post_author', $pose_id);
    $current_user_id = get_current_user_id();

    // Vérifiez si l'utilisateur actuel est différent de l'auteur de la pose
    // et si l'état n'est pas "accepté"
    if ($current_user_id != $auteur_pose_id && $etat != 'accepte') {
        $tokens = get_user_meta($auteur_pose_id, 'push-tkn', true);
        if (!empty($tokens)) {
            foreach ($tokens as $token) {
                send_push_notification($token, "Un.e collègue propose sa compagnie !", "(P)OSE");
            }
        }
    }
}




function send_push_notification($token, $message, $title) {
    $response = wp_remote_post('https://exp.host/--/api/v2/push/send', array(
        'headers' => array(
            'Content-Type' => 'application/json',
        ),
        'body' => json_encode(array(
            'to' => $token,
            'title' => $title,
            'body' => $message,
        )),
    ));

    // Vous pouvez vérifier la réponse ici pour voir si la requête a réussi.
}

/// PUSH NOTIFICATION 2
if (!wp_next_scheduled('verifier_demandes_cron_job')) {
    wp_schedule_event(time(), 'every_minute', 'verifier_demandes_cron_job');
}

add_action('verifier_demandes_cron_job', 'verifier_et_mettre_a_jour_demandes');

function verifier_et_mettre_a_jour_demandes() {
    $args = array(
        'post_type' => 'demande-en-cours',
        'post_status' => 'publish',
        'meta_query' => array(
            array(
                'key'     => 'etat',
                'value'   => 'accepte',
                'compare' => '='
            ),
        ),
    );

    $demandes = get_posts($args);

    foreach ($demandes as $demande) {
        $etat = get_post_meta($demande->ID, 'etat', true);
        $notification_envoyee = get_post_meta($demande->ID, 'push-notification-envoyee', true);

        if ($etat == 'accepte' && $notification_envoyee != 'oui') {
            // Envoyer la notification
            $auteur_id = get_post_meta($demande->ID, 'auteur-demande', true);
            envoyer_notification_push($auteur_id);

            // Mettre à jour le meta field après l'envoi de la notification
            update_post_meta($demande->ID, 'push-notification-envoyee', 'oui');
        }
    }
}

function envoyer_notification_push($user_id) {
    $tokens = get_user_meta($user_id, 'push-tkn', true);

    if (!empty($tokens)) {
        foreach ($tokens as $token) {
            // Remplacer par la logique d'envoi de notification
            $response = wp_remote_post('https://exp.host/--/api/v2/push/send', array(
                'headers' => array('Content-Type' => 'application/json'),
                'body' => json_encode(array(
                    'to' => $token,
                    'title' => '(P)OSE',
                    'body' => 'Ta proposition a été acceptée !'
                ))
            ));
            // Gérer la réponse...
        }
    }
}


/// PUSH NOTIFICATION 3 - SELFIE
