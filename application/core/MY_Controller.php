<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Ionize
 *
 * @package		Ionize
 * @author		Ionize Dev Team
 * @license		http://ionizecms.com/doc-license
 * @link		http://ionizecms.com
 * @since		Version 0.9.0
 */

// ------------------------------------------------------------------------


/**
 * MY_Controller Class
 *
 * Extends CodeIgniter Controller
 * Basic Model loads and settings set.
 *
 */
class MY_Controller extends CI_Controller 
{
	/**
	 * Views data array
	 * Will be send to the view in case of standard output
	 * Used by $this->output
	 * @var array
	 */
	protected $template = array();

	/**
	 * Default FTL tag prefix
	 * @var string
	 */
	protected $context_tag = 'ion';


	// ------------------------------------------------------------------------


	/**
	 * Constructor
	 *
	 */
    public function __construct()
	{
		parent::__construct();

		// Check the database settings
		if ($this->test_database_config() === FALSE)
		{
			redirect(base_url().'install/');
			die();
		}

		$this->load->database();

		if ( ! $this->db->db_select())
		{
			$error =& load_class('Exceptions', 'core');
			echo $error->show_error('Database Error', 'Unable to connect to the specified database : '. $this->db->database, 'error_db');
			exit;
		}

		// Models
		$this->load->model('base_model', '', TRUE);
		$this->load->model('settings_model', '', TRUE);

		// Helpers
		$this->load->helper('file');
		$this->load->helper('trace');
		
		// Get all the website languages from DB and store them into config file "languages" key
		$languages = $this->settings_model->get_languages();
		Settings::set_languages($languages);
		if( Connect()->is('editors', TRUE))
		{
			log_message('debug', 'MY_Controller : EDITOR LOGGED IN');
			Settings::set_all_languages_online();
		}

		// Validate and set the detected language code regarding the available languages
		$detected_lang_code = config_item('detected_lang_code');
		Settings::validate_detected_lang_code();

		// Load the Connect() lib lang file
		if ($detected_lang_code != config_item('detected_lang_code'))
			$this->lang->load('connect');


		// 	Settings : google analytics string, filemanager, etc.
		//	Each setting is accessible through Settings::get('setting_name');
		Settings::set_settings_from_list($this->settings_model->get_settings(), 'name','content');
        Settings::set_settings_from_list($this->settings_model->get_lang_settings(Settings::get('detected_lang_code')), 'name','content');

		// Try to find the installer class : No access if install folder is already there
		$installer = glob(BASEPATH.'../*/class/installer'.EXT);

		// If installer class is already here, avoid site access
		if ( ! empty($installer))
		{
			// Get languages codes from available languages folder/translation file
			/*
			$languages = $this->settings_model->get_admin_langs();

			if ( ! in_array(config_item('detected_lang_code'), $languages))
				$this->config->set_item('detected_lang_code', config_item('default_admin_lang'));
			*/
			Settings::validate_detected_lang_code(TRUE);

			$this->lang->load('admin', Settings::get('detected_lang_code'));

			Theme::set_theme('admin');

			// Set the view to output
			$this->output('system/delete_installer');

			// Display the view directly
			$this->output->_display();

			// Don't do anything more
			die();
		}

		log_message('debug', __CLASS__. " Class Initialized");
	}


	// ------------------------------------------------------------------------


    /**
     * Outputs the global template regarding to the used library to do this stuff
     *
     * @param	string	The view name
     *
     */
    public function output($view)
    {
    	// Unique ID, useful for DOM Element displayed in windows.
    	$this->template['UNIQ'] = (uniqid());

    	Theme::output($view, $this->template);
    }


	// ------------------------------------------------------------------------


	/**
	 * Returns true if this is an XMLHttpRequest (ie. Javascript).
	 * 
	 * This requires a special header to be sent from the JS
	 * (usually the Javascript frameworks' Ajax/XHR methods add it automatically):
	 * 
	 * <code>
	 * X-Requested-With: XMLHttpRequest
	 * </code>
	 * 
	 * @return bool
	 */
	public function is_xhr()
	{
		return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
	}


	// ------------------------------------------------------------------------


