<?php /*
Plugin Name: Abstract Widget
Plugin URI:
Description: Framework on which to built widgets quickly. Ideally most of this could be built into the core WP_Widget class.
Version: 1
Author: Eddie Moya
Author URL: http://eddiemoya.com

Copyright (C) 2012 Eddie Moya (eddie.moya+wp[at]gmail.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
  */

//if(!class_exists('Abstract_Widget')){
    
abstract class Abstract_Widget extends WP_Widget {

    
    
    /**
     * Root id for all widgets of this type.
     * 
     * @var string 
     */
    protected $id_base = false;
    
    /**
     * Name for this widget type.
     * 
     * @var string 
     */
    protected $name;
    
    /** 
     * Associative array passed to wp_register_sidebar_widget(). 
     * Two items can be passed here:
     * 
     * * 'description' -> This will be shown as the widget description under the widget
     * in the Widgets admin page.
     * 
     * * 'classname' -> This will be used as a classname on the wrapping element
     * when the widget is rendered on the front-end.
     *
     * @var array Optional
     */
    protected $widget_options;
    
    /**
     * Associative array passed to wp_register_widget_control().
     * 
     * The options contains the 'height' and'width. The 'height'
     * option is never used. The 'width' option is the width of the fully expanded
     * control form, but try hard to use the default width.
     *
     * @var array Optional.
     */
    protected $control_options;

    /**
     * PHP4
     */
    public function Abstract_Widget(){
        $this->__contructor();
    }
    
    /**
     * PHP5
     */
    public function __contructor() {

        $this->widget_ops = array(
            'description' => $this->description,
            'classname' => $this->classname
        );

        parent::WP_Widget($this->id_base, $this->name, $this->widget_ops);
    }
    
    /**
     * Self-registering widget mothod.
     * 
     * @author Eddie Moya
     * 
     * @param type $widget_class 
     */
    public function register_widget($widget_class = '') {
        
        if(function_exists('get_called_class')){
            $widget_class = get_called_class(); //PHP 5.3 Eliminates the need to pass the classname explicitly.
        }
        
        add_action('widgets_init', create_function( '', 'register_widget("' . $widget_class . '");' ));
    }
    
    /**
     * 
     * @abstract Is an abstract wrapper for WP_Widget::widget();
     */
    abstract function front_end( $args, $instance );

    /**
     * @abstract Is an abstract wrapper for WP_Widget::input_validation();
     */
    abstract function input_validation($new_instance, $old_instance);

    /**
     * @abstract Is an abstract wrapper for WP_Widget::update();
     */
    abstract function admin_form($instance);
    
    
    /**
     * Only exists because you cant define the existing concrete method in WP_Widget
     * as abstract. If this abstraction calss were part of core this wrapper would
     * be unecessary, the method itself would be abstract.
     * 
     * @param type $args
     * @param type $instance
     * @return type 
     */
    public function widget( $args, $instance ){
        return $this->font_end( $args, $instance );
    }
    
    /**
     * Only exists because you cant define the existing concrete method in WP_Widget
     * as abstract. If this abstraction calss were part of core this wrapper would
     * be unecessary, the method itself would be abstract.
     * 
     * @param type $new_instance
     * @param type $old_instance
     * @return type 
     */
    public function update($new_instance, $old_instance){
        return $this->input_validation($new_instance, $old_instance);
    }
    
    /**
     * Only exists because you cant define the existing concrete method in WP_Widget
     * as abstract. If this abstraction calss were part of core this wrapper would
     * be unecessary, the method itself would be abstract.
     * 
     * @param type $instance
     * @return type 
     */
    public function form($instance){
        return $this->admin_form($instance);
    }
    
    /**
     *
     * @param type $string
     * @return type 
     */
    protected function sanitize_string($string){ 
        return strip_tags(stripslashes($string));
    }
    
    /**
     *
     * @param type $bool
     * @return type 
     */
    protected function sanitize_bool($bool){
        return (!empty($bool)) ? (bool) $bool : false;
    }
    
    /**
     * 
     * @param type $fields
     * @param type $instance 
     */
    protected function form_fields($fields, $instance){
        foreach($fields as &$field){
            extract($field);
            
            $args['options'] = (isset($options)) ? $options : array();
            $this->form_field($field_id, $type, $label, $args, $instance);
        }
    }
    
