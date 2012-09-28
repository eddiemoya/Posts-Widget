<?php /*
Plugin Name: Boilerplate Widget
Description: Starting point for building widgets quickly and easier
Version: 1.0
Author: Eddie Moya

/**
 * IMPORTANT: Change the class name for each widget
 */    
class Summary_list_Widget extends WP_Widget {
      
    /**
     * Name for this widget type, should be human-readable - the actual title it will go by.
     * 
     * @var string [REQUIRED]
     */
    var $widget_name = 'Posts Widget: Summary List';
   
    /**
     * Root id for all widgets of this type. Will be automatically generate if not set.
     * 
     * @var string [OPTIONAL]. FALSE by default.
     */
    var $id_base = 'summary';
    
    /**
     * Shows up under the widget in the admin interface
     * 
     * @var string [OPTIONAL]
     */
    private $description = 'Summary List Example';

    /**
     * CSS class used in the wrapping container for each instance of the widget on the front end.
     * 
     * @var string [OPTIONAL]
     */
    private $classname = 'summary-list';
    
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

 		the_widget('Posts_Widget', $instance, $args);
        
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
     * Generates this form for this widget, in the WordPress admin area.
     * 
     * The use of the helper functions form_field() and form_fields() is not
     * necessary, and may sometimes be inhibitive or restrictive.
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
        $defaults = array(
            'title' => 'Default Value of Text Field',  
            'widget_name' => $this->classname, 
            'filter-by' => 'manual');
        
        /* Merge saved input values with default values */
        $instance = wp_parse_args((array) $instance, $defaults);  

        if (isset($instance['show_title']) || isset($instance['show_subtitle']) || isset($instance['show_share'])) {
            ?><p><strong>General Options:</strong></p><?php        
        }
        $fields = array();
        if(isset($instance['show_title'])) {
            $fields[] = array(
                'field_id' => 'widget_title',
                'type' => 'text',
                'label' => 'Title'
            );
        }
        
        if(isset($instance['show_subtitle'])) {
            $fields[] = array(
                'field_id' => 'widget_subtitle',
                'type' => 'text',
                'label' => 'Sub-Title'
            );
        }
       
        if(isset($instance['show_share'])) {
            $fields[] =  array(
                'field_id' => 'share_style',
                'type' => 'select',
                'label' => 'Share Tools Style',
                'options' => array(
                    'long' => 'Footer Bar',
                    'short' => 'Flyout'
                )
            );
        }
        $this->form_fields($fields, $instance);
        ?><p><strong>Display Options:</strong></p><?php

        

        /* Example of multiple inputs at once. */
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
                'field_id' => 'show_date',
                'type' => 'checkbox',
                'label' => 'Date<br />'
            ),
            array(
                'field_id' => 'show_share',
                'type' => 'checkbox',
                'label' => 'Share Icons'
            ),
            array(
                'field_id' => 'show_author',
                'type' => 'checkbox',
                'label' => 'Author'
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
        );
        
        
        $this->form_fields($show_options, $instance, true);
            $query_options = array(
                array(
                    'field_id' => 'filter-by',
                    'type' => 'select',
                    'label' => 'Filter By (save to update)',
                    'options' => array (
                        'manual' => 'Selected Content',
                        'none' => 'Recent Content',
                    )
                ),
                array(
                    'field_id' => 'list_style',
                    'type' => 'select',
                    'label' => 'Post Header Display',
                    'options' => array (
                        'none' => 'None',
                        'post-type' => 'Show Post Type',
                        'category' => 'Show Category'
                    )
                ),
                array(
                    'field_id' => 'limit',
                    'type' => 'select',
                    'label' => 'Number of posts',
                    'options' => range(1, 10)
                )
            );
            if(isset($instance['filter-by'])){
               if ($instance['filter-by'] == 'manual') {
                    for ($i = 1; $i < $instance['limit']+1; $i++) {
                        $query_options[] = array(
                            'field_id' => "post__in_" . ($i),
                            'type' => 'text',
                            'label' => "Post ID #" . ($i),
                        );
                    }
                } else {
                    $query_options[] = array(
                        'field_id' => 'recent-filter',
                        'type' => 'select',
                        'label' => 'Filter Type (save to update)',
                        'options' => array (
                            'category' => 'Category',
                            'post-type' => 'Post type',
                            'both' => 'Both'
                        )
                    );
                    if ($instance['recent-filter'] == "category") {
                        $cat_array[-1] = "All Categories";
                        foreach(get_terms('category') as $category) {
                            $cat_array[$category->term_id] = ucwords($category->name);
                        }
                        $query_options[] = array(
                            'field_id' => 'category',
                            'type' => 'select',
                            'label' => 'Category',
                            'options' => $cat_array
                        );
                    } else if ($instance['recent-filter'] == "post-type") {
                        $query_options[] = array(
                            'field_id' => 'filter-post-type',
                            'type' => 'select',
                            'label' => 'Post Type Filter',
                            'options' => array (
                                'post' => 'Posts',
                                'question' => 'Questions',
                                'guide' => 'Guides'
                            )
                        );
                    } else if ($instance['recent-filter'] == "both") {
                        $cat_array[-1] = "All Categories";
                        foreach(get_terms('category') as $category) {
                            $cat_array[$category->term_id] = ucwords($category->name);
                        }
                        $query_options[] = array(
                            'field_id' => 'category',
                            'type' => 'select',
                            'label' => 'Category',
                            'options' => $cat_array
                        );
                        
                        $query_options[] = array(
                            'field_id' => 'filter-post-type',
                            'type' => 'select',
                            'label' => 'Post Type Filter',
                            'options' => array (
                                'post' => 'Posts',
                                'question' => 'Questions',
                                'guide' => 'Guides'
                            )
                        );
                    }
                }
            }

            $this->form_fields($query_options, $instance);

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
            
        foreach((array)$fields as $field){
            
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
                    <input type="text" id="<?php echo $this->get_field_id( $field_id ); ?>" class="widefat" style="<?php echo (isset($style)) ? $style : ''; ?>" class="" name="<?php echo $this->get_field_name( $field_id ); ?>" value="<?php echo $input_value; ?>" />
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
                    <textarea class="widefat" rows="<?php echo $rows; ?>" cols="<?php echo $cols; ?>" id="<?php echo $this->get_field_id($field_id); ?>" name="<?php echo $this->get_field_name($field_id); ?>"><?php echo $input_value; ?></textarea>
                <?php break;
            
            case 'radio' :
                /**
                 * Need to figure out how to automatically group radio button settings with this structure.
                 */
                ?>
                    
                <?php break;
            
            case 'hidden': ?>
                    <input id="<?php echo $this->get_field_id( $field_id ); ?>" type="hidden" style="<?php echo (isset($style)) ? $style : ''; ?>" class="widefat" name="<?php echo $this->get_field_name( $field_id ); ?>" value="<?php echo $input_value; ?>" />
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

Summary_List_Widget::register_widget();