	/**
	 * Returns true if database settings seems to be correct
	 *
	 */
	public function test_database_config()
	{
		require(APPPATH.'config/database.php');
				
		if ($db[$active_group]['hostname'] == '' || $db[$active_group]['username'] == '' || $db[$active_group]['database'] == '')
		{
			return FALSE;
		}
		return TRUE;
	}


	// ------------------------------------------------------------------------


	public function get_modules_config()
	{
		// Modules config include
		$config_files = glob(MODPATH.'*/config/config.php');

		if ( ! empty($config_files))
		{
			// Add each module config element to the main config 
			foreach($config_files as $file)
			{
				include($file);
				
				if ( isset($config))
				{
					foreach($config as $k=>$v)
						$this->config->set_item($k, $v);
	
					unset($config);
				}
			}
		}
	}


	// ------------------------------------------------------------------------


	/**
	 * Outputs XHR data
	 * If the passed data is an array or an object, it will converted to json.
	 * else, the string will be send.
	 *
	 * @param	Array	Optional. Array of data. will be converted into a JSON String
	 *
	 */
	protected function xhr_output($data)
	{
		if (is_array($data) OR is_object($data))
			$data = json_encode($data);
		
		echo($data);
		
		die();
	}
	
} 
// End MY_Controller


// ------------------------------------------------------------------------


class Base_Controller extends MY_Controller
{
	/**
	 * Constructor
	 *
	 */		
    public function __construct()
    {
        parent::__construct();

// $this->output->enable_profiler(TRUE);
		
		$this->connect = Connect::get_instance();

		// Libraries
		$this->load->library('structure');	
		$this->load->library('widget');

		// Models
		$this->load->model('menu_model', '', TRUE);

		// Modules config
		$this->get_modules_config();

		// Add path to installed modules
		require(APPPATH.'config/modules.php');
		$installed_modules = $modules;
		foreach($installed_modules as $module)
			Finder::add_path(MODPATH.$module.'/');

		// Set the current theme
		Theme::set_theme(Settings::get('theme'));

		// Theme config file : Overwrites Ionize standard config.
		if (is_file($file = Theme::get_theme_path().'config/config.php'))
		{
			include($file);
			if ( ! empty($config))
			{
				foreach($config as $k=>$v)
					$this->config->set_item($k, $v);

				unset($config);
			}
		}

		// Menus
		Settings::set('menus', $this->menu_model->get_list());


/*
 * Already done by My_Controller
 * Test and remove
 *
		$languages = $this->settings_model->get_languages();

		// Put all DB languages array to Settings

		Settings::set_languages($languages);

		// Set all languages online if conected as editor or more
		if( Connect()->is('editors', TRUE))
		{
			Settings::set_all_languages_online();
		}
*/
		// Simple languages code array, used to detect if Routers found language is in DB languages
		$online_lang_codes = array();
		foreach(Settings::get_online_languages() as $language)
			$online_lang_codes[] = $language['lang'];

		// If the lang code detected by the Router is not in the DB languages, set it to the DB default one
		/*
		if ( ! in_array(config_item('detected_lang_code'), $online_lang_codes))
		{
			Settings::set('current_lang', Settings::get_lang('default'));
			$this->config->set_item('detected_lang_code', Settings::get_lang('default'));
		}
		else
		{
			// Store the current lang code (found by Router and validated by Settings) to Settings
			Settings::set('current_lang', config_item('detected_lang_code'));
		}
		*/
		// Settings::set('current_lang', Settings::get('detected_lang_code'));


		// Set lang preferrence cookie
		$host = @str_replace('www', '', $_SERVER['HTTP_HOST']);
		
		if( ! empty($_COOKIE['ion_selected_language']))
			setcookie('ion_selected_language', '', time() - 3600, '/', $host);

		setcookie('ion_selected_language', Settings::get_lang(), time() + 3600, '/', $host);

		// Lang dependant settings for the current language : Meta, etc.
		Settings::set_settings_from_list($this->settings_model->get_lang_settings(Settings::get('detected_lang_code')), 'name','content');

		// Static translations
		$lang_files = array();
		$lang_folder = APPPATH . 'language/' . Settings::get_lang();
		// Core languages files : Including except "admin_lang.php"
		if (is_dir($lang_folder))
		{
			$lang_files = glob($lang_folder.'/*_lang.php', GLOB_BRACE);
			foreach($lang_files as $key => $lang_file)
			{
				if ($lang_file == $lang_folder.'/admin_lang.php')
				{
					unset($lang_files[$key]);
				}
			}
		}

		// Theme static translations
		$lf = glob(FCPATH.Theme::get_theme_path().'language/'.Settings::get_lang().'/*_lang.php');
		if ( !empty($lf))
			$lang_files = array_merge($lf, (Array)$lang_files);

		// Modules static translations
		foreach($installed_modules as $module)
		{
			// Languages files : Including. Can be empty
			$lang_file = MODPATH.$module.'/language/'.Settings::get_lang().'/'.strtolower($module).'_lang.php';
			array_push($lang_files, $lang_file);
		}

		// Load all modules lang files
		if ( ! empty($lang_files))
		{
			foreach($lang_files as $l)
			{
				if (is_file($l) && '.'.end(explode('.', $l)) == EXT )
				{
					include $l;
					if ( ! empty($lang))
					{
						foreach($lang as $key => $translation)
						{
							// If the term doesn't exists
							if ( ! isset($this->lang->language[$key]))
							{
								$this->lang->language[$key] = $translation;
							}
							else
							{
								// Only replace by default (theme vs. module) if the translation is empty
								if (empty($this->lang->language[$key]))
									$this->lang->language[$key] = $translation;
							}
						}
						unset($lang);
					}
				}
			}
		}

		log_message('debug', __CLASS__. " Class Initialized");

		require_once APPPATH.'libraries/Tagmanager.php';
	}
}