    /**
     *
     * @param type $field_id
     * @param type $type
     * @param type $label
     * @param type $args
     * @param type $instance 
     */
    protected function form_field($field_id, $type, $label, $args, $instance){
        $options = $args['options'];
        
        ?><p><?php
        
        switch ($type){
            
            case 'text':
                ?>
                    <label for="<?php echo $this->get_field_id( $field_id ); ?>"><?php echo $label; ?>: </label>
                    <input id="<?php echo $this->get_field_id( $field_id ); ?>" style="<?php echo $style; ?>" class="widefat" name="<?php echo $this->get_field_name( $field_id ); ?>" value="<?php echo $instance[$field_id]; ?>" />
                <?php break;
            
            case 'select':
                $selected_option = $instance[$field_id];
  
                ?>
                    <select id="<?php echo $this->get_field_id( $field_id ); ?>" class="widefat" name="<?php echo $this->get_field_name($field_id); ?>">
                        <?php
                            foreach ( $options as $value => $label ) : 
                                $selected = ($selected_option == $value) ? 'selected="selected"' : ''; 
                                ?><option value="<?php echo $value; ?>" <?php selected($value, $instance[$field_id]) ?>><?php echo $label ?></option><?php
                            endforeach; 
                        ?>
                    </select>
                    
				<?php break;
                
            case 'textarea':
                
                $rows = (isset($options['rows'])) ? $options['rows'] : '16';
                $cols = (isset($options['cols'])) ? $options['cols'] : '20';
                
                ?>
                    <label for="<?php echo $this->get_field_id( $field_id ); ?>"><?php echo $label; ?>: </label>
                    <textarea class="widefat" rows="<?php echo $rows; ?>" cols="<?php echo $cols; ?>" id="<?php echo $this->get_field_id($field_id); ?>" name="<?php echo $this->get_field_name($field_id); ?>">
                        <?php echo $instance[$field_id]; ?>
                    </textarea>
                <?php break;
            
            case 'radio' :
                
                ?>
                    
                <?php break;
            
            case 'checkbox' :
                
                ?>
                    <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id($field_id); ?>" name="<?php echo $this->get_field_name($field_id); ?>"<?php checked( $instance[$field_id]); ?> />
                	<label for="<?php echo $this->get_field_id( $field_id ); ?>"><?php echo $label; ?> </label>
                <?php
        }
        
        ?></p><?php
    }
}

//}

class Example_Widget extends Abstract_Widget {
 
    var $name = 'Example Widget';
    var $id_base = 'example_widget';
    var $description = 'Example Widget that makes widgets easier to build';
    
    
    /**
     * Widget's front end. Wrapping markup is provided by the theme, 
     * and passed to this function via $args
     * 
     * This would normally just be called 'update()'
     * 
     * @param type $args
     * @param type $instance 
     */
	function front_end( $args, $instance ) {
		extract( $args );
        
        $title = $instance['example-text'];
        //$img = $instance['img'];
        
        echo $before_widget;
        
        echo $before_title . $title . $after_title; 
        
        //Widget output
        
        echo $after_widget;
	}

    /**
     * User input validation (input from the admin side form).
     * Return validated sanitized data, returning false rejects the update.
     * 
     * This would normally just be called 'update()'
     * 
     * @param type $new_instance
     * @param type $old_instance
     * @return type 
     */
    function input_validation($new_instance, $old_instance) {
        $instance = $old_instance;

        //Do validation;
        $instance['example-string'] = $this->sanitize_string($new_instance['example-string']);
        $instance['example-text']   = $this->sanitize_string($new_instance['example-text']);
        $instance['example-select'] = $this->sanitize_string($new_instance['example-select']);
		$instance['example-checkbox'] = $this->sanitize_bool($new_instance['example-checkbox']);
        $instance['example-checkbox-2'] = $this->sanitize_bool($new_instance['example-checkbox-2']);

        return $instance;
    }

    /**
     *
     * @param type $instance 
     */
    function admin_form($instance) {

        $defaults = array('example-text' => 'Example Text Field');
        $instance = wp_parse_args((array) $instance, $defaults);

        //Example of multiple inputs at once.
        $fields = array(
            array(
                'field_id' => 'example-text',
                'type' => 'text',
                'label' => 'Enter Title'
            ),
            array(
                'field_id' => 'example-select',
                'type' => 'select',
                'label' => 'Select an Option',
                'options' => array(
                    'option1' => 'Option 1',
                    'option2' => 'Option 2'
                    )
            )
        );

        //Builds a series of inputs based on the $fields array created above.
        $this->form_fields($fields, $instance);
        
        //Examples of input fields one at a time.
        $this->form_field('example-string', 'text', 'Enter String', array(), $instance);
        $this->form_field('example-checkbox', 'checkbox', 'Choice', array(), $instance);
        $this->form_field('example-checkbox-2', 'checkbox', 'Choice 2', array(), $instance);
    }

}

Example_Widget::register_widget("Example_Widget");