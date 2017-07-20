<?php
/*
 * Parent class for custom post types
 * Borrows heavily from https://code.tutsplus.com/articles/custom-post-type-helper-class--wp-25104
 */

class TCP_CustomPostType {
    public $slug;
    public $args;
    public $labels;
    
    /* Create new custom post type with optional $args and $labels */
    public function __construct( $name, $args = array(), $labels = array() ) {
        $this->slug = strtolower( str_replace( ' ', '_', $name ) );
        $this->args = $args;
        $this->labels = $labels;
        
        if( ! post_type_exists( $this->slug ) ) {
            add_action( 'init', array(&$this, 'register_post_type' ) );
        }
        $this->save();
    }
    
    /* Register a new custom post type */
    public function register_post_type() {
        $name = ucwords( str_replace( '_', ' ', $this->slug ) );
        $plural = $name . 's';
        
        /* Merge given array of labels and args with default settings */
        $labels = array_merge(array(
            'name'              => _x( $plural, 'post type general name'),
            'singular_name'     => _x( $name, 'post type singular name'),
            'add_new'           => _x( 'Add New', $name ),
            'add_new_item'      => __( 'Add New ' . $name ),
            'edit_item'         => __( 'Edit ' . $name ),
            'new_item'          => __( 'New ' . $name ),
            'all_items'         => __( 'All ' . $plural ),
            'view_item'         => __( 'View ' . $name ),
            'search_items'      => __( 'Search ' . $plural),
            'not_found'         => __( 'No ' . $plural . ' found.' ),
            'not_found_in_trash'=> __( 'No ' . $plural . ' found in trash.' ),
            'parent_item_colon' => '',
            'menu_name'         => $plural,
        ), $this->labels);
        
        $args = array_merge(array(
            'label'             => $plural,
            'labels'            => $labels,
            'public'            => true,
            'show_ui'           => true,
            'supports'          => array( 'title', 'editor' ),
            'show_in_nav_menus' => true,
            '_builtin'          => false,
        ), $this->args);
        
        register_post_type( $this->slug, $args ); 
    }
    
    public function add_taxonomy( $name, $args = array(), $labels = array() ) {
        if( empty( $name ) ) {
            // TODO add an error msg? 
            // Fail silently
            return;
        }
        // We need to know the post type name, so the new taxonomy can be attached to it.
        $post_type_name = $this->slug;

        // Taxonomy properties
        $taxonomy_name      = strtolower( str_replace( ' ', '_', $name ) );
        $taxonomy_labels    = $labels;
        $taxonomy_args      = $args;

        if( ! taxonomy_exists( $taxonomy_name ) ) {
            $plural = $name . 's';

            // Overwrite default labels with provided
            $labels = array_merge(array(
                'name'                  => _x( $plural, 'taxonomy general name' ),
                'singular_name'         => _x( $name, 'taxonomy singular name' ),
                'search_items'          => __( 'Search ' . $plural ),
                'all_items'             => __( 'All ' . $plural ),
                'parent_item'           => __( 'Parent ' . $name ),
                'parent_item_colon'     => __( 'Parent ' . $name . ':' ),
                'edit_item'             => __( 'Edit ' . $name ),
                'update_item'           => __( 'Update ' . $name ),
                'add_new_item'          => __( 'Add New ' . $name ),
                'new_item_name'         => __( 'New ' . $name . ' Name' ),
                'menu_name'             => __( $name ),
                ),
                // Given labels
                $taxonomy_labels
            );

            // Default arguments, overwritten with the given arguments
            $args = array_merge(array(
                'label'                 => $plural,
                'labels'                => $labels,
                'public'                => true,
                'show_ui'               => true,
                'show_in_nav_menus'     => true,
                '_builtin'              => false,
                ),
                // Given arguments
                $taxonomy_args
            );

            // Add the taxonomy to the post type
            add_action( 'init', function() use( $taxonomy_name, $post_type_name, $args ) {
                register_taxonomy( $taxonomy_name, $post_type_name, $args );
            });
        }
        else {
            /* The taxonomy already exists. Attach the existing taxonomy 
             * to the object type (post type) */
            add_action( 'init', function() use( $taxonomy_name, $post_type_name ) {
                register_taxonomy_for_object_type( $taxonomy_name, $post_type_name );
            });
        }
    }
    
    /* Add a metabox to the custom post type page */
    public function add_meta_box( $title, $fields = array(), $context = 'normal', $priority = 'default') {
        if( empty( $title ) ) {
            //fail silently
            return;
        }
        // Get post slug
        $post_type_name = $this->slug;

        // Meta variables
        $box_id         = strtolower( str_replace( ' ', '_', $title ) );
        $box_title      = $title;
        $box_context    = $context;
        $box_priority   = $priority;
 
        // Make the fields global
        global $custom_fields;
        $custom_fields[$title] = $fields;
        
        add_action( 'admin_init', function() use( $box_id, $box_title, $post_type_name, $box_context, $box_priority, $fields ) {
            add_meta_box( $box_id, $box_title, function( $post, $data ) {
                global $post;
     
                // Nonce field for some validation
                wp_nonce_field( plugin_basename( __FILE__ ), 'custom_post_type' );
     
                // Get all inputs from $data
                $custom_fields = $data['args'][0];
     
                // Get the saved values
                $meta = get_post_custom( $post->ID );
     
                // Check the array and loop through it
                if( ! empty( $custom_fields ) )
                {
                            /* Loop through $custom_fields */
                            foreach( $custom_fields as $label => $type )
                            {
                                $field_id_name  = strtolower( str_replace( ' ', '_', $data['id'] ) ) . '_' . strtolower( str_replace( ' ', '_', $label ) );
                     
                                echo '<label for="' . $field_id_name . '">' . $label . '</label><input type="text" name="custom_meta[' . $field_id_name . ']" id="' . $field_id_name . '" value="' . $meta[$field_id_name][0] . '" />';
                            }
                        }
         
                    },
                    $post_type_name,
                    $box_context,
                    $box_priority,
                    array( $fields )
                );
            }
        );
    }
    
    public function save() {
        // Need the post type name again
            $post_type_name = $this->slug;
 
            add_action( 'save_post',
                function() use( $post_type_name ) {
                // Deny the WordPress autosave function
                if( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;

                if ( ! wp_verify_nonce( $_POST['custom_post_type'], plugin_basename(__FILE__) ) ) return;
     
                global $post;
         
                if( isset( $_POST ) && isset( $post->ID ) && get_post_type( $post->ID ) == $post_type_name )
                {
                    global $custom_fields;
             
                    // Loop through each meta box
                    foreach( $custom_fields as $title => $fields )
                    {
                        // Loop through all fields
                        foreach( $fields as $label => $type )
                        {
                            $field_id_name  = strtolower( str_replace( ' ', '_', $title ) ) . '_' . strtolower( str_replace( ' ', '_', $label ) );
                     
                            update_post_meta( $post->ID, $field_id_name, $_POST['custom_meta'][$field_id_name] );
                        }
             
                    }
                }
            }
        );
    }
}