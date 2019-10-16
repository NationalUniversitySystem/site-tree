<?php
/**
 * From https://github.com/wpexplorer/page-templater
 */

/**
 * Page_Templater class
 */
class Page_Templater {
	/**
	 * Instance of this class
	 *
	 * @var boolean
	 */
	public static $instance = false;

	/**
	 * The array of templates that this plugin tracks.
	 *
	 * @var array
	 */
	protected $templates;

	/**
	 * Use class construct method to define all filters & actions
	 */
	public function __construct() {
		$this->templates = [];

		// Add a filter to the attributes metabox to inject template into the cache.
		if ( version_compare( floatval( get_bloginfo( 'version' ) ), '4.7', '<' ) ) {
			// 4.6 and older
			add_filter( 'page_attributes_dropdown_pages_args', [ $this, 'register_project_templates' ], 10, 2 );
		} else {
			// Add a filter to the wp 4.7 version attributes metabox.
			add_filter( 'theme_page_templates', [ $this, 'add_new_template' ] );
		}

		// Add a filter to the save post to inject out template into the page cache.
		add_filter( 'wp_insert_post_data', [ $this, 'register_project_templates' ], 10, 2 );

		// Add a filter to the template include to determine if the page has our
		// template assigned and return it's path.
		add_filter( 'template_include', [ $this, 'view_project_template' ] );

		// Add your templates to this array.
		$this->templates = [
			'inc/template-page-tree.php' => 'Site Page Tree',
		];

		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_template_files' ], 9999 );
	}

	/**
	 * Singleton
	 *
	 * Returns a single instance of this class.
	 */
	public static function singleton() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Adds our template to the page dropdown for v4.7+
	 *
	 * @param array $page_templates Existing page templates.
	 */
	public function add_new_template( $page_templates ) {
		$page_templates = array_merge( $page_templates, $this->templates );
		return $page_templates;
	}

	/**
	 * Adds our template to the pages cache in order to trick WordPress
	 * into thinking the template file exists where it doesn't really exist.
	 *
	 * @param array   $dropdown_args Array of arguments used to generate the pages drop-down.
	 * @param WP_Post $post          The current post.
	 */
	public function register_project_templates( $dropdown_args, $post ) {
		// Create the key used for the themes cache.
		$cache_key = 'page_templates-' . md5( get_theme_root() . '/' . get_stylesheet() );

		// Retrieve the cache list.
		// If it doesn't exist, or it's empty prepare an array.
		$templates = wp_get_theme()->get_page_templates();
		if ( empty( $templates ) ) {
			$templates = array();
		}

		// New cache, therefore remove the old one.
		wp_cache_delete( $cache_key, 'themes' );

		// Now add our template to the list of templates by merging our templates
		// with the existing templates array from the cache.
		$templates = array_merge( $templates, $this->templates );

		// Add the modified cache to allow WordPress to pick it up for listing available templates.
		wp_cache_add( $cache_key, $templates, 'themes', 1800 );

		return $dropdown_args;
	}

	/**
	 * Checks if the template is assigned to the page
	 *
	 * @param string $template The filtered template file.
	 */
	public function view_project_template( $template ) {
		// Return the search template if we're searching (instead of the template for the first result).
		if ( is_search() ) {
			return $template;
		}

		// Get global post.
		global $post;

		// Return template if post is empty.
		if ( ! $post ) {
			return $template;
		}

		$page_template = get_post_meta( $post->ID, '_wp_page_template', true );

		// Return default template if we don't have a custom one defined.
		if ( ! isset( $this->templates[ $page_template ] ) ) {
			return $template;
		}

		// Allows filtering of file path.
		$file = SITE_TREE_PATH . $page_template;

		// Just to be safe, we check if the file exist first.
		if ( file_exists( $file ) ) {
			return $file;
		} else {
			echo esc_html( $file );
		}

		// Return template.
		return $template;
	}

	/**
	 * Include our CSS when using the page template.
	 *
	 * @return void
	 */
	public function enqueue_template_files() {
		if ( is_page_template( 'inc/template-page-tree.php' ) ) {
			wp_enqueue_style( 'site-tree', SITE_TREE_URL . 'assets/css/main.min.css', [], filemtime( SITE_TREE_PATH . 'assets/css/main.min.css' ) );
		}
	}
}
