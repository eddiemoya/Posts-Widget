<?php /*


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
     *
     * @var type 
     */
    private $width = '250';
    
    
    /**
     * Never used - does nothing.
     * @var type 
     */
    private $height = '200';
    
    
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
        
        $control_options = array(
            'height' => $this->height,
            'width' => $this->width
        );

        parent::WP_Widget($this->id_base, $this->widget_name, $widget_ops, $control_options);
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
    public function widget($args, $instance) {
        global $wp_query;
        extract($args);
        
        query_posts($this->query($instance));
        //print_pre($wp_query);
        $template = $this->get_template($instance);
       //echo "<pre>";print_r($template);echo "</pre>";
        //$before_widget = $this->add_class($before_widget, $instance['span']);
        //echo $template;
        echo $before_widget;

        include($template);

        echo $after_widget;        
        
        wp_reset_query();
    }
    
    /**
     *
     * @param type $instance
     * @return string 
     */
    function query($instance){
        $query['is_widget'] = $instance;
        
        $format = get_query_var('post_format');
        if(!empty($format)){
            $query['post_format'] = "post-format-$format";
        }

        if($instance['widget_name'] == 'summary-list') {
            if($instance['filter-by'] == 'none') {
                if($instance['recent-filter'] == 'category') {
                    $post_types = array('post', 'guide', 'question');
                    foreach ($post_types as $post_type) {
                        $query['post_type'][] = $post_type;
                    }
                    if($instance['category']!="") {
                        $query['cat'] = $instance['category'];
                    }
                } else if ($instance['recent-filter'] == 'post-type') {
                    if($instance['filter-post-type'] != "") {
                        $query['post_type'][] = $instance['filter-post-type'];
                    }
                } else if ($instance['recent-filter'] == 'both') {
                    if($instance['category'] != "") {
                        $query['cat'] = $instance['category'];
                    }
                    if($instance['filter-post-type'] != "") {
                        $query['post_type'][] = $instance['filter-post-type'];
                    }
                }
           } else {
                $post_types = array('post', 'guide', 'question');
                foreach ($post_types as $post_type) {
                    $query['post_type'][] = $post_type;
                }
                if($instance['limit'] == 1){
                    $query['p'] = $instance['post__in_1'];
                } else {
                    for ($i = 1; $i < $instance['limit'] + 1; $i++) {
                        $query['post__in'][] = $instance['post__in_' . ($i)];
                    }
                }
            }
            $query['posts_per_page'] = $instance['limit'];
        } else {
            
            //Allow chosen post types to override the natural query.
            $queried_types = get_query_var('post_type');
            $post_types = array('post', 'guide', 'question');

            if(!empty($instance['include_post']) || !empty($instance['include_question']) || !empty($instance['include_guide'])){
                foreach ($post_types as $post_type) {
                    if (isset($instance['include_' . $post_type])) {
                        $query['post_type'][] = $post_type;
                    }
                }
            } else {
                if(!empty($queried_types)){
                    $query['post_type'] = $queried_types;
                }
            }

            //$query['is_widget']

            if($instance['pagination'] != false || !isset($instance['pagination'])){
                $query['paged'] = get_query_var('paged'); 
            } else {
                $query['paged'] = 1;
            }

            if($query['paged'] == 0) {
                $query['paged'] = 1;
            }

            $limit = get_query_var('posts_per_page');

            $query['posts_per_page'] = (!empty($instance['limit'])) ? $instance['limit'] : $limit;


            $filter = $instance['filter-by'];
            if($filter != "none") {
                if($filter == 'automatic'){
                    $cat = get_query_var('cat');
                    if(!empty($cat)){
                        $filter == 'category';
                        $instance['category'] = '_automatic';
                    }
                    $user = get_query_var('author');
                    if(!empty($user)){
                        $filter == 'author';
                        $instance['author'] = '_automatic';
                    }
                }

                if($filter == 'category'){
                    $query['cat'] = $instance['category'];
                    if($query['cat'] == '_automatic' && isset(get_queried_object()->term_id)){
                        $query['cat'] = get_queried_object()->term_id;
                    } else {
                        unset($instance['category']);
                        unset($query['cat']);
                    }

                    if(isset($instance['subcategory'])){
                        $query['cat'] = $instance['subcategory'];
                    }
                }

                if($filter == 'author'){
                    $query['author'] = $instance['author'];
                    if($author == '_automatic'){
                        $query['author'] = get_query_var('author');
                    }
                }

                if ($filter == 'manual') {
                    if($instance['limit'] == 1){
                        $query['p'] = $instance['post__in_1'];
                    } else {
                        for ($i = 1; $i < $instance['limit'] + 1; $i++) {
                            $query['post__in'][] = $instance['post__in_' . ($i)];
                        }
                    }
                }
            }
        }
        
        //$q = get_queried_object();
        //echo "<pre>";print_r($query);echo "</pre>";
        return $query;
    }

    /**
     *
     * @param type $tag
     * @param type $class
     * @return type 
     */
    function add_class($string, $class) {
        return str_replace('$span', $class, $string);
        
    }
    /**
     *
     * @param type $instance
     * @param type $query 
     */
    function get_template($instance) {
        global $wp_query;
        $object = get_queried_object();
        
        if (is_author()) {
            $role = get_userdata($object->ID)->roles[0];
        }

        $directories = array();
        //$dir_patterns = apply_filters('widget_template_dirs', array('widgets/%widget-name%','widgets') );
        $dir_patterns =array('widgets/%widget-name%','widgets');

        $widget_names = explode(' ', $instance['widget_name']);

           // foreach($widget_names as $widget_name){
        foreach($dir_patterns as $pattern){
            array_push($directories, str_replace('%widget-name%', $instance['widget_name'], $pattern ) );
        }
                //echo "<pre>";print_r( str_replace('%widget-name%', $widget_name, $dir_patterns[$key]) ) ;echo "</pre>";
            //}
        

        //echo "<pre>";print_r( $widget_name );echo "</pre>";

        array_push($directories, '');

        $templates = array();
        $tax_templates = array();
        $cat_templates = array();
        $tag_templates = array();
        $author_templates = array();
        $archive_templates = array();
        $single_templates = array();
        $index_templates = array();

        if (is_archive()) {
            
            if (is_tax()) { 
                $tax_templates = array(
                    "taxonomy-{$object->taxonomy}-{$object->slug}.php",
                    "taxonomy-{$object->taxonomy}-{$object->term_id}.php",
                    "taxonomy-{$object->taxonomy}.php",
                    "taxonomy.php"
                );
            }

            if (is_category()) {
                $cat_templates = array(
                    "category-{$object->slug}.php",
                    "category-{$object->term_id}.php",
                    "category.php"
                );
            }

            if (is_tag()) {
                $tag_templates = array(
                    "tag-{$object->slug}.php",
                    "tag-{$object->term_id}.php",
                    "tag.php"
                );
            }

            if (is_author()) {
                $author_templates = array(
                    "author-{$object->user_nicename}.php",
                    "author-{$object->ID}.php",
                    "author-{$role}.php",
                    "author.php",
                );
            }
            
            $archive_templates[] = "archive-{$object->post_type}.php";
            $archive_templates[] = "archive.php";
      
        }

        if (is_single()) {
            $object = get_post(get_query_var('p'));
            $single_templates = array(
                "single-{$object->post_type}-{$object->post_name}.php",
                "single-{$object->post_type}-{$object->ID}.php",
                "single-{$object->post_type}.php",
                "single.php"
            );
        }

        //$archive_templates = "archive.php";
        $index_templates = "index.php";
        
        
        $templates = array_merge(
                $templates, 
                $this->template_set($tax_templates, $directories),
                $this->template_set($cat_templates, $directories),
                $this->template_set($tag_templates, $directories),
                $this->template_set($author_templates, $directories),
                $this->template_set($archive_templates, $directories),
                $this->template_set($single_templates, $directories),
                $this->template_set($index_templates, $directories)
                );

        //print_pre($templates);
        return get_query_template('widget', $templates);
    }
    
    /**
     *
     * @param type $templates
     * @param type $dirs
     * @return type 
     */
    function template_set($templates, $dirs){
        $set = array();
        //echo "<pre>";print_r($dirs);echo "</pre>";
        foreach((array)$templates as $template){
            foreach($dirs as $dir){
                $div = (!empty($dir) && !empty($template)) ? "/" : "";
                $set[] = $dir . $div . $template;
            }
        }
        return $set;
    }

    /**
     *
     * @param type $template
     * @param type $dirs
     * @return string 
     */
    function dirs($template, $dirs = array('')){
        $templates = array();
        foreach((array)$dirs as $dir){
            $div = (!empty($dir) && !empty($template)) ? "/" : "";
            $templates[] = $dir . $div . $template;
        }
        
        return $templates;
        
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
        
        //Handle unchecked checkboxes
        foreach($instance as $key => $value){
            if($value == 'on' && !isset($new_instance[$key])){
                $instance[$key] = '';
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
        $defaults = array(
            'title' => '', 
            'style' => 'normal',
            'limit' => 3,
            'filter-by' => 'manual',
            'show_title' => 'on',
            'show_subtitle' => 'on',
            'show_category' => 'on',
            'show_content' => 'on',
            'show_comment_count' => 'on',
            'show_share' => 'on',
            'share_style' => 'long',
            'widget_name' => $this->classname
            );
        
        /* Merge saved input values with default values */
        $instance = wp_parse_args($instance, $defaults);
       
        $this->form_field('title', 'text', 'Widget Label (shows on admin only)', $instance);
        
        $fields = array();
        
        ?><p><strong>Genreal Options:</strong></p><?php        
 
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
                    'long' => 'Long (post footer bar)',
                    'short' => 'Short (button w. flyout)'
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
                'options' => range(0, 10)
            )
        );
        
        $this->form_fields($limit, $instance);
                
        $query_options = array(
            array(
                'field_id' => 'filter-by',
                'type' => 'select',
                'label' => 'Filter By (save to update)',
                'options' => array (
                   // 'automatic' => 'Automatic',
                    'manual' => 'Manual',
                    'category' => 'Category',
                    'author' => 'Author',
                    'none' => 'No Filter (Sort by Date)'
                )
            )
        );
        if(isset($instance['filter-by'])){
            if($instance['filter-by'] == 'category') {
                
                $categories = get_categories(array('parent' => 0, 'hide_empty' => false, 'hierarchical' => true));
                foreach($categories as $cat){
                    $cats[$cat->term_id] = $cat->name; 
                }
                
                $query_options[] = array(
                    'field_id' => 'category',
                    'type' => 'select',
                    'label' => 'Category',
                    'options' => $cats 
                );
                
                if(isset($instance['category'])){
                    $categories = get_categories(array('parent' => $instance['category'], 'hide_empty' => false, 'hierarchical' => true));
                    foreach($categories as $cat){
                        $subcats[$cat->term_id] = $cat->name; 
                    }
                    if(!empty($subcats)){
                        $all = array($instance['category'] => 'All');
                        $query_options[] = array(
                            'field_id' => 'subcategory',
                            'type' => 'select',
                            'label' => 'Subcategory',
                            'options' => $all + $subcats
                        );
                    } else unset($instance['subcategory']);
                }
            }

        

            if($instance['filter-by'] == 'author'){
                $authors = get_users();
                foreach($authors as $author){
                    $users[$author->ID] = $author->user_nicename;
                }
                if(!empty($users)){
                    $query_options[] = array(
                        'field_id' => 'author',
                        'type' => 'select',
                        'label' => 'Author',
                        'options' => $users
                    );
                }
            }
            
           if ($instance['filter-by'] == 'manual') {
                for ($i = 1; $i < $instance['limit']+1; $i++) {
                    $query_options[] = array(
                        'field_id' => "post__in_" . ($i),
                        'type' => 'text',
                        'label' => "Post ID #" . ($i),
                    );
                }
            }
        }
        
        $this->form_fields($query_options, $instance);
        
        ?><label>Include:</label><?php
        $query_options = array(
            array(
                'field_id' => 'include_post',
                'type' => 'checkbox',
                'label' => 'Blog Posts'
            ),
            array(
                'field_id' => 'include_question',
                'type' => 'checkbox',
                'label' => 'Questions'
            ),
            array(
                'field_id' => 'include_guide',
                'type' => 'checkbox',
                'label' => 'Articles'
            ),
        );
        
        $this->form_fields($query_options, $instance, true);
        
        ?><p><strong>Display Options:</strong></p><?php
        
        // $show_options = array(
        //     array(
        //         'field_id' => 'style',
        //         'type'      => 'select',
        //         'label' =>  'Select a Template Style',
        //         'options' => array(
        //             'general'  => 'General',
        //             'featured' => 'Featured'
        //         )
        //     ),          
        // );
        // $this->form_fields($show_options, $instance);
        
        ?><p><label>Show:</label></p><?php
        
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
            array(
                'field_id' => 'show_thumbnail',
                'type' => 'checkbox',
                'label' => 'Featured Image'
            ),
            array(
                'field_id' => 'widget_name',
                'type' => 'hidden',
                'label' => ''
            ),
        );

        $this->form_fields($show_options, $instance, true);
        $show_options = array(
            array(
                'field_id' => 'show_date',
                'type' => 'checkbox',
                'label' => 'Date'
            ),
        );
        $this->form_fields($show_options, $instance, true);
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

Posts_Widget::register_widget();


/**
 * Needs to be moved into a functions.php file.
 */
function is_widget($property = null){
    global $wp_query;

    $return = (isset($wp_query->query_vars['is_widget'])) ? (object)$wp_query->query_vars['is_widget'] : false;
    
    if($return){
        if(isset($property)){
            if(isset($return->$property)){
                $return = $return->$property;
            } else {
                $return = false;
            }
        }
    }

    return $return;
}

