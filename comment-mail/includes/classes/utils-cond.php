<?php
/**
 * Conditional Utilities
 *
 * @package utils_cond
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\utils_cond'))
	{
		/**
		 * Conditional Utilities
		 *
		 * @package utils_cond
		 * @since 14xxxx First documented version.
		 */
		class utils_cond // Conditional utilities.
		{
			/**
			 * @var plugin Plugin reference.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $plugin; // Set by constructor.

			/**
			 * @var array Global static cache.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected static $static = array();

			/**
			 * Class constructor.
			 *
			 * @since 14xxxx First documented version.
			 */
			public function __construct()
			{
				$this->plugin = plugin();
			}

			/**
			 * Current request is for a pro version preview?
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @return boolean TRUE if the current request is for a pro preview.
			 */
			public function is_pro_preview()
			{
				if(isset(static::$static[__FUNCTION__]))
					return static::$static[__FUNCTION__];

				$is = &static::$static[__FUNCTION__];

				if(!empty($_REQUEST[__NAMESPACE__.'_pro_preview']))
					return ($is = TRUE);

				return ($is = FALSE);
			}
		}
	}
}