// ------------------------------------------------------------------------


/**
 * MY_Admin Class
 *
 * Extends MY_Controller
 *
 */
class MY_Admin extends MY_Controller
{
	/**
	 * Response message type
	 * Used by controller to send answer to request
	 * can be 'error', 'notice', 'success'
	 *
	 * @var string
	 */
	public $message_type = '';
	
	/**
	 * Response message to the user
	 * Human understandable message
	 *
	 * @var string
	 */
	public $message = '';
	
	/**
	 * Array of HTMLDomElement to update with corresponding update URL
	 * Array (
	 *		'htmlDomElement' => 'controller/method/'
	 * );
	 *
	 * @var array
	 */
	public $update = array();
	
	/**
	 * Current element ID
	 *
	 * @var int
	 */
	public $id;
	
	/**
	 * Javascript callback array
	 *
	 * @var array
	 */
	public $callback = array();

	/**
	 * Modules backedn Addons
	 *
	 * @var array
	 */
	public $modules_addons = array();


	// ------------------------------------------------------------------------


	/**
	 * Constructor
	 *
	 */		
    public function __construct()
    {
        parent::__construct();

		$this->load->helper('module_helper');	
		
		// Redirect the not authorized user to the login panel. See /application/config/connect.php
		Connect()->restrict_type_redirect = array(
					'uri' => config_item('admin_url').'/user/login',
					'flash_msg' => 'You have been denied access to %s',
					'flash_use_lang' => FALSE,
					'flash_var' => 'error');

		$this->output->enable_profiler(FALSE);

		// Connect library
		$this->connect = Connect::get_instance();
		
		// Current user		
		$this->template['current_user'] = $this->connect->get_current_user();

		// Set the admin theme as current theme
		Theme::set_theme('admin');
		
		// Set admin lang codes array
		Settings::set('admin_languages', $this->settings_model->get_admin_langs());
		Settings::set('displayed_admin_languages', explode(',', Settings::get('displayed_admin_languages')));

		// Set Router's found language code as current language
		Settings::validate_detected_lang_code(TRUE);
		// Settings::set('current_lang', Settings::get('detected_lang_code'));

		// Load the current language translations file
		$this->lang->load('admin', Settings::get_lang());

		// Modules config
		$this->get_modules_config();

		// Modules translation files
		require(APPPATH.'config/modules.php');
		$this->modules = $modules;

		foreach($this->modules as $module)
		{
			$lang_file = MODPATH.$module.'/language/'.Settings::get_lang().'/'.strtolower($module).'_lang.php';
			
			if (is_file($lang_file))
			{
				$lang = array();
				include $lang_file;
				$this->lang->language = array_merge($this->lang->language, $lang);
				unset($lang);
			}
		}
		
		// @TODO : Remove this thing from the global CMS. Not more mandatory, but necessary for compatibility with historical version
		// Available menus
		// Each menu was a root node in which you can put several pages, wich are composing a menu.
		// Was never really implemented in ionize historical version, but already used as : menus[0]...
		Settings::set('menus', config_item('menus'));

		// No cache for backend
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate");
		$this->output->set_header("Cache-Control: post-check=0, pre-check=0", FALSE);
		$this->output->set_header("Pragma: no-cache");

		log_message('debug', __CLASS__ ." Class Initialized");
	}
    

