<?php /*
Plugin Name: Boilerplate Widget
Description: Starting point for building widgets quickly and easier
Version: 1.0
Author: Eddie Moya

/**
 * IMPORTANT: Change the class name for each widget
 */    
class Results_List_Widget extends WP_Widget {
      
    /**
     * Name for this widget type, should be human-readable - the actual title it will go by.
     * 
     * @var string [REQUIRED]
     */
    var $widget_name = 'Posts Widget: Results List';
   
    /**
     * Root id for all widgets of this type. Will be automatically generate if not set.
     * 
     * @var string [OPTIONAL]. FALSE by default.
     */
    var $id_base = 'results_list_widget';
    
    /**
     * Shows up under the widget in the admin interface
     * 
     * @var string [OPTIONAL]
     */
    private $description = 'Results List Widget';

    /**
     * CSS class used in the wrapping container for each instance of the widget on the front end.
     * 
     * @var string [OPTIONAL]
     */
    private $classname = 'results-list';
    
    /**
     * Be careful to consider PHP versions. If running PHP4 class name as the contructor instead.
     * 
     * @author Eddie Moya
     * @return void
     */
    public function __construct(){
        $widget_ops = array(
            'description' => $this->description,
            'classname' => $this->classname
        );

        parent::WP_Widget($this->id_base, $this->widget_name, $widget_ops);
    }
    
    /**
     * Self-registering widget method.
     * 
     * This can be called statically.
     * 
     * @author Eddie Moya
     * @return void
     */
    public function register_widget() {
        add_action('widgets_init', create_function( '', 'register_widget("' . __CLASS__ . '");' ));
    }
    
    /**
     * The front end of the widget. 
     * 
     * Do not call directly, this is called internally to render the widget.
     * 
     * @author [Widget Author Name]
     * 
     * @param array $args       [Required] Automatically passed by WordPress - Settings defined when registering the sidebar of a theme
     * @param array $instance   [Required] Automatically passed by WordPress - Current saved data for the widget options.
     * @return void 
     */
    public function widget( $args, $instance ){

        if(!isset($instance['query_type']) || $instance['query_type'] == 'posts'){
            global $wp_query;
            //echo "<pre>";print_r($wp_query);echo "</pre>";
             // $instance['include_question'] = false;
             // $instance['include_post'] = false;
             // $instance['include_guide'] = false;
            //$instance['paged'] = true;
            the_widget('Posts_Widget', $instance, $args);
        } else {

            echo $args['before_widget'];
            if(function_exists('get_users_by_taxonomy')){
                if(isset(get_queried_object()->term_id) && function_exists('get_partial')){
                    if(isset($_REQUEST['filter-sub-category']) || isset($_REQUEST['filter-category'])){
                        $category = (isset($_REQUEST['filter-sub-category'])) ? $_REQUEST['filter-sub-category'] : $_REQUEST['filter-category'];
                    } else {
                        $category = get_queried_object()->term_id;
                    }
                    $users = get_users_by_taxonomy('category', $category);
                    get_partial('widgets/results-list/author-archive', array('users' => $users));
                } else if ($instance['all_users']) {
                    global $wpdb;
                    $roles = new WP_Roles();
                    $roles = $roles->role_objects;
                    $experts = array();
                    foreach($roles as $role) {
                        if($role->has_cap("post_as_expert"))
                            $experts[] = trim($role->name);
                    }
                    $query = $this->get_user_role_tax_intersection(array('hide_untaxed' => false, 'roles' => $experts));
                    $users = $wpdb->get_results($wpdb->prepare($query));
                    get_partial('widgets/results-list/author-filtered-list', array('users' => $users));
                }
            }
            echo $args['after_widget'];
        }    
    }
    
