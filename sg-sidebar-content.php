<?php
/*
Plugin Name: Sidebar content
Author: Stephen Gray
Description: Allows creation of reusable sidebar content and easy inclusion on pages
Version: 0.2
*/

//custom posts
add_action('init', 'sg_sidebar_content_register');

function sg_sidebar_content_register() {

	$labels = array(
		'name' => _x('Sidebar content', 'post type general name'),
		'singular_name' => _x('Sidebar content', 'post type singular name'),
		'add_new' => _x('Add New', 'sg_sidebar_content'),
		'add_new_item' => __('Add New Sidebar content'),
		'edit_item' => __('Edit Sidebar content'),
		'new_item' => __('New Sidebar content'),
		'view_item' => __('View Sidebar content'),
		'search_items' => __('Search Sidebar content'),
		'not_found' =>  __('Nothing found'),
		'not_found_in_trash' => __('Nothing found in Trash'),
		'parent_item_colon' => ''
	);

	$args = array(
		'labels' => $labels,
		'public' => true,
		'publicly_queryable' => true,
		'show_ui' => true,
		'query_var' => true,
		'rewrite' => true,
		'capability_type' => 'post',
		'hierarchical' => false,
		'menu_position' => null,
		'exclude_from_search' => false,
		'supports' => array('title')
	  );
	register_post_type( 'sg_sidebar_content' , $args );
}
add_action('admin_init', 'register_sg_sidebar_content_meta');

//------------------------------------------------------------------------------------------------------------------------
// ADD THE META BOXES
//------------------------------------------------------------------------------------------------------------------------


function register_sg_sidebar_content_meta(){
	add_meta_box('meta_options', 'Content', 'sg_sidebar_content_meta_options', 'sg_sidebar_content', 'normal', 'low');
}

function sg_sidebar_content_meta_options(){
	global $post;

	$sg_sidebar_content_text = get_post_meta($post->ID, '_sg_sidebar_content_text', TRUE);
	echo '<p><label for="sg_sidebar_content_text">Text: </label><br />';
	echo '<textarea id="sg_sidebar_content_text" name="sg_sidebar_content_text" type="text" class="widefat">'.$sg_sidebar_content_text.'</textarea>';
	echo "</p>";

	$sg_sidebar_content_link = get_post_meta($post->ID, '_sg_sidebar_content_link', TRUE);
	echo '<p><label for="sg_sidebar_content_link">Link (including http:// or https://): </label><br />';
	echo '<input id="sg_sidebar_content_link" name="sg_sidebar_content_link" type="text" class="widefat" value="'.$sg_sidebar_content_link.'" />';
	echo "</p>";
}

add_action('save_post', 'save_sg_sidebar_contents_meta');

function save_sg_sidebar_contents_meta(){
  	global $post;
  	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
	    return $post->ID;
	}
	if($post->post_type == 'sg_sidebar_content'){
  		update_post_meta($post->ID, '_sg_sidebar_content_text', $_POST['sg_sidebar_content_text']);
  		update_post_meta($post->ID, '_sg_sidebar_content_link', $_POST['sg_sidebar_content_link']);
	}
}

function get_sg_sidebar_contents_list(){
	global $wp_query;

	$args=array('post_type'=>'sg_sidebar_content', 'nopaging' => 'true', 'orderby' => 'menu_order', 'order'  => 'ASC');
	$sg_sidebar_contents = new WP_Query($args);
	if (is_array($sg_sidebar_contents->posts)){
		$output= '<ul>';
		foreach($sg_sidebar_contents->posts as $sg_sidebar_content){
			$sg_sidebar_content_address = get_post_meta($sg_sidebar_content->ID, '_sg_sidebar_content_address', TRUE);
			$sg_sidebar_content_phone = get_post_meta($sg_sidebar_content->ID, '_sg_sidebar_content_phone', TRUE);

			$output.=  '<li>';
			if (has_post_thumbnail($sg_sidebar_content->ID)){
				$output.= '<div class="alignleft">'.get_the_post_thumbnail($sg_sidebar_content->ID,'thumbnail').'</div>';
			}
			$output.= '<div class="sg_sidebar_content_text"><h3>'.$sg_sidebar_content->post_title.'</h3>';
			if (strlen($sg_sidebar_content_address)){
				$output .= '<p class="caption">'.$sg_sidebar_content_address.'</p>';
			}
			if (strlen($sg_sidebar_content_phone)){
				$output .= '<p class="caption">'.$sg_sidebar_content_phone.'</p>';
			}
			if (strlen($sg_sidebar_content->post_content)){
				$output .= '<p>'.$sg_sidebar_content->post_content.'</p></div>';
			}
			$output.= '<br class="clear"/></li>';
		}
		$output= '</ul>';
	}

	return $output;
}

function sg_sidebar_content(){
	global $wp_query;

	$args=array('post_type'=>'sg_sidebar_content', 'nopaging' => 'true', 'orderby' => 'menu_order', 'order'  => 'ASC');
	$sg_sidebar_content = new WP_Query($args);

	return $sg_sidebar_content->posts;
}
function get_sg_sidebar_meta_box(){
	$prefix = 'sg_';

	//get sidebar content
	$sg_sidebar_contents = sg_sidebar_content();

	foreach($sg_sidebar_contents as $sg_sidebar_content){
		$sg_sidebar_fields[]=array(
				'name' => $sg_sidebar_content->post_title,
				'id' => $prefix . $sg_sidebar_content->post_name,
				'type' => 'checkbox'
		);
	}

	$sg_sidebar_box = array(
		'id' => 'sg-sidebar-meta-box',
		'title' => 'Sidebar content',
		'page' => 'page',
		'context' => 'side',
		'priority' => 'low',
		'fields' => $sg_sidebar_fields
	);
	return $sg_sidebar_box;
}