	// ------------------------------------------------------------------------


	/**
	 * Sets an error message and call the response method
	 * 
	 * @param	string		Message to the user
	 * @param	array		Additional data to put to the answer. Optional.
	 *
	 */
    public function error($message, $addon_data = NULL)
    {
    	$this->message_type = 'error';
    	$this->message = $message;
    	
    	if ( !isset($this->redirect) && !empty($_SERVER['HTTP_REFERER']))
    	{
    		$this->redirect = $_SERVER['HTTP_REFERER'];
    	}
    	
    	$this->response($addon_data);
    }

   
	// ------------------------------------------------------------------------


	/**
	 * Sets a success message and call the response method
	 * 
	 * @param	string		Message to the user
	 * @param	array		Additional data to put to the answer. Optional.
	 *
	 */
    public function success($message, $addon_data = NULL)
    {
    	$this->message_type = 'success';
    	$this->message = $message;
    	
    	$this->response($addon_data);
    }


	// ------------------------------------------------------------------------


	/**
	 * Sets a notice message and call the response method
	 * 
	 * @param	string		Message to the user
	 * @param	array		Additional data to put to the answer. Optional.
	 *
	 */
    public function notice($message, $addon_data = NULL)
    {
    	$this->message_type = 'notice';
    	$this->message = $message;
    	
    	$this->response($addon_data);
    }


	// ------------------------------------------------------------------------


    /**
     * Send an answer to the browser depending on the incoming request
     * If the request cames from XHR, sends a JSON object as response
     * else, check if redirect is defined and redirect
     *
     * @param	array	Additional data to put to the answer. Optional.
     *
     */
    public function response($addon_data = NULL)
    {
    	/* XHR request : JSON answer
    	 * Sends a JSON javascript object
    	 *  
    	 */
    	if ($this->is_xhr() === TRUE)
    	{
			// Basic JSON answser
    		$data = array (    	
				'message_type' => $this->message_type,
				'message' => $this->message,
				'update' => $this->update,
				'callback' => $this->callback
			);
			
			// Puts additional data to answer
			if ( ! empty($addon_data))
			{
				$data = array_merge($data, $addon_data);
			}
			
			// Adds element ID if isset
			if (isset($this->id) )
			{
				$data['id'] = $this->id;
			}
			echo json_encode($data);
			
			exit();
    	}
    }


	// ------------------------------------------------------------------------


	/**
     * Load the modules addons
     * Called by each Core controller method (Page, Article) which displays modules addon views
     *
     * @param	array	Optional data array to pass to the module's _addons() method
     *					for example the page data array, so the module addon can retrieve the current edited page
     * @return 	array
	 *
     */
    protected function load_modules_addons($data = array())
    {
    	return $this->_load_modules_addons($data);
    }


	// ------------------------------------------------------------------------


	private function _load_modules_addons($data)
	{
		if (get_class($this) != 'Module')
		{
			$addons = array();
		
			foreach($this->modules as $uri => $folder)
			{
				// Get the module Class modules/Module_Name/controllers/admin/module_name.php
				if (file_exists(MODPATH.$folder.'/controllers/admin/'.$uri.EXT) ) {
					if ( ! class_exists($folder) )
						require_once(MODPATH.$folder.'/controllers/admin/'.$uri.EXT);
	
					if (method_exists($folder, '_addons'))
					{
						// Module path
						$module_path = MODPATH.ucfirst($folder).'/';
			
						// Add the module path to the finder
						array_push(Finder::$paths, $module_path);
	
						$addons[$uri] = call_user_func(array($folder, '_addons'), $data);
					}
				}
			}
			
			return $addons;
		}	
	}


