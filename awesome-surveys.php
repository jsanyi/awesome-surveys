<?php
/*
Plugin Name: Awesome Surveys
Plugin URI: http://www.willthewebmechanic.com/awesome-surveys
Description:
Version: 1.0
Author: Will Brubaker
Author URI: http://www.willthewebmechanic.com
License: GPLv3.0
Text Domain: awesome-surveys
Domain Path: /languages/
*/

/**
 * @package Awesome_Surveys
 *
 */
class Awesome_Surveys {

 static private $wwm_plugin_values = array(
  'name' => 'Awesome_Surveys',
  'dbversion' => '1.0',
  'supplementary' => array(
   'hire_me_html' => '<a href="http://www.willthewebmechanic.com">Hire Me</a>',
  )
 );
 public $wwm_page_link, $page_title, $menu_title, $menu_slug, $menu_link_text, $text_domain;

 /**
 * The construct runs every time plugins are loaded.  The bulk of the action and filter hooks go here
 * @since 1.0
 *
 */
 function __construct()
 {

  $this->page_title = 'Awesome Surveys';
  $this->menu_title = 'Awesome Surveys';
  $this->menu_slug = 'awesome-surveys.php';
  $this->menu_link_text = 'Awesome Surveys';
  $this->text_domain = 'awesome-surveys';

  register_activation_hook( __FILE__ , array( $this, 'init_plugin' ) );
  add_action( 'admin_menu', array( &$this, 'plugin_menu' ) );
  if ( is_admin() ) {
   add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( &$this, 'plugin_manage_link' ), 10, 4 );
  }