    /**
     * Generates query for user results list.
     * 
     * @author Eddie Moya, Jason Corradino
     * 
     * @param array $args
     */
    function get_user_role_tax_intersection($args = array()){
        global $wpdb;

        $default_args = array(
            'hide_untaxed' => true,
            'terms'         => array(),
            'roles'         => array()
        );

        $args = array_merge($default_args, $args);

        $roles = implode("|", $args['roles']);

        $query['SELECT'] = 'SELECT DISTINCT u.ID, u.user_login, u.user_nicename, u.user_email, u.display_name, m2.meta_value as role, GROUP_CONCAT(DISTINCT m.meta_value) as terms from wp_users as u';

        $query['JOIN'] = array(
            "JOIN wp_usermeta AS m  ON u.ID = m.user_id AND m.meta_key = 'um-taxonomy-category' JOIN wp_usermeta AS m2 ON u.ID = m2.user_id AND m2.meta_value REGEXP '{$roles}'",
        );

        $query['GROUP'] = 'GROUP BY u.ID';
        $query['ORDER'] = 'ORDER BY m.meta_key';

        if($args['hide_untaxed'] == false){
            $query['JOIN'][0] = 'LEFT '. $query['JOIN'][0];
        }

        if(!empty($args['terms'])){
            $terms = implode(', ', $args['terms']);
            $query['JOIN'][0] .= "AND m.meta_value IN ($terms)";
        }

        $query['JOIN'] = implode(' ', $query['JOIN']);

        //print_r($query);
        return  implode(' ', $query);

    }
    
    
    /**
     * Data validation. 
     * 
     * Do not call directly, this is called internally to render the widget
     * 
     * @author [Widget Author Name]
     * 
     * @uses esc_attr() http://codex.wordpress.org/Function_Reference/esc_attr
     * 
     * @param array $new_instance   [Required] Automatically passed by WordPress
     * @param array $old_instance   [Required] Automatically passed by WordPress
     * @return array|bool Final result of newly input data. False if update is rejected.
     */
    public function update($new_instance, $old_instance){
        
        /* Lets inherit the existing settings */
        $instance = $old_instance;
        
        
        
        /**
         * Sanitize each option - be careful, if not all simple text fields,
         * then make use of other WordPress sanitization functions, but also
         * make use of PHP functions, and use logic to return false to reject
         * the entire update. 
         * 
         * @see http://codex.wordpress.org/Function_Reference/esc_attr
         */
        foreach($new_instance as $key => $value){
            $instance[$key] = esc_attr($value);
            
        }
        
        
        foreach($instance as $key => $value){
            if($value == 'on' && !isset($new_instance[$key])){
                unset($instance[$key]);
            }

        }
        
        return $instance;
    }
    
    /**
     * Generates the form for this widget, in the WordPress admin area.
     * 
     * The use of the helper functions form_field() and form_fields() is not
     * neccessary, and may sometimes be inhibitive or restrictive.
     * 
     * @author Eddie Moya
     * 
     * @uses wp_parse_args() http://codex.wordpress.org/Function_Reference/wp_parse_args
     * @uses self::form_field()
     * @uses self::form_fields()
     * 
     * @param array $instance [Required] Automatically passed by WordPress
     * @return void 
     */
    public function form($instance){
        $defaults = array(  
            'query_type' => 'posts',
            'widget_name' => $this->classname, 
            'filter-by' => 'category',
            'category' => '_automatic',
            'limit' => '10'
        );

        $instance = wp_parse_args((array) $instance, $defaults);
        
        
        $fields = array(
            array(
                'field_id' => 'show_filters',
                'type' => 'checkbox',
                'label' => 'Show Filtering/Sorting Dropdowns',
            ),
            array(
                'field_id' => 'query_type',
                'type' => 'select',
                'label' => 'Type of Results to Show',
                'options' => array(
                    'posts' => 'Posts',
                    'users' => 'Users'
                )
            ),             
            array(
                'field_id' => 'widget_name',
                'type' => 'hidden',
                'label' => ''
            ),
            array(
                'field_id' => 'filter-by',
                'type' => 'hidden',
                'label' => ''
            ),
            array(
                'field_id' => 'category',
                'type' => 'hidden',
                'label' => ''
            ),
            array(
                'field_id' => 'limit',
                'type' => 'hidden',
                'label' => ''
            )
        );
        
        if($instance['query_type'] == 'users') {
            $fields[] = array(
                'field_id' => 'all_users',
                'type' => 'checkbox',
                'label' => 'Filter through all users'
            );
        }

        $this->form_fields($fields, $instance);
    }
    

    /**
     * Helper function - does not need to be part of widgets, this is custom, but 
     * is helpful in generating multiple input fields for the admin form at once. 
     * 
     * This is a wrapper for the singular form_field() function.
     * 
     * @author Eddie Moya
     * 
     * @uses self::form_fields()
     * 
     * @param array $fields     [Required] Nested array of field settings
     * @param array $instance   [Required] Current instance of widget option values.
     * @return void
     */
    private function form_fields($fields, $instance, $group = false){
        
        if($group) {
            echo "<p>";
        }
            
        foreach($fields as $field){
            
            extract($field);
            $label = (!isset($label)) ? null : $label;
            $options = (!isset($options)) ? null : $options;

            $this->form_field($field_id, $type, $label, $instance, $options, $group);
        }
        
        if($group){
             echo "</p>";
        }
    }
    
    /**
     * Helper function - does not need to be part of widgets, this is custom, but 
     * is helpful in generating single input fields for the admin form at once. 
     *
     * @author Eddie Moya
     * 
     * @uses get_field_id() (No Codex Documentation)
     * @uses get_field_name() http://codex.wordpress.org/Function_Reference/get_field_name
     * 
     * @param string $field_id  [Required] This will be the CSS id for the input, but also will be used internally by wordpress to identify it. Use these in the form() function to set detaults.
     * @param string $type      [Required] The type of input to generate (text, textarea, select, checkbox]
     * @param string $label     [Required] Text to show next to input as its label.
     * @param array $instance   [Required] Current instance of widget option values. 
     * @param array $options    [Optional] Associative array of values and labels for html Option elements.
     * 
     * @return void
     */
    private function form_field($field_id, $type, $label, $instance, $options = array(), $group = false){
  
        if(!$group)
             echo "<p>";
            
        $input_value = (isset($instance[$field_id])) ? $instance[$field_id] : '';
        switch ($type){
            
            case 'text': ?>
            
                    <label for="<?php echo $this->get_field_id( $field_id ); ?>"><?php echo $label; ?>: </label>
                    <input type="text" id="<?php echo $this->get_field_id( $field_id ); ?>" class="widefat" style="<?php echo (isset($style)) ? $style : ''; ?>" class="" name="<?php echo $this->get_field_name( $field_id ); ?>" value="<?php echo $input_value ?>" />
                <?php break;
            
            case 'select': ?>
                    <label for="<?php echo $this->get_field_id( $field_id ); ?>"><?php echo $label; ?>: </label>
                    <select id="<?php echo $this->get_field_id( $field_id ); ?>" class="widefat" name="<?php echo $this->get_field_name($field_id); ?>">
                        <?php
                            foreach ( $options as $value => $label ) :  ?>
                        
                                <option value="<?php echo $value; ?>" <?php selected($value, $input_value) ?>>
                                    <?php echo $label ?>
                                </option><?php
                                
                            endforeach; 
                        ?>
                    </select>
                    
                <?php break;
                
            case 'textarea':
                
                $rows = (isset($options['rows'])) ? $options['rows'] : '16';
                $cols = (isset($options['cols'])) ? $options['cols'] : '20';
                
                ?>
                    <label for="<?php echo $this->get_field_id( $field_id ); ?>"><?php echo $label; ?>: </label>
                    <textarea class="widefat" rows="<?php echo $rows; ?>" cols="<?php echo $cols; ?>" id="<?php echo $this->get_field_id($field_id); ?>" name="<?php echo $this->get_field_name($field_id); ?>"><?php echo $input_value ?></textarea>
                <?php break;
            
            case 'radio' :
                /**
                 * Need to figure out how to automatically group radio button settings with this structure.
                 */
                ?>
                    
                <?php break;
            

            case 'hidden': ?>
                    <input id="<?php echo $this->get_field_id( $field_id ); ?>" type="hidden" style="<?php echo (isset($style)) ? $style : ''; ?>" class="widefat" name="<?php echo $this->get_field_name( $field_id ); ?>" value="<?php echo $input_value ?>" />
                <?php break;

            
            case 'checkbox' : ?>
                    <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id($field_id); ?>" name="<?php echo $this->get_field_name($field_id); ?>"<?php checked( (!empty($instance[$field_id]))); ?> />
                    <label for="<?php echo $this->get_field_id( $field_id ); ?>"><?php echo $label; ?></label>
                <?php
        }
        
        if(!$group)
             echo "</p>";
            
       
    }
}

Results_List_Widget::register_widget();