	// ------------------------------------------------------------------------


	/**
	 * Retrieves all the modules addons views
	 *
	 * @param	String	Parent type. 'page', 'article', etc.
	 * @param	String	Name of the placehoder
	 *
	 * @return 	string
	 */
	function get_modules_addons($type, $placeholder)
	{
		$return = '';

		foreach($this->modules_addons as $module => $type_array)
		{
			foreach($type_array as $type_key => $addon)
			{
				if ( $type_key == $type  &&  !empty($addon[$placeholder]))
				{
					foreach($addon[$placeholder] as $content)
						$return .= $content;
				}
			}
		}

		return $return;
	}


	// ------------------------------------------------------------------------


	/**
	 * Adds on addon to a core element
	 * This function must be called from _addons() module's function.
	 *
	 * @param	string
	 * @param	string		Core Element to add the addon to. Can be 'page', 'article', 'media'
	 * @param	string		Placeholder which will display the addon.
	 *  					- 'side_top' : Side Column, Top
	 *  					- 'side_bottom' : Side Column, Bottom
	 *  					- 'main_top' : Main Column, Top
	 *  					- 'main_bottom' : Main Column, Top
	 *  					- 'toolbar' : Top toolbar
	 * @param	string
	 * @param	array
	 *
	 */
	public function load_addon_view($module_name, $type, $placeholder, $view, $data = array())
	{
		$this->modules_addons[$module_name][$type][$placeholder][] = $this->load->_ci_load(array(
			'_ci_path' => MODPATH.ucfirst($module_name).'/views/'.$view.EXT, 
			'_ci_vars' => $this->load->_ci_object_to_array($data), 
			'_ci_return' => TRUE
		));
	}
}


// ------------------------------------------------------------------------


/**
 * Base Admin Module Class
 *
 * All modules Admin class must extend this class
 *
 * @author	Martin Wernstahl
 *
 */

abstract class Module_Admin extends MY_Admin
{
	/**
	 * @var CI_Controller
	 */
	protected $parent;
	
	/**
	 * Constructor
	 *
	 * @param	CI_Controller
	 *
	 */
	final public function __construct(CI_Controller $c)
	{
		log_message('debug', __CLASS__. " Class Initialized");

		$this->parent = $c;
		$this->construct();
	}


	// ------------------------------------------------------------------------


	/**
	 * The deported construct function
	 * Should be called instead of parent::__construct by inherited classes
	 *
	 * @return mixed
	 */
	abstract protected function construct();
	

	// ------------------------------------------------------------------------


	/**
	 * @param $prop
	 *
	 * @return mixed
	 *
	 */
	public function __get($prop)
	{
		if(property_exists($this->parent, $prop))
		{
			return $this->parent->$prop;
		}
		else
		{
	 		show_error(get_class($this).'->'.$prop.'() doesn\'t exist.', 206, 'Missing property');
		}
	}
	

	// ------------------------------------------------------------------------


	/**
	 * @param $method
	 * @param $param
	 *
	 * @return mixed
	 *
	 */
	public function __call($method, $param)
	{
	 	if(method_exists($this->parent, $method))
	 	{
	 		return call_user_func_array(array($this->parent, $method), $param);
	 	}
	 	else
	 	{
	 		show_error(get_class($this).'->'.$method.'() doesn\'t exist.', 206, get_class($this). ' controller Error');
	 	}
	}


	// ------------------------------------------------------------------------


	/**
	 * Outputs a simple view
	 * Available from each module
	 *
	 * @param bool $view
	 *
	 */
	public function get($view = FALSE)
	{
		$args = func_get_args();
		$args = implode('/', $args);

		$this->output($args);
	}
}

/* End of file MY_Controller.php */
/* Location: ./application/libraries/MY_Controller.php */