  add_action( 'init', array( &$this, 'init' ) );
  add_action( 'admin_enqueue_scripts', array( &$this, 'admin_enqueue_scripts' ) );
  add_action( 'wp_ajax_create_survey', array( &$this, 'create_survey' ) );
  add_action( 'wp_ajax_get_element_form', array( &$this, 'get_element_form' ) );
 }

 /**
 * stuff to do on plugin initialization
 * @return none
 * @since 1.0
 */
 function init_plugin()
 {

 }

 /**
  * Hooked into the init action, does things required on that action
  * @since  1.0
  * @author Will the Web Mechanic <will@willthewebmechanic>
  * @link http://willthewebmechanic.com
  */
 public function init()
 {

  if ( ! isset( $_SESSION ) ) {
   session_start();
  }
 }

 /**
  * enqueues the necessary css/js for the admin area
  * @since 1.0
  * @author Will the Web Mechanic <will@willthewebmechanic>
  * @link http://willthewebmechanic.com
  */
 public function admin_enqueue_scripts()
 {

  if ( strpos( $_SERVER['REQUEST_URI'], $this->menu_slug ) > 1 ) {
   wp_enqueue_script( $this->text_domain . '-admin-script', plugins_url( 'js/admin-script.js', __FILE__ ), array( 'jquery', 'jquery-ui-tabs' ), self::$wwm_plugin_values['version'] );
   wp_register_style( 'jquery-ui-lightness', plugins_url( 'css/jquery-ui.min.css', __FILE__ ), array(), '1.10.13', 'all' );
   wp_enqueue_style( $this->text_domain . '-admin-style', plugins_url( 'css/admin-style.css', __FILE__ ), array( 'jquery-ui-lightness' ), self::$wwm_plugin_values['version'], 'all' );
  }
 }

 /**
  * Adds the WtWM menu item to the admin menu & adds a submenu link to this plugin to that menu item.
  * @since  1.0
  * @author Will the Web Mechanic <will@willthewebmechanic>
  * @link http://willthewebmechanic.com
  */
 public function plugin_menu()
 {

  global $_wwm_plugins_page;
  /**
   * If, in the future, there is an enhancement or improvement,
   * allow other plugins to overwrite the panel by using
   * a higher number version.
   */
  $plugin_panel_version = 1;
  add_filter( 'wwm_plugin_links', array( &$this, 'this_plugin_link' ) );
  if ( empty( $_wwm_plugins_page ) || ( is_array( $_wwm_plugins_page ) && $plugin_panel_version > $_wwm_plugins_page[1] ) ) {
   $_wwm_plugins_page[0] = add_menu_page( 'WtWM Plugins', 'WtWM Plugins', 'manage_options', 'wwm_plugins', array( &$this, 'wwm_plugin_links' ), plugins_url( 'images/wwm_wp_menu.png', __FILE__ ), '60.9' );
   $_wwm_plugins_page[1] = $plugin_panel_version;
  }
  add_submenu_page( 'wwm_plugins', $this->page_title, $this->menu_title, 'manage_options', $this->menu_slug, array( &$this, 'plugin_options' ) );
 }

 /**
  * adds the link to this plugin's management page
  * to the $links array to be displayed on the WWM
  * plugins page:
  * @since 1.0
  * @author Will the Web Mechanic <will@willthewebmechanic>
  * @link http://willthewebmechanic.com
  * @param  array $links the array of links
  * @return array $links the filtered array of links
  */
 public function this_plugin_link( $links )
 {

  $this->wwm_page_link = $menu_page_url = menu_page_url( $this->menu_slug, 0 );
  $links[] = '<a href="' . $this->wwm_page_link . '">' . $this->menu_link_text . '</a>' . "\n";
  return $links;
 }

 /**
  * outputs an admin panel and displays links to all
  * admin pages that have been added to the $wwm_plugin_links array
  * via apply_filters
  * @since 1.0
  * @author Will the Web Mechanic <will@willthewebmechanic>
  * @link http://willthewebmechanic.com
  */
 public function wwm_plugin_links()
 {

  $wwm_plugin_links = apply_filters( 'wwm_plugin_links', $wwm_plugin_links );
  //set a version here so that everything can be overwritten by future plugins.
  //and pass it via the do_action calls
  $plugin_links_version = 1;
  echo '<div class="wrap">' . "\n";
  echo '<div id="icon-plugins" class="icon32"><br></div>' . "\n";
  echo '<h2>Will the Web Mechanic Plugins</h2>' . "\n";
  do_action( 'before_wwm_plugin_links', $plugin_links_version, $wwm_plugin_links );
  if ( ! empty( $wwm_plugin_links ) ) {
   echo '<ul>' . "\n";
   foreach ( $wwm_plugin_links as $link ) {
    echo '<li>' . $link . '</li>' . "\n";
   }
   echo '</ul>';
  }
  do_action( 'after_wwm_plugin_links', $plugin_links_version );
  echo '</div>' . "\n";
 }

 /**
  * Outputs the plugin options panel for this plugin
  * @since 1.0
  * @author Will the Web Mechanic <will@willthewebmechanic>
  * @link http://willthewebmechanic.com
  */
 public function plugin_options()
 {

  if ( ! class_exists( 'Form' ) ) {
   include_once( plugin_dir_path( __FILE__ ) . 'includes/PFBC/Form.php' );
   include_once( plugin_dir_path( __FILE__ ) . 'includes/PFBC/Overrides.php' );
  }
  $nonce = wp_create_nonce( 'create-survey' );
  $form = new FormOverrides( 'survey-manager' );
  $form->addElement( new Element_HTML( '<div class="overlay"><span class="preloader"></span></div>') );
  $form->addElement( new Element_Textbox( __( 'Survey Name:', $this->text_domain ), 'survey_name' ) );
  $form->addElement( new Element_Hidden( 'action', 'create_survey' ) );
  $form->addElement( new Element_Hidden( 'create_survey_nonce', $nonce ) );
  $form->addElement( new Element_HTML( '<div class="create_holder">') );
  $form->addElement( new Element_Button( __( 'Create Survey', $this->text_domain ), 'submit', array( 'class' => 'button-primary' ) ) );
  $form->addElement( new Element_HTML( '</div>') );
  ?>
  <div class="wrap">
   <div id="tabs">
    <ul>
     <li><a href="#create">Create Survey</a></li>
    </ul>
    <div id="create" class="half">
     <?php
      $form->render();
      $form = new FormOverrides( 'new-elements' );
      $form->addElement( new Element_HTML( '<div class="submit_holder"><div id="add-element"></div>' ) );
      $form->addElement( new Element_Button( __( 'Add Element', $this->text_domain ), 'submit', array( 'class' => 'button-primary' ) ) );
      $form->addElement( new Element_HTML( '</div>' ) );
      $form->render();
     ?>
     </div><!--#create-->
     <div id="preview" class="half">
      <h4 class="survey-name"></h4>
      <div class="survey-preview">
      <?php echo $this->survey_preview(); ?>
      </div><!--.survey-preview-->
     </div><!--#preview-->
     <div class="clear"></div>
   </div><!--#tabs-->
  </div>
  <?php
 }

 public function create_survey()
 {

  if ( ! wp_verify_nonce( $_POST['create_survey_nonce'], 'create-survey' ) || ! current_user_can( 'manage_options' ) ) {
   exit;
  }
  $data = get_option( 'wwm_awesome_surveys', array() );
  if ( isset( $data['surveys'] ) && ! empty( $data['surveys'] ) ) {
   $names = wp_list_pluck( $data['surveys'], 'name' );
   if ( in_array( $_POST['survey_name'], $names ) ) {
    echo json_encode( array( 'error' => __( 'A survey already exists named', $this->text_domain ) .  $_POST['survey_name'] ) );
    exit;
   }
  } elseif ( '' == $_POST['survey_name'] ) {
   echo json_encode( array( 'error' => __( 'Survey Name cannot be blank', $this->text_domain ) ) );
   exit;
  }
  $form = $this->render_element_selector();
  echo json_encode( array( 'form' => $form ) );
  exit;
 }

 private function dynamic_form( $args = array() )
 {

  $defaults = array(
   'type' => 'text',
   'name' => 'form_field',
   'value' => '',
   'props' => array(),
   'atts' => array(),
  );
  $args = wp_parse_args( $args, $defaults );
  $props = implode(' ', $args['props'] );
  if ( ! empty( $args['atts'] ) ) {
   foreach ( $args['atts'] as $key => $att ) {
    $atts .= $key . '="' . $att . '"';
   }
  }
  return '<input type="' . $args['type'] . '" name="' . $args['name'] . '" value="' . $args['value'] . '"' . $atts . ' ' . $props . '>';
 }

 private function render_element_selector()
 {

  $types = array( 'select...' => '', 'text' => 'Element_Textbox', 'dropdown selection' => 'Element_Select', 'radio' => 'Element_Radio', 'checkbox' => 'Element_Checkbox', 'textarea' => 'Element_Textarea' );
  $html = '<input type="hidden" name="survey_name" value="' . $_POST['survey_name'] . '">';
  $html .= '<div id="new-element-selector">Add a field to your survey.<br><label>Select Field Type:<br><select name="type" class="type-selector">';
  foreach ( $types as $type => $pfbc_method ) {
   $html .= '<option value="' . $pfbc_method . '">' . $type . '</option>';
  }
  $html .= '</select></label></div>';
  return $html;
 }

 public function get_element_form()
 {

  if ( ! current_user_can( 'manage_options' ) ) {
   exit;
  }
  $html = '<label>input a name for this ' . $_POST['text']  . ' field<br><input type="text" name="name"></label>';
  $html .= '<input type="hidden" name="type" value="' . $_POST['type'] . '">';
  $needs_options = array( 'radio', 'checkbox', 'dropdown selection' );
  if ( in_array( $_POST['text'], $needs_options ) ) {
   $html .= '<label>Number of options required?<br><input type="number" name="num_options"></label>';
  }
  echo json_encode( array( 'form' => $html, 'preview' => $preview ) );
  exit;
 }

 private function survey_preview( $args = array() )
 {

  $defaults = array(
   'id' => null,
  );

  $args = wp_parse_args( $args, $defaults );
  return null;
 }

 /**
 * adds a link on the plugins page
 * @param  array $actions     the actions array
 * @param  string $plugin_file this plugin file
 * @param  array $plugin_data plugin data
 * @param  string $context
 * @return array  $actions the action links array
 * @since 1.0
 */
 function plugin_manage_link( $actions, $plugin_file, $plugin_data, $context )
 {

  //add a link to the front of the actions list for this plugin
  return array_merge(
   array(
   'Hire Me' => self::$wwm_plugin_values['supplementary']['hire_me_html']
   ),
   $actions
  );
 }
}

$var = new Awesome_Surveys;