<?php /*
Plugin Name: Posts Widget
Description: Starting point for building widgets quickly and easier
Version: 1.0
Author: Eddie Moya
Author URI: http://eddiemoya.com/
 */

/**
 * IMPORTANT: Change the class name for each widget
 */    
class Posts_Widget extends WP_Widget {
      
    /**
     * Name for this widget type, should be human-readable - the actual title it will go by.
     * 
     * @var string [REQUIRED]
     */
    var $widget_name = 'Posts Widget';
   
    /**
     * Root id for all widgets of this type. Will be automatically generate if not set.
     * 
     * @var string [OPTIONAL]. FALSE by default.
     */
    var $id_base = 'posts_widget';
    
    /**
     * Shows up under the widget in the admin interface
     * 
     * @var string [OPTIONAL]
     */
    private $description = 'Posts Widget Example';

    /**
     * CSS class used in the wrapping container for each instance of the widget on the front end.
     * 
     * @var string [OPTIONAL]
     */
    private $classname = 'posts-widget';
    
    /**
     * Be careful to consider PHP versions. If running PHP4 class name as the contructor instead.
     * 
     * @author Eddie Moya
     * @return void
     */
    public function Posts_Widget(){
        $widget_ops = array(
            'description' => $this->description,
            'classname' => $this->classname
        );

        parent::WP_Widget($this->id_base, $this->widget_name, $this->widget_ops);
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
        extract($args);
        extract($instance);
        
        echo $before_title . $bp_title . $after_title;
        
        ?>
            <h3><?php echo $bp_string; ?></h3>
                
            <?php if($bp_checkbox) { ?>
                <p>Check box '<?php echo $bp_checkbox; ?>' was checked</p>
            <?php } ?>
            
            <?php if($bp_checkbox_2) { ?>
                <p>Check box '<?php echo $bp_checkbox_2; ?>' was checked</p>
            <?php } ?>
            
            <p>The chosen select option is:<?php echo $bp_select; ?></p>
            <p><?php echo $bp_textarea; ?></p>
        <?
        
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
        
        /* Setup default values for form fields - associtive array, keys are the field_id's */
        $defaults = array('title' => '', 'style' => 'general');
        
        /* Merge saved input values with default values */
        $instance = wp_parse_args((array) $instance, $defaults);
        
        $fields = array();
        
        ?><p><strong>Genreal Options:</strong></p><?php
        
        if($instance['show_title']) {
            $fields[] = array(
                'field_id' => 'display_title',
                'type' => 'text',
                'label' => 'Title'
            );
        }
        
        if($instance['show_subtitle']) {
            $fields[] = array(
                'field_id' => 'sub_title',
                'type' => 'text',
                'label' => 'Sub-Title'
            );
        }

        if($instance['show_share']) {
            $fields[] =  array(
                'field_id' => 'share_style',
                'type' => 'select',
                'label' => 'Share Tools Style',
                'options' => array(
                    'footer' => 'Footer Bar',
                    'flyout' => 'Flyout'
                )
            );
        }
        $this->form_fields($fields, $instance);
        
        
        ?><p><strong>Query Options:</strong></p><?php
        
        $limit = array(
            array(
                'field_id' => 'limit',
                'type' => 'select',
                'label' => 'Number of posts',
                'options' => range(1, 10)
            )
        );
        
        $this->form_fields($limit, $instance);
        
        ?><label>Include:</label><?php
        $query_options = array(
            array(
                'field_id' => 'include_posts',
                'type' => 'checkbox',
                'label' => 'Blog Posts'
            ),
            array(
                'field_id' => 'include_questions',
                'type' => 'checkbox',
                'label' => 'Questions'
            ),
            array(
                'field_id' => 'include_guides',
                'type' => 'checkbox',
                'label' => 'Articles'
            ),
        );
        
        $this->form_fields($query_options, $instance, true);
                
        
        
        
        
        ?><p><strong>Display Options:</strong></p><?php
        
        
        $show_options = array(
            array(
                'field_id' => 'show_title',
                'type' => 'checkbox',
                'label' => 'Title'
            ),
            array(
                'field_id' => 'show_subtitle',
                'type' => 'checkbox',
                'label' => 'Sub-Title'
            ),
            array(
                'field_id' => 'show_category',
                'type' => 'checkbox',
                'label' => 'Category'
            ),
        );
        
        
        $this->form_fields($show_options, $instance, true);
        
        
        $show_options = array(
            array(
                'field_id' => 'show_content',
                'type' => 'checkbox',
                'label' => 'Post Content'
            ),
            array(
                'field_id' => 'show_comment_count',
                'type' => 'checkbox',
                'label' => 'Response Count'
            ),
        );
        
        
        $this->form_fields($show_options, $instance, true);
        
        $show_options = array(
            array(
                'field_id' => 'show_share',
                'type' => 'checkbox',
                'label' => 'Share Icons'
            ),
        );
        
        
        $this->form_fields($show_options, $instance, true);

        
        $this->form_field('title', 'text', 'Widget Label (shows on admin only)', $instance);

        
        
//        $fields = array(
//            array(
//                'field_id' => 'title',
//                'type' => 'text',
//                'label' => 'Enter Title (leave empty for no title)'
//            ),
//            array(
//                'field_id' => 'style',
//                'type' => 'select',
//                'label' => 'Select a Style',
//                'options' => array(
//                    'general' => 'General',
//                    'featured' => 'Featured'
//                )
//            )
//        );

 


        /* Builds a series of inputs based on the $fields array created above. */
        
        
        /* Examples of input fields one at a time. */
//        
//        $this->form_field('checkbox', 'checkbox', 'Choice 1', $instance);
//        $this->form_field('checkbox_2', 'checkbox', 'Choice 2', $instance);
//        $this->form_field('textarea', 'textarea', 'Enter Lots of Text', $instance);
//        $this->form_field('checkbox_3', 'checkbox', 'Choice 3', $instance);
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
            
        foreach($fields as &$field){
            
            extract($field);
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
            
        
        switch ($type){
            
            case 'text': ?>
            
                    <label for="<?php echo $this->get_field_id( $field_id ); ?>"><?php echo $label; ?>: </label>
                    <input id="<?php echo $this->get_field_id( $field_id ); ?>" style="<?php echo $style; ?>" class="widefat" name="<?php echo $this->get_field_name( $field_id ); ?>" value="<?php echo $instance[$field_id]; ?>" />
                <?php break;
            
            case 'select': ?>
                    <label for="<?php echo $this->get_field_id( $field_id ); ?>"><?php echo $label; ?>: </label>
                    <select id="<?php echo $this->get_field_id( $field_id ); ?>" class="widefat" name="<?php echo $this->get_field_name($field_id); ?>">
                        <?php
                            foreach ( $options as $value => $label ) :  ?>
                        
                                <option value="<?php echo $value; ?>" <?php selected($value, $instance[$field_id]) ?>>
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
                    <textarea class="widefat" rows="<?php echo $rows; ?>" cols="<?php echo $cols; ?>" id="<?php echo $this->get_field_id($field_id); ?>" name="<?php echo $this->get_field_name($field_id); ?>"><?php echo $instance[$field_id]; ?></textarea>
                <?php break;
            
            case 'radio' :
                /**
                 * Need to figure out how to automatically group radio button settings with this structure.
                 */
                ?>
                    
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

Posts_Widget::register_widget();