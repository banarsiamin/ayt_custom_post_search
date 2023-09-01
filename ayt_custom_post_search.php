<?php
/*
Plugin Name: Custom post search by text,category,tags And ACf fields
Plugin URI: https://github.com/banarsiamin/ayt_custom_post_search
description: Dynamic search having Custom post type, category, tags And ACf fields [post_with_filter_form]
Version: 1.0.0
Author: BNARSIAMIN
Text Domain: ayt_cps
Author URI: https://github.com/banarsiamin/ayt_custom_post_search
License: GPL2
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if this file is accessed directly

define('AYTCPS_VERSION', '1.0.0' );
define('AYTCPS_PATH',plugin_dir_path( __FILE__ ));
define('AYTCPS_BASENAME',dirname(plugin_basename(__FILE__)));
define('AYTCPS_URL',plugin_dir_url( __FILE__ ));
define( 'AYT_CPS', 'ayt_cps' );

add_action('plugins_loaded', 'ayt_cps_plugin_init'); 
function ayt_cps_plugin_init() {
	load_plugin_textdomain( 'ayt_cps', false, dirname(plugin_basename(__FILE__)).'/languages/' );
}
// add_action('acf/init', 'ayt_cps_acf_init');
function ayt_cps_acf_init() {
    acf_init();
}




// custom attorny search filter
function post_with_filter_form (){
    $args2 = array(
        'taxonomy' => 'location',
        'orderby' => 'name',
        'order'   => 'ASC'
     );
     $category_ats = get_categories($args2);
     $args3 = array(
        'taxonomy' => 'first_letter',
        'orderby' => 'name',
        'hide_empty' => false,
        'order'   => 'ASC'
     );
     $category_first = get_categories($args3);
    //die();
    ?>
    <div id="attorney_parent">
        <span class='fil_title'>Sort By:</span>
        <div id='selected_cat'>
            <?php
                foreach($category_ats as $category_cats) {
                    echo "<input type='checkbox' class='category_cats' data-name='".$category_cats->name."' data-slug='".$category_cats->slug."' id='loc_".$category_cats->term_id."' name='category_cats[]'  value='".$category_cats->term_id."'><label class='category_cats' >".$category_cats->name."</label>";
                }
            ?>
        </div>
        <div id="selected_titl">
            <span class='fil_title'>Filter:</span>
            <ul class="alfa_filtr">
                <li class="sf-level-0 sf-item-0 sf-option-active" data-sf-count="0" data-sf-depth="0">
                    <input class="sf-input-radio" type="radio" value="" checked name="_sft_first_letter"><label class="sf-label-radio" for="sf-input-">All</label>
                </li>
                <?php
                foreach($category_first as $category_firstt) {
                    echo '<li class="sf-level-0 sf-item-0 sf-option-active" data-sf-count="0" data-sf-depth="0">';
                    echo '<input class="sf-input-radio  _sft_first_letter" type="radio" value="'.$category_firstt->slug.'" name="_sft_first_letter"><label class="sf-label-radio" for="sf-input-">'.$category_firstt->name.'</label>';
                    echo '</li>';
                }
                ?>
            </ul>
        </div>
        <div id="selected_searh">
            <input placeholder="Search …" name="sf_search_txt" class="sf-input-text sf_search_txt" type="text">
        </div>
        
        <div id="attorney_post"></div>
    </div>
    <script>
        
    search_filtr();
    function search_filtr(){
        var catID = '';
            jQuery('.category_cats:checked').each(function () {
                // var ids = jQuery(this).val();    
                var ids = jQuery(this).attr('data-slug');    
                     
                catID += ids+',';                
            });
        var _sft_first_letter = jQuery('._sft_first_letter:checked').val();
        
        var sf_search_txt = jQuery('.sf_search_txt').val();
        jQuery.ajax({
            type: 'POST',
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            dataType: 'html',
            data: {
                action: 'attorney_post_with_filter',
                selected_cat: catID,
                _sft_first_letter: _sft_first_letter,
                sf_search_txt: sf_search_txt,
            },
            success: function(res) {
                jQuery('#attorney_post').html(res);
            }
        })
    }
        // category location
        jQuery('.category_cats, #selected_titl .alfa_filtr').change(function () {
            search_filtr();
        });
        jQuery('#selected_searh .sf-input-text').keyup(function () {
            search_filtr();
        });
        jQuery('#selected_searh .sf-input-text').keypress(function () {
            search_filtr();
        });
    </script>
    <?
}
add_shortcode( 'post_with_filter_form', 'post_with_filter_form' );

function attorney_post_with_filter () {
    ob_start();
      $location_req = isset($_REQUEST['selected_cat'])? trim($_REQUEST['selected_cat']):"";
      $selected_cat =array();
        if(!empty($location_req)){
            $loctions = explode(',',$location_req);
            foreach($loctions as $ckey=> $iids){
                if(!empty($iids)){
                    $selected_cat[]=$iids;
                }
            }
        }
        $_sft_first_letter = isset($_REQUEST['_sft_first_letter'])? trim($_REQUEST['_sft_first_letter']):"";
        $selected_txt = isset($_REQUEST['sf_search_txt'])? trim($_REQUEST['sf_search_txt']):"";
        $args = array(
            'post_type'  => 'attorneys',
            'post_status' => 'publish',
            'orderby'    => 'date',
            'order'      => 'DESC',
            'posts_per_page' => -1,
            'suppress_filters' => true
        );
        $my_args=[];
        $acf_array=array(
            'bio',
            'position',
            'location',
            'attorney_last_name',
            'attorney_first_name',
            'principal_practice_areas',
            'education',
            'email',
            'phone',
            'other_phone_number',
            'fax',
            'address',
            'other_address',
            'attorney_vcard',
            'experience',
            'bar_admissions_certifications',
            'professional_community',
            'news',
            'publications_presentations',
            'pdf_photo',
            'attorney_search_photo',
            '_attorney_social_media',
            '_hidden_relationship_title'
        );
        
        if(!empty($selected_cat) && !empty($_sft_first_letter)){
            $my_args[] = array(
                'taxonomy' => 'first_letter',
                'field' => 'slug',
                'terms' => $_sft_first_letter,
            );          
            foreach($selected_cat as $key=> $lSlug){
                $my_args[]=array(
                    'taxonomy' => 'location',
                    'field' => 'slug',
                    'terms' => "$lSlug",
                );
            }
            $args['tax_query'] = array('relation' => 'AND',$my_args);
        }else if( !empty($selected_cat) && empty($_sft_first_letter)){
            foreach($selected_cat as $key=> $lSlug){
                $my_args[]=array(
                    'taxonomy' => 'location',
                    'field' => 'slug',
                    'terms' => "$lSlug",
                );
            }
            $args['tax_query'] = array('relation' => 'AND',$my_args);
        }else if(empty($selected_cat) && !empty($_sft_first_letter)){
            $my_args[] = array(
                'taxonomy' => 'first_letter',
                'field' => 'slug',
                'terms' => $_sft_first_letter,
            );          
            $args['tax_query'] = array('relation' => 'AND',$my_args);
        }

        if(!empty($selected_txt)){
            $acff =[];
            $args['s'] = $selected_txt;   
            $loop11 = new WP_Query($args);       
            if($loop11->have_posts()){
                $loop =$loop11;
            }else{
                unset($args['s']);
                $args['meta_query']=array('relation' => 'OR');
                foreach($acf_array as $key=>$kacf){
                    $args['meta_query'][] = array(
                        'key' => "$kacf",
                        'value' => $selected_txt,
                        'compare' => 'LIKE',
                    );
                } 
                $loop = new WP_Query($args);
            }
        }else{
            $loop = new WP_Query($args);
        }
        //  if(isset($_GET['print'])){
            // echo "<pre>";
            // print_r($_REQUEST);
            // print_r($args);
            // echo "</pre>";
        // }
        
      $html = "";
      $html .="<div class='employee_loop'>";
      if($loop->have_posts()){
            while ( $loop->have_posts() ) : $loop->the_post();
                $post_id = get_the_id();
                $title = get_post_field( 'post_title', $post_id );
                $position = get_field( "position", $post_id );
                $location = get_field( "location", $post_id );
                $email = get_field( "email", $post_id );
                $phone = get_field( "phone", $post_id );
                $url   = get_permalink($post_id);
                $attorney_vcard = get_field( "attorney_vcard", $post_id );
                $attorney_search_photo = get_field( "attorney_search_photo", $post_id );
                $html.= "<div class='loop_div'>";
                $html.= "<div class='profile_img'><img src='".$attorney_search_photo."' alt='profile image'></div>";
                $html.= "<div class='profile_data'>";
                    $html.= "<div class='myname'><h6><a href='$url' style='color:#000;'>$title</a><h6></div>";
                    $html.= "<div class='position'><b>".$position."</b></div>";
                    $html.= "<div class='location'><p>".implode( ', ', $location )."</p></div>";
                    $html.= "<div class='email'>".$email."</div>";
                    $html.= "<div class='phone'>".$phone."</div>";
                    $html.= "<div class='attorney_vcard'><a target='_self' title='' href='".$attorney_vcard."'>Vcard</a></div>";
                $html.= "</div>";
                $html.= "</div>";
            endwhile;
        }else {
            $html.=  'No results found';
        }
      $html.= "</div>";
      wp_reset_query();
      echo $html;
      die();
  }
  add_action('wp_ajax_attorney_post_with_filter', 'attorney_post_with_filter');
  add_action('wp_ajax_nopriv_attorney_post_with_filter', 'attorney_post_with_filter');

add_filter('acf/update_value/name=news', 'ayt_add_titles_to_post_for_search', 10, 3);
function ayt_add_titles_to_post_for_search($value, $post_id, $field) {
    // use a new field, it does not need to be an acf field
    // first delete anything it might hold
    delete_post_meta($post_id, '_hidden_relationship_title');
    if (!empty($value)) {
        $posts = $value;
        if (!is_array($posts)) {
            $posts = array($posts);
        }
        $_hidden_relationship_title='';
        foreach ($posts as $post) {
            // add each related post's title
            // add_post_meta($post_id, '_hidden_relationship_title', get_the_title($post), false);
            $_hidden_relationship_title .=get_the_title($post);
        }
        add_post_meta($post_id, '_hidden_relationship_title', $_hidden_relationship_title, false);


    }
    return $value;
}