add_action('admin_menu', 'sg_sidebar_content_add_rhs_box');

// Add meta box
function sg_sidebar_content_add_rhs_box() {
	$sg_sidebar_meta_box = get_sg_sidebar_meta_box();
	add_meta_box($sg_sidebar_meta_box['id'], $sg_sidebar_meta_box['title'], 'sg_sidebar_content_show_rhs_box', $sg_sidebar_meta_box['page'], $sg_sidebar_meta_box['context'], $sg_sidebar_meta_box['priority']);
}

// Callback function to show fields in meta box
function sg_sidebar_content_show_rhs_box() {
	global $post;
	$sg_sidebar_meta_box = get_sg_sidebar_meta_box();

	// Use nonce for verification
	echo '<input type="hidden" name="sg_sidebar_content_meta_box_nonce" value="', wp_create_nonce(basename(__FILE__)), '" />';
	if (!empty($sg_sidebar_meta_box['fields'])) {
		foreach ($sg_sidebar_meta_box['fields'] as $field) {
			// get current post meta data
			$meta = get_post_meta($post->ID, $field['id'], true);

			echo '<p>';
			switch ($field['type']) {
				case 'text':
					echo '<input type="text" name="', $field['id'], '" id="', $field['id'], '" value="', $meta ? $meta : $field['std'], '" size="30" style="width:97%" />',
						'<br />', $field['desc'];
					break;
				case 'textarea':
					echo '<textarea name="', $field['id'], '" id="', $field['id'], '" cols="60" rows="4" style="width:97%">', $meta ? $meta : $field['std'], '</textarea>',
						'<br />', $field['desc'];
					break;
				case 'select':
					echo '<select name="', $field['id'], '" id="', $field['id'], '">';
					foreach ($field['options'] as $option) {
						echo '<option', $meta == $option ? ' selected="selected"' : '', '>', $option, '</option>';
					}
					echo '</select>';
					break;
				case 'radio':
					foreach ($field['options'] as $option) {
						echo '<input type="radio" name="', $field['id'], '" value="', $option['value'], '"', $meta == $option['value'] ? ' checked="checked"' : '', ' />', $option['name'];
					}
					break;
				case 'checkbox':
					echo '<input type="checkbox" name="', $field['id'], '" id="', $field['id'], '"', $meta ? ' checked="checked"' : '', ' />';
					break;
			}
			echo ' <label for="', $field['id'], '" title="'.$field['title'].'">', $field['name'], '</label>';
			echo '</p>';
		}
	}else{
		echo '<p>No sidebar content added yet. <a href="/wp-admin/post-new.php?post_type=sg_sidebar_content">Add some</a></p>';
	}
}

add_action('save_post', 'sg_sidebar_content_save_rhs_data');

// Save data from meta boxes
function sg_sidebar_content_save_rhs_data($post_id) {
	$sg_sidebar_meta_box = get_sg_sidebar_meta_box();

	// verify nonce
	if (!wp_verify_nonce($_POST['sg_sidebar_content_meta_box_nonce'], basename(__FILE__))) {
		return $post_id;
	}

	// check autosave
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return $post_id;
	}

	// check permissions
	if ('page' == $_POST['post_type']) {
		if (!current_user_can('edit_page', $post_id)) {
			return $post_id;
		}
	} elseif (!current_user_can('edit_post', $post_id)) {
		return $post_id;
	}

	foreach ($sg_sidebar_meta_box['fields'] as $field) {
		$old = get_post_meta($post_id, $field['id'], true);
		$new = $_POST[$field['id']];

		if ($new && $new != $old) {
			update_post_meta($post_id, $field['id'], $new);
		} elseif ('' == $new && $old) {
			delete_post_meta($post_id, $field['id'], $old);
		}
	}
}

// frontend display
function sg_sidebar_content_display(){
	global $post;

	//get sidebar content
	$sg_sidebar_contents = sg_sidebar_content();

	//check each for inclusion on this page
	foreach($sg_sidebar_contents as $sg_sidebar_content){
		//if post_meta == 'on', this content is for this page
		if (get_post_meta($post->ID, 'sg_'.$sg_sidebar_content->post_name, true)=='on'){
			$output.= '<h2 class="section_heading">';
            if (strlen(get_post_meta($sg_sidebar_content->ID, '_sg_sidebar_content_link', TRUE))){
                $output.= '<a href="'.get_post_meta($sg_sidebar_content->ID, '_sg_sidebar_content_link', TRUE).'">';
            }
            $output.=$sg_sidebar_content->post_title;
            if (strlen(get_post_meta($sg_sidebar_content->ID, '_sg_sidebar_content_link', TRUE))){
                $output.= '</a>';
            }
            $output.= '</h2>';
			$output.= '<div class="page_sidebar_section">';
			$output.= apply_filters('the_content', get_post_meta($sg_sidebar_content->ID, '_sg_sidebar_content_text', TRUE));
			if (strlen(get_post_meta($sg_sidebar_content->ID, '_sg_sidebar_content_link', TRUE))){
				$output.= '<p class="readmore"><a href="'.get_post_meta($sg_sidebar_content->ID, '_sg_sidebar_content_link', TRUE).'"><span>&gt;&gt;</span> Find out more</a></p>';
			}

			$output.= '</div>';
		}
	}
	echo $output;
}
?>