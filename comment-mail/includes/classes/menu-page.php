<?php
/**
 * Menu Pages
 *
 * @since 141111 First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\menu_page'))
	{
		/**
		 * Menu Pages
		 *
		 * @since 141111 First documented version.
		 */
		class menu_page extends abs_base
		{
			/**
			 * Class constructor.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string $which Which menu page to display?
			 */
			public function __construct($which)
			{
				parent::__construct();

				$which = $this->plugin->utils_string->trim((string)$which, '', '_');
				if($which && method_exists($this, $which.'_'))
					$this->{$which.'_'}();
			}

			/**
			 * Displays menu page.
			 *
			 * @since 141111 First documented version.
			 */
			protected function options_()
			{
				$_this             = $this;
				$form_field_args   = array(
					'ns_id_suffix'   => '-options-form',
					'ns_name_suffix' => '[save_options]',
					'class_prefix'   => 'pmp-options-form-',
				);
				$form_fields       = new form_fields($form_field_args);
				$current_value_for = function ($key) use ($_this)
				{
					if(strpos($key, 'template__') === 0)
						if(isset($_this->plugin->options[$key]))
						{
							if($_this->plugin->options[$key])
								return $_this->plugin->options[$key];

							$file             = template::option_key_to_file($key);
							$default_template = new template($file, TRUE);

							return $default_template->file_contents();
						}
					return isset($_this->plugin->options[$key]) ? $_this->plugin->options[$key] : NULL;
				};
				/* ----------------------------------------------------------------------------------------- */

				echo '<div class="'.esc_attr($this->plugin->slug.'-menu-page '.$this->plugin->slug.'-menu-page-options '.$this->plugin->slug.'-menu-page-area').'">'."\n";
				echo '   <form method="post" enctype="multipart/form-data" action="'.esc_attr($this->plugin->utils_url->page_nonce_only()).'" novalidate="novalidate">'."\n";

				echo '      '.$this->heading(__('Plugin Options', $this->plugin->text_domain), 'logo.png').
				     '      '.$this->notes(); // Heading/notifications.

				echo '      <div class="pmp-body">'."\n";

				echo '         '.$this->all_panel_togglers();

				/* ----------------------------------------------------------------------------------------- */

				echo '         <h2 class="pmp-section-heading">'.
				     '            '.__('Basic Configuration (Required)', $this->plugin->text_domain).
				     '            <small'.($this->plugin->install_time() > strtotime('-1 hour') ? ' class="pmp-hilite"' : '').'>'.
				     sprintf(__('Review these basic options and %1$s&trade; will be ready-to-go!', $this->plugin->text_domain), esc_html($this->plugin->name)).'</small>'.
				     '         </h2>';

				/* --------------------------------------------------------------------------------------------------------------------------------------------------------------------------- */

				$_panel_body = '<table style="margin:0;">'.
				               ' <tbody>'.
				               $form_fields->select_row(
					               array(
						               'label'           => sprintf(__('Enable %1$s&trade; Functionality?', $this->plugin->text_domain), esc_html($this->plugin->name)),
						               'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
						               'name'            => 'enable',
						               'current_value'   => $current_value_for('enable'),
						               'allow_arbitrary' => FALSE, // Must be one of these.
						               'options'         => array(
							               '1' => sprintf(__('Yes, enable %1$s&trade; (recommended)', $this->plugin->text_domain), esc_html($this->plugin->name)),
							               '0' => sprintf(__('No, disable %1$s&trade; temporarily', $this->plugin->text_domain), esc_html($this->plugin->name)),
						               ),
						               'notes_after'     => '<div class="pmp-note pmp-warning pmp-panel-if-disabled-show">'.
						                                    '   <p style="font-weight:bold; font-size:110%; margin:0;">'.sprintf(__('When %1$s&trade; is disabled in this way:', $this->plugin->text_domain), esc_html($this->plugin->name)).'</p>'.
						                                    '   <ul class="pmp-list-items">'.
						                                    '      <li>'.__('Comment Subscription Options (options for receiving email notifications regarding comments/replies) no longer appear on comment forms. In short, no new subscriptions are allowed. In addition, the ability to add a new subscription through any/all front-end forms is disabled too. All other front &amp; back-end functionality (including the ability for subscribers to edit and/or unsubscribe from existing subscriptions on the front-end) remains available.', $this->plugin->text_domain).'</li>'.
						                                    '      <li>'.sprintf(__('The mail queue processor will stop processing, until such time as the plugin is renabled; i.e. no more email notifications. However, mail queue injections will continue; just no queue processing. This means that when somebody posts a comment, %1$s will still check if there are any subscribers. If there are, %1$s will inject with the queue with any notifications that should be sent once queue processing is resumed. If it is desirable that any/all queued notifications NOT be processed at all upon re-enabling, you can choose to delete all existing queued notifications before doing so. See: %2$s.', $this->plugin->text_domain), esc_html($this->plugin->name), $this->plugin->utils_markup->pmp_path('Mail Queue')).'</li>'.
						                                    '   </ul>'.
						                                    '   <p><em>'.sprintf(__('<strong>Note:</strong> If you want to disable %1$s&trade; completely, please deactivate it from the plugins menu in WordPress.', $this->plugin->text_domain), esc_html($this->plugin->name)).'</em></p>'.
						                                    '</div>',
					               )).
				               ' </tbody>'.
				               '</table>';

				$_panel_body .= '<div class="pmp-panel-if-enabled-show"><hr />'.
				                ' <table>'.
				                '    <tbody>'.
				                $form_fields->select_row(
					                array(
						                'label'           => __('Allow New Subsciptions?', $this->plugin->text_domain),
						                'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
						                'field_class'     => 'no-if-enabled',
						                'name'            => 'new_subs_enable',
						                'current_value'   => $current_value_for('new_subs_enable'),
						                'allow_arbitrary' => FALSE, // Must be one of these.
						                'options'         => array(
							                '1' => __('Yes, allow new subscriptions (recommended)', $this->plugin->text_domain),
							                '0' => __('No, disallow new subscriptions temporarily', $this->plugin->text_domain),
						                ),
						                'notes_after'     => '<p>'.__('If you set this to <code>No</code> (disallow), Comment Subscription Options (options for receiving email notifications regarding comments/replies) no longer appear on comment forms. In short, no new subscriptions are allowed. In addition, the ability to add a new subscription through any/all front-end forms is disabled too. All other front &amp; back-end functionality (including the ability for subscribers to edit and/or unsubscribe from existing subscriptions on the front-end) remains available.', $this->plugin->text_domain).'</p>',
					                )).
				                '    </tbody>'.
				                ' </table>'.

				                ' <table>'.
				                '    <tbody>'.
				                $form_fields->select_row(
					                array(
						                'label'           => __('Enable Mail Queue Processing?', $this->plugin->text_domain),
						                'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
						                'field_class'     => 'no-if-enabled',
						                'name'            => 'queue_processing_enable',
						                'current_value'   => $current_value_for('queue_processing_enable'),
						                'allow_arbitrary' => FALSE, // Must be one of these.
						                'options'         => array(
							                '1' => __('Yes, enable mail queue processing (recommended)', $this->plugin->text_domain),
							                '0' => __('No, disable mail queue processing temporarily', $this->plugin->text_domain),
						                ),
						                'notes_after'     => '<p>'.sprintf(__('If you set this to <code>No</code> (disabled), all mail queue processing will stop. In short, no more email notifications will be sent. However, mail queue injections will continue; just no queue processing. This means that when somebody posts a comment, %1$s will still check if there are any subscribers. If there are, %1$s will inject with the queue with any notifications that should be sent once queue processing is resumed. If it is desirable that any/all queued notifications NOT be processed at all upon re-enabling, you can choose to delete all existing queued notifications before doing so. See: %2$s.', $this->plugin->text_domain), esc_html($this->plugin->name), $this->plugin->utils_markup->pmp_path('Mail Queue')).'</p>',
					                )).
				                '    </tbody>'.
				                ' </table>'.
				                '</div>';

				echo $this->panel(__('Enable/Disable', $this->plugin->text_domain), $_panel_body, array('open' => !$this->plugin->options['enable']));

				unset($_panel_body); // Housekeeping.

				/* ----------------------------------------------------------------------------------------- */

				$_panel_body = '<table>'.
				               '  <tbody>'.
				               $form_fields->select_row(
					               array(
						               'label'           => __('Uninstall on Plugin Deletion, or Safeguard Data?', $this->plugin->text_domain),
						               'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
						               'name'            => 'uninstall_safeguards_enable',
						               'current_value'   => $current_value_for('uninstall_safeguards_enable'),
						               'allow_arbitrary' => FALSE, // Must be one of these.
						               'options'         => array(
							               '1' => __('Safeguards on; i.e. protect my plugin options &amp; comment subscriptions (recommended)', $this->plugin->text_domain),
							               '0' => sprintf(__('Safeguards off; uninstall (completely erase) %1$s on plugin deletion', $this->plugin->text_domain), esc_html($this->plugin->name)),
						               ),
						               'notes_after'     => '<p>'.sprintf(__('By default, if you delete %1$s using the plugins menu in WordPress, no data is lost. However, if you want to completely uninstall %1$s you should turn Safeguards off, and <strong>THEN</strong> deactivate &amp; delete %1$s from the plugins menu in WordPress. This way %1$s will erase your options for the plugin, erase database tables created by the plugin, remove subscriptions, terminate CRON jobs, etc. In short, when Safeguards are off, %1$s erases itself from existence completely when you delete it.', $this->plugin->text_domain), esc_html($this->plugin->name)).'</p>',
					               )).
				               '  </tbody>'.
				               '</table>';

				echo $this->panel(__('Data Safeguards', $this->plugin->text_domain), $_panel_body, array());

				unset($_panel_body); // Housekeeping.

				/* ----------------------------------------------------------------------------------------- */

				$_panel_body = '<table>'.
				               '  <tbody>'.
				               $form_fields->input_row(
					               array(
						               'label'         => __('<code>From</code> Name:', $this->plugin->text_domain),
						               'placeholder'   => __('e.g. MySite.com', $this->plugin->text_domain),
						               'name'          => 'from_name',
						               'current_value' => $current_value_for('from_name'),
						               'notes_after'   => '<p>'.sprintf(__('All emails sent by %1$s will have a specific <code>%3$s: "<strong>Name</strong>" &lt;email&gt;</code> header, indicating that each message was sent by your site; not by a specific individual. It\'s a good idea to use something like: <code>MySite.com</code>. This name will appear beside the subject line in most email clients. Provide the <strong>name only</strong>, excluding quotes please. Examples: <code>MySite.com</code>, <code>Acme&trade;</code>, <code>MyCompany, Inc.</code>', $this->plugin->text_domain), esc_html($this->plugin->name), $this->plugin->utils_markup->x_anchor('http://en.wikipedia.org/wiki/Email#Message_header', 'Reply-To'), $this->plugin->utils_markup->x_anchor('http://en.wikipedia.org/wiki/Email#Message_header', 'From'), $this->plugin->utils_markup->x_anchor('http://en.wikipedia.org/wiki/Email#Message_header', 'Return-Path')).'</p>'
					               )).
				               '  </tbody>'.
				               '</table>';

				$_panel_body .= '<table>'.
				                '  <tbody>'.
				                $form_fields->input_row(
					                array(
						                'type'          => 'email',
						                'label'         => __('<code>From</code> Email Address:', $this->plugin->text_domain),
						                'placeholder'   => __('e.g. moderator@mysite.com', $this->plugin->text_domain),
						                'name'          => 'from_email',
						                'current_value' => $current_value_for('from_email'),
						                'notes_after'   => '<p>'.sprintf(__('All emails sent by %1$s will have a specific <code>%3$s: "Name" &lt;<strong>email</strong>&gt;</code> header, indicating that each message was sent by your site; not by a specific individual. It\'s a good idea to use something like: <code>moderator@mysite.com</code>. This email will appear beside the subject line in most email clients. Provide the <strong>email address only</strong>, excluding &lt;&gt; brackets please. Examples: <code>moderator@mysite.com</code>, <code>postmaster@example.com</code>, <code>notifications@example.com</code>', $this->plugin->text_domain), esc_html($this->plugin->name), $this->plugin->utils_markup->x_anchor('http://en.wikipedia.org/wiki/Email#Message_header', 'Reply-To'), $this->plugin->utils_markup->x_anchor('http://en.wikipedia.org/wiki/Email#Message_header', 'From'), $this->plugin->utils_markup->x_anchor('http://en.wikipedia.org/wiki/Email#Message_header', 'Return-Path')).'</p>'
					                )).
				                '  </tbody>'.
				                '</table>';

				$_panel_body .= '<hr />';

				$_panel_body .= '<table>'.
				                '  <tbody>'.
				                $form_fields->input_row(
					                array(
						                'type'          => 'email',
						                'label'         => __('<code>Reply-To</code> Email Address:', $this->plugin->text_domain),
						                'placeholder'   => __('e.g. noreply@example.com', $this->plugin->text_domain),
						                'name'          => 'reply_to_email',
						                'current_value' => $current_value_for('reply_to_email'),
						                'notes_after'   => '<p>'.sprintf(__('All emails sent by %1$s can have a specific <code>%2$s:</code> email header, which might be different from the address that %1$s messages are actually sent <code>%3$s</code>. This makes it so that if someone happens to reply to an email notification, that reply will be directed to a specific email address that you prefer. Some site owners like to use something like <code>noreply@mysite.com</code>, while others find it best to use a real email address that can monitor replies. It is a matter of preference. In the future, %1$s may support replies to comments via email, and this field could become more important at that time.', $this->plugin->text_domain), esc_html($this->plugin->name), $this->plugin->utils_markup->x_anchor('http://en.wikipedia.org/wiki/Email#Message_header', 'Reply-To'), $this->plugin->utils_markup->x_anchor('http://en.wikipedia.org/wiki/Email#Message_header', 'From'), $this->plugin->utils_markup->x_anchor('http://en.wikipedia.org/wiki/Email#Message_header', 'Return-Path')).'</p>'
					                )).
				                '  </tbody>'.
				                '</table>';

				$_panel_body .= '<hr />';

				$_panel_body .= ' <table>'.
				                '  <tbody>'.
				                $form_fields->input_row(
					                array(
						                'type'        => 'email',
						                'label'       => __('Test Mail Settings?', $this->plugin->text_domain),
						                'placeholder' => __('e.g. me@mysite.com', $this->plugin->text_domain),
						                'name'        => 'mail_test', // Not an actual option key; but the `save_options` handler picks this up.
						                'notes_after' => sprintf(__('Enter an email address to have %1$s&trade; send a test message when you save these options, and report back about any success or failure.', $this->plugin->text_domain), esc_html($this->plugin->name)),
					                )).
				                '  </tbody>'.
				                ' </table>';

				echo $this->panel(__('Email Message Headers', $this->plugin->text_domain), $_panel_body, array());

				unset($_panel_body); // Housekeeping.

				/* ----------------------------------------------------------------------------------------- */

				$_panel_body = '<table>'.
				               '  <tbody>'.
				               $form_fields->input_row(
					               array(
						               'type'          => 'email',
						               'label'         => __('Postmaster Email Address', $this->plugin->text_domain),
						               'placeholder'   => __('e.g. postmaster@example.com or abuse@example.com', $this->plugin->text_domain),
						               'name'          => 'can_spam_postmaster',
						               'current_value' => $current_value_for('can_spam_postmaster'),
						               'notes_after'   => '<p>'.sprintf(__('This is not the address that emails are sent from. This address is simply displayed at the bottom of each email sent by %1$s, as a way for people to report any abuse of the system.', $this->plugin->text_domain), esc_html($this->plugin->name)).'</p>',
					               )).
				               '  </tbody>'.
				               '</table>';

				$_panel_body .= '<table>'.
				                '  <tbody>'.
				                $form_fields->textarea_row(
					                array(
						                'label'         => sprintf(__('Mailing Address (Required for %1$s)', $this->plugin->text_domain), $this->plugin->utils_markup->x_anchor('http://en.wikipedia.org/wiki/CAN-SPAM_Act_of_2003', __('CAN-SPAM Compliance', $this->plugin->text_domain))),
						                'placeholder'   => __('e.g. 123 Somewhere Street; Somewhere, USA 99999', $this->plugin->text_domain),
						                'cm_mode'       => 'text/html', 'cm_height' => 150,
						                'name'          => 'can_spam_mailing_address',
						                'current_value' => $current_value_for('can_spam_mailing_address'),
						                'notes_before'  => '<p class="pmp-note pmp-notice">'.sprintf(__('Please be sure to provide a mailing address that %1$s can include at the bottom of every email that it sends. This is required for %2$s.', $this->plugin->text_domain), esc_html($this->plugin->name), $this->plugin->utils_markup->x_anchor('http://en.wikipedia.org/wiki/CAN-SPAM_Act_of_2003', __('CAN-SPAM Compliance', $this->plugin->text_domain))).'</p>',
						                'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Note:</strong> this needs to be provided in HTML format please. For line breaks please use: <code>&lt;br /&gt;</code>', $this->plugin->text_domain).'</p>',
					                )).
				                '  </tbody>'.
				                '</table>';

				$_panel_body .= '<table>'.
				                '  <tbody>'.
				                $form_fields->input_row(
					                array(
						                'type'          => 'url',
						                'label'         => __('Privacy Policy URL (Optional)', $this->plugin->text_domain),
						                'placeholder'   => __('e.g. http://example.com/privacy-policy/', $this->plugin->text_domain),
						                'name'          => 'can_spam_privacy_policy_url',
						                'current_value' => $current_value_for('can_spam_privacy_policy_url'),
						                'notes_after'   => '<p>'.sprintf(__('If you fill this in, %1$s will display a link to your privacy policy in strategic locations.', $this->plugin->text_domain), esc_html($this->plugin->name)).'</p>',
					                )).
				                '  </tbody>'.
				                '</table>';

				echo $this->panel(__('CAN-SPAM Compliance', $this->plugin->text_domain), $_panel_body, array());

				unset($_panel_body); // Housekeeping.

				/* ----------------------------------------------------------------------------------------- */

				$_panel_body = '<table>'.
				               '  <tbody>'.
				               $form_fields->select_row(
					               array(
						               'label'           => sprintf(__('Enable "<small><code>Powered by %1$s&trade;</code></small>" in Email Footer?', $this->plugin->text_domain), esc_html($this->plugin->name)),
						               'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
						               'field_class'     => 'no-if-enabled',
						               'name'            => 'email_footer_powered_by_enable',
						               'current_value'   => $current_value_for('email_footer_powered_by_enable'),
						               'allow_arbitrary' => FALSE,
						               'options'         => array(
							               '1' => sprintf(__('Yes, enable "powered by" note at the bottom of all emails sent by %1$s&trade;', $this->plugin->text_domain), esc_html($this->plugin->name)),
							               '0' => sprintf(__('No, disable "powered by" note', $this->plugin->text_domain), esc_html($this->plugin->name)),
						               ),
					               )).
				               '  </tbody>'.
				               '</table>';

				$_panel_body .= '<table>'.
				                '  <tbody>'.
				                $form_fields->select_row(
					                array(
						                'label'           => sprintf(__('Enable "<small><code>Powered by %1$s&trade;</code></small>" in Site Footer?', $this->plugin->text_domain), esc_html($this->plugin->name)),
						                'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
						                'field_class'     => 'no-if-enabled',
						                'name'            => 'site_footer_powered_by_enable',
						                'current_value'   => $current_value_for('site_footer_powered_by_enable'),
						                'allow_arbitrary' => FALSE,
						                'options'         => array(
							                '1' => sprintf(__('Yes, enable "powered by" note at the bottom of all pages generated by %1$s&trade;', $this->plugin->text_domain), esc_html($this->plugin->name)),
							                '0' => sprintf(__('No, disable "powered by" note', $this->plugin->text_domain), esc_html($this->plugin->name)),
						                ),
					                )).
				                '  </tbody>'.
				                '</table>';

				echo $this->panel(__('Powered by Notes', $this->plugin->text_domain), $_panel_body, array('note' => sprintf(__('Help support %1$s&trade;', $this->plugin->text_domain), esc_html($this->plugin->name))));

				unset($_panel_body); // Housekeeping.

				/* ----------------------------------------------------------------------------------------- */

				echo '         <h2 class="pmp-section-heading">'.
				     '            '.__('Advanced Configuration (All Optional)', $this->plugin->text_domain).
				     '            <small>'.__('Recommended for advanced site owners only; already pre-configured for most WP installs.', $this->plugin->text_domain).'</small>'.
				     '         </h2>';

				/* --------------------------------------------------------------------------------------------------------------------------------------------------------------------------- */

				$_panel_body = '<table>'.
				               '  <tbody>'.
				               $form_fields->select_row(
					               array(
						               'label'           => __('Enable Comment Form Subscr. Options Template?', $this->plugin->text_domain),
						               'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
						               'name'            => 'comment_form_template_enable',
						               'current_value'   => $current_value_for('comment_form_template_enable'),
						               'allow_arbitrary' => FALSE, // Must be one of these.
						               'options'         => array(
							               '1' => __('Yes, use built-in template system (recommended)', $this->plugin->text_domain),
							               '0' => __('No, disable built-in template system; I have a deep theme integration of my own', $this->plugin->text_domain),
						               ),
						               'notes_after'     => '<p>'.__('The built-in template system is quite flexible already; you can even customize the default template yourself if you want to (as seen below). Therefore, it is not recommended that you disable the default template system. This option only exists for very advanced users; i.e. those who prefer to disable the template completely in favor of their own custom implementation. If you disable the built-in template, you\'ll need to integrate HTML markup of your own into the proper location of your theme.', $this->plugin->text_domain).'</p>',
					               )).
				               '  </tbody>'.
				               '</table>';

				$_panel_body .= '<div class="pmp-panel-if-disabled-show">'.
				                '  <table>'.
				                '     <tbody>'.
				                $form_fields->select_row(
					                array(
						                'label'           => __('Also Disable Scripts Associated w/ Comment Form Subscr. Options?', $this->plugin->text_domain),
						                'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
						                'field_class'     => 'no-if-enabled',
						                'name'            => 'comment_form_scripts_enable',
						                'current_value'   => $current_value_for('comment_form_scripts_enable'),
						                'allow_arbitrary' => FALSE, // Must be one of these.
						                'options'         => array(
							                '1' => __('No, leave scripts associated w/ comment form subscr. options enabled (recommended)', $this->plugin->text_domain),
							                '0' => __('Yes, disable built-in scripts also; I have a deep theme integration of my own', $this->plugin->text_domain),
						                ),
						                'notes_after'     => '<p>'.__('For advanced use only. If you disable the built-in template system, you may also want to disable the built-in JavaScript associated w/ this template.', $this->plugin->text_domain).'</p>',
					                )).
				                '     </tbody>'.
				                '  </table>'.
				                '</div>';

				$_panel_body .= '<div class="pmp-panel-if-enabled"><hr />'.
				                '  <table>'.
				                '     <tbody>'.
				                $form_fields->textarea_row(
					                array(
						                'label'         => __('Comment Form Subscr. Options Template', $this->plugin->text_domain),
						                'placeholder'   => __('Template Content...', $this->plugin->text_domain),
						                'cm_mode'       => 'application/x-httpd-php', 'cm_height' => 250,
						                'name'          => 'template__site__comment_form__sub_ops',
						                'current_value' => $current_value_for('template__site__comment_form__sub_ops'),
						                'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for most WordPress themes; i.e. you shouldn\'t need to customize. However, if your theme is not playing well with the default; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>',
						                'notes_after'   => '<p><img src="'.esc_attr($this->plugin->utils_url->to('/client-s/images/sub-ops-ss.png')).'" class="pmp-right" style="margin-left:3em;" />'.
						                                   sprintf(__('This template is connected to one of two hooks that are expected to exist in all themes following WordPress standards. If the <code>%1$s</code> hook/filter exists, we use it (ideal). Otherwise, we use the <code>%2$s</code> action hook (most common). This is how the template is integrated into your comment form automatically. If both of these hooks are missing from your WP theme (e.g. subscr. options are not showing up no matter what you do), you will need to seek assistance from a theme developer.', $this->plugin->text_domain), $this->plugin->utils_markup->x_anchor('https://developer.wordpress.org/reference/hooks/comment_form_field_comment/', 'comment_form_field_comment'), $this->plugin->utils_markup->x_anchor('https://developer.wordpress.org/reference/hooks/comment_form/', 'comment_form')).'</p>'.
						                                   '<p class="pmp-note pmp-info pmp-max-width">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
					                )).
				                '     </tbody>'.
				                '  </table>'.

				                ' <hr />'.

				                '  <table>'.
				                '     <tbody>'.
				                $form_fields->select_row(
					                array(
						                'label'           => __('Default Subscription Option Selected for Commenters:', $this->plugin->text_domain),
						                'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
						                'name'            => 'comment_form_default_sub_type_option',
						                'current_value'   => $current_value_for('comment_form_default_sub_type_option'),
						                'allow_arbitrary' => FALSE, // Must be one of these.
						                'options'         => array(
							                ''         => __('do not subscribe', $this->plugin->text_domain),
							                'comment'  => __('replies only (recommended)', $this->plugin->text_domain),
							                'comments' => __('all comments/replies', $this->plugin->text_domain),
						                ),
						                'notes_after'     => '<p>'.__('This is the option that will be pre-selected for each commenter as the default value. You can change the wording that appears for these options by editing the template above. However, the default choice is determined systematically, based on the one that you choose here — assuming that you haven\'t dramatically altered code in the template. For most sites, the most logical default choice is: <code>replies only</code>; i.e. the commenter will only receive notifications for replies to the comment they are posting.', $this->plugin->text_domain).'</p>',
					                )).
				                '     </tbody>'.
				                '  </table>'.

				                '  <table>'.
				                '     <tbody>'.
				                $form_fields->select_row(
					                array(
						                'label'           => __('Default Subscription Delivery Option Selected for Commenters:', $this->plugin->text_domain),
						                'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
						                'name'            => 'comment_form_default_sub_deliver_option',
						                'current_value'   => $current_value_for('comment_form_default_sub_deliver_option'),
						                'allow_empty'     => FALSE, // Do not offer empty option value.
						                'allow_arbitrary' => FALSE, // Must be one of these.
						                'options'         => '%%deliver%%', // Predefined options.
						                'notes_after'     => '<p>'.__('This is the option that will be pre-selected for each commenter as the default value. You can change the wording that appears for these options by editing the template above. However, the default choice is determined systematically, based on the one that you choose here — assuming that you haven\'t dramatically altered code in the template. For most sites, the most logical default choice is: <code>asap</code> (aka: instantly); i.e. the commenter will receive instant notifications regarding replies to their comment.', $this->plugin->text_domain).'</p>',
					                )).
				                '     </tbody>'.
				                '  </table>'.
				                '</div>';

				echo $this->panel(__('Comment Form', $this->plugin->text_domain), $_panel_body, array());

				unset($_panel_body); // Housekeeping.

				/* ----------------------------------------------------------------------------------------- */

				$_panel_body = '<table style="margin:0;">'.
				               ' <tbody>'.
				               $form_fields->select_row(
					               array(
						               'label'           => __('Enable Auto-Subscribe?', $this->plugin->text_domain),
						               'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
						               'name'            => 'auto_subscribe_enable',
						               'current_value'   => $current_value_for('auto_subscribe_enable'),
						               'allow_arbitrary' => FALSE, // Must be one of these.
						               'options'         => array(
							               '1' => __('Yes, enable Auto-Subscribe (recommended)', $this->plugin->text_domain),
							               '0' => __('No, disable all Auto-Subscribe functionality', $this->plugin->text_domain),
						               ),
						               'notes_after'     => '<div class="pmp-panel-if-enabled-show">'.
						                                    '  <p style="font-weight:bold; font-size:110%; margin:0;">'.__('When Auto-Subscribe is enabled:', $this->plugin->text_domain).'</p>'.
						                                    '  <ul class="pmp-list-items">'.
						                                    '     <li>'.__('The author of a post can be subscribed to all comments/replies automatically. This way they\'ll receive email notifications w/o needing to go through the normal comment subscription process.', $this->plugin->text_domain).'</li>'.
						                                    '     <li>'.__('A list of other recipients can be added, allowing you to auto-subscribe other email addresses to every post automatically.', $this->plugin->text_domain).'</li>'.
						                                    '  </ul>'.
						                                    '</div>',
					               )).
				               ' </tbody>'.
				               '</table>';

				$_panel_body .= '<div class="pmp-panel-if-enabled"><hr />'.
				                ' <table>'.
				                '    <tbody>'.
				                $form_fields->select_row(
					                array(
						                'label'           => __('Auto-Subscribe Post Authors?', $this->plugin->text_domain),
						                'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
						                'field_class'     => 'no-if-enabled',
						                'name'            => 'auto_subscribe_post_author_enable',
						                'current_value'   => $current_value_for('auto_subscribe_post_author_enable'),
						                'allow_arbitrary' => FALSE, // Must be one of these.
						                'options'         => array(
							                '1' => __('Yes, auto-subscribe post authors (recommended)', $this->plugin->text_domain),
							                '0' => __('No, post authors will subscribe on their own', $this->plugin->text_domain),
						                ),
					                )).
				                '    </tbody>'.
				                ' </table>'.

				                ' <table>'.
				                '    <tbody>'.
				                $form_fields->input_row(
					                array(
						                'label'         => __('Auto-Subscribe the Following Email Addresses:', $this->plugin->text_domain),
						                'placeholder'   => __('"John" <john@example.com>; jane@example.com; "Susan Smith" <susan@example.com>', $this->plugin->text_domain),
						                'name'          => 'auto_subscribe_recipients',
						                'current_value' => $current_value_for('auto_subscribe_recipients'),
						                'notes_after'   => '<p>'.__('You can enter a list of other email addresses that should be auto-subscribed to all posts. This is a semicolon-delimited list of recipients; e.g. <code>"John" &lt;john@example.com&gt;; jane@example.com; "Susan Smith" &lt;susan@example.com&gt;</code>.', $this->plugin->text_domain).'</p>',
					                )).
				                '    </tbody>'.
				                ' </table>'.

				                ' <table>'.
				                '    <tbody>'.
				                $form_fields->select_row(
					                array(
						                'label'           => __('Auto-Subscribe Delivery Option:', $this->plugin->text_domain),
						                'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
						                'name'            => 'auto_subscribe_deliver',
						                'current_value'   => $current_value_for('auto_subscribe_deliver'),
						                'allow_empty'     => FALSE, // Do not offer empty option value.
						                'allow_arbitrary' => FALSE, // Must be one of these.
						                'options'         => '%%deliver%%', // Predefined options.
						                'notes_after'     => '<p>'.__('Whenever someone is auto-subscribed, this is the delivery option that will be used. Any value that is not <code>asap</code> results in a digest instead of instant notifications.', $this->plugin->text_domain).'</p>',
					                )).
				                '    </tbody>'.
				                ' </table>'.

				                ' <hr />'.

				                ' <table>'.
				                '    <tbody>'.
				                $form_fields->input_row(
					                array(
						                'label'         => __('Auto-Subscribe Post Types (Comma-Delimited):', $this->plugin->text_domain),
						                'placeholder'   => __('e.g. post,page,portfolio,gallery', $this->plugin->text_domain),
						                'name'          => 'auto_subscribe_post_types',
						                'current_value' => $current_value_for('auto_subscribe_post_types'),
						                'notes_after'   => '<p>'.sprintf(__('These are the %2$s that will trigger automatic subscriptions; i.e. %1$s will only auto-subscribe people to these types of posts. The default list is adequate for most sites. However, if you have other %2$s enabled by a theme/plugin, you might wish to include those here. e.g. <code>post,page,portfolio,gallery</code>; where <code>portfolio,gallery</code> might be two %3$s that you add to the default list, if applicable.', $this->plugin->text_domain), esc_html($this->plugin->name), $this->plugin->utils_markup->x_anchor('http://codex.wordpress.org/Post_Types', __('Post Types', $this->plugin->text_domain)), $this->plugin->utils_markup->x_anchor('http://codex.wordpress.org/Post_Types#Custom_Post_Types', __('Custom Post Types', $this->plugin->text_domain))).'</p>',
					                )).
				                '    </tbody>'.
				                ' </table>'.
				                '</div>';

				echo $this->panel(__('Auto-Subscribe Settings', $this->plugin->text_domain), $_panel_body, array());

				unset($_panel_body); // Housekeeping.

				/* ----------------------------------------------------------------------------------------- */

				$_panel_body = '<table style="margin:0;">'.
				               ' <tbody>'.
				               $form_fields->select_row(
					               array(
						               'label'           => __('Auto-Confirm Everyone?', $this->plugin->text_domain),
						               'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
						               'name'            => 'auto_confirm_force_enable',
						               'current_value'   => $current_value_for('auto_confirm_force_enable'),
						               'allow_arbitrary' => FALSE, // Must be one of these.
						               'options'         => array(
							               '0' => __('No, require subscriptions to be confirmed via email (highly recommended)', $this->plugin->text_domain),
							               '1' => __('Yes, automatically auto-confirm everyone; i.e. never ask for email confirmation', $this->plugin->text_domain),
						               ),
						               'notes_after'     => '<div class="pmp-panel-if-enabled-show">'.
						                                    '   <p style="font-weight:bold; font-size:110%; margin:0;">'.__('When Auto-Confirm Everyone is enabled:', $this->plugin->text_domain).'</p>'.
						                                    '   <ul class="pmp-list-items">'.
						                                    '      <li>'.sprintf(__('Nobody will be required to confirm a subscription. For instance, when someone leaves a comment and chooses to be subscribed (with whatever email address they\'ve entered), that email address will be added to the list w/o getting confirmation from the real owner of that address. This scenario changes slightly if you %1$s before leaving a comment, via WordPress Discussion Settings. If that\'s the case, then depending on the way your users register (i.e. if they are required to verify their email address in some way), this option might be feasible. That said, in 99%% of all cases this option is NOT recommended. If you enable auto-confirmation for everyone, please take extreme caution.', $this->plugin->text_domain), $this->plugin->utils_markup->x_anchor(admin_url('/options-discussion.php'), __('require users to be logged-in', $this->plugin->text_domain))).'</li>'.
						                                    '      <li>'.sprintf(__('In addition to security issues associated w/ auto-confirming everyone automatically; if you enable this behavior it will also have the negative side-effect of making it slightly more difficult for users to view a summary of their existing subscriptions; i.e. they won\'t get an encrypted <code>%2$s</code> cookie right away via email confirmation, as would normally occur. This is how %1$s identifies a user when they are not currently logged into the site (typical w/ commenters). Therefore, if Auto-Confirm Everyone is enabled, the only way users can view a summary of their subscriptions, is if:', $this->plugin->text_domain), esc_html($this->plugin->name), esc_html(__NAMESPACE__.'_sub_email')).
						                                    '        <ul>'.
						                                    '           <li>'.__('They\'re a logged-in user, and you\'ve enabled "All WP Users Confirm Email" below; i.e. a logged-in user\'s email address can be trusted — known to be confirmed already.', $this->plugin->text_domain).'</li>'.
						                                    '           <li>'.sprintf(__('Or, if they click a link to manage their subscription after having received an email notification regarding a new comment. It is at this point that an auto-confirmed subscriber will finally get their encrypted <code>%1$s</code> cookie. That said, it\'s important to note that <em>anyone</em> can manage their subscriptions after receiving an email notification regarding a new comment. In every email notification there is a "Manage My Subscriptions" link provided for them. This link provides access to subscription management through a secret subscription key; not dependent upon a cookie.', $this->plugin->text_domain), esc_html(__NAMESPACE__.'_sub_email')).'</li>'.
						                                    '        </ul>'.
						                                    '     </li>'.
						                                    '   </ul>'.
						                                    '</div>',
					               )).
				               ' </tbody>'.
				               '</table>';

				$_panel_body .= '<div class="pmp-panel-if-disabled-show"><hr />'.
				                ' <table>'.
				                '  <tbody>'.
				                $form_fields->select_row(
					                array(
						                'label'           => __('Auto-Confirm if Already Subscribed w/ the Same IP Address?', $this->plugin->text_domain),
						                'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
						                'field_class'     => 'no-if-enabled',
						                'name'            => 'auto_confirm_if_already_subscribed_u0ip_enable',
						                'current_value'   => $current_value_for('auto_confirm_if_already_subscribed_u0ip_enable'),
						                'allow_arbitrary' => FALSE, // Must be one of these.
						                'options'         => array(
							                '0' => __('No, do not trust a commenter\'s IP address; always request email confirmation (safest choice)', $this->plugin->text_domain),
							                '1' => __('Yes, if already subscribed to same post; with same email/IP; don\'t require another confirmation', $this->plugin->text_domain),
						                ),
						                'notes_after'     => '<p>'.__('IP addresses can be spoofed by an end-user, so it\'s generally recommended that you don\'t enable this. However, the sky won\'t fall if you do. Setting this to <code>Yes</code> will prevent repeat confirmation emails from being sent to commenters who choose to subscribe to <em>replies only</em> every time they comment on a single post. In this scenario; a single commenter, on a single post, may actually be associated with multiple comment subscriptions — one for each of their own comments. We say, "the sky won\'t fall", because even if an IP is spoofed, the underlying email address will have already been confirmed in one way or another. Enabling this option is not the safest route to take, but it might be an acceptable risk for your organization. It\'s really a judgement call on your part.', $this->plugin->text_domain).'</p>'
					                )).
				                '  </tbody>'.
				                ' </table>'.
				                '</div>';

				$_panel_body .= '<hr />';

				$_panel_body .= '<table>'.
				                ' <tbody>'.
				                $form_fields->select_row(
					                array(
						                'label'           => __('<i class="fa fa-wordpress"></i> <i class="fa fa-users"></i>'.
						                                        ' All WordPress Users Confirm their Email Address?', $this->plugin->text_domain),
						                'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
						                'name'            => 'all_wp_users_confirm_email',
						                'current_value'   => $current_value_for('all_wp_users_confirm_email'),
						                'allow_arbitrary' => FALSE, // Must be one of these.
						                'options'         => array(
							                '0' => __('No, some of my users register &amp; log in w/o confirming their email address (typical, safest answer)', $this->plugin->text_domain),
							                '1' => __('Yes, ALL of my users register &amp; confirm their email address before being allowed to log in', $this->plugin->text_domain),
						                ),
						                'notes_before'    => '<p><em>'.__('Please do a review of your theme and all plugins before answering yes to this question.', $this->plugin->text_domain).'</em></p>',
						                'notes_after'     => '<p>'.sprintf(__('If %1$s sees that a user is currently logged into the site as a real user (i.e. not <em>just</em> a commenter); it can detect the current user\'s email address w/o needing the encrypted <code>%2$s</code> cookie that is normally set via email confirmation. However, in order for this to occur, this option must be set to <code>Yes</code>; i.e. %1$s needs to know that it can trust the email address associated w/ each user account within WordPress before it will read an email address from <code>wp_users</code> table.', $this->plugin->text_domain), esc_html($this->plugin->name), esc_html(__NAMESPACE__.'_sub_email')).'</p>'.
						                                     '<p class="pmp-note pmp-warning">'.sprintf(__('<strong>Warning:</strong> Please be cautious about how you answer this question. Do all of your users <em>really</em> register and confirm their email address before being allowed to log in? If a user updates their profile, is an email change-of-address always confirmed too? Some themes/plugins make it possible for registration/updates to occur <em>without</em> doing so. If that\'s the case, you should answer <code>No</code> here (default behavior), and just let the encrypted <code>%2$s</code> cookie do it\'s thing. That\'s what it\'s there for <i class="fa fa-smile-o"></i>', $this->plugin->text_domain), esc_html($this->plugin->name), esc_html(__NAMESPACE__.'_sub_email')).'</p>'.
						                                     '<p class="pmp-note pmp-info">'.sprintf(__('<strong>Note:</strong> Your answer here does not enable or disable auto-confirmation in any way. It\'s simply a flag that is used by %1$s (internally), to help it make the most logical (safest) decision under certain scenarios that are impacted by the email address of the current user. It\'s important to realize that no matter what you answer here, %1$s will still be fully functional. You can only go wrong by saying <code>Yes</code> when in fact your users do NOT always confirm their email. <strong>If in doubt, please answer <code>No</code> (default behavior)</strong>.', $this->plugin->text_domain), esc_html($this->plugin->name)).'</p>'
					                )).
				                ' </tbody>'.
				                '</table>';

				echo $this->panel(__('Auto-Confirm Settings', $this->plugin->text_domain), $_panel_body, array());

				unset($_panel_body); // Housekeeping.

				/* ----------------------------------------------------------------------------------------- */

				$_panel_body = '<table>'.
				               '  <tbody>'.
				               $form_fields->input_row(
					               array(
						               'type'          => 'number',
						               'label'         => __('Maximum Chars in Parent Comment Clips:', $this->plugin->text_domain),
						               'placeholder'   => __('e.g. 100', $this->plugin->text_domain),
						               'name'          => 'comment_notification_parent_content_clip_max_chars',
						               'other_attrs'   => 'min="1"',
						               'current_value' => $current_value_for('comment_notification_parent_content_clip_max_chars'),
						               'notes_after'   => '<p>'.sprintf(__('When %1$s notifies someone about a reply to their comment, there will first be a short clip of the original comment displayed to help offer some context; i.e. to show what the reply is pertaining to. How many characters (maximum) do you want to display in that short clip of the parent comment? The recommended setting is <code>100</code> characters, but you can change this to whatever you like. A very large number will prevent the parent comment from being clipped at all.', $this->plugin->text_domain), esc_html($this->plugin->name)).'</p>'
					               )).
				               '  </tbody>'.
				               '</table>';

				$_panel_body .= '<table>'.
				                '  <tbody>'.
				                $form_fields->input_row(
					                array(
						                'type'          => 'number',
						                'label'         => __('Maximum Chars in Other Comment/Reply Clips:', $this->plugin->text_domain),
						                'placeholder'   => __('e.g. 200', $this->plugin->text_domain),
						                'name'          => 'comment_notification_content_clip_max_chars',
						                'other_attrs'   => 'min="1"',
						                'current_value' => $current_value_for('comment_notification_content_clip_max_chars'),
						                'notes_after'   => '<p>'.sprintf(__('For all other comment/reply notifications, there will be a short clip of the comment, along with a link to [continue reading] on your website. How many characters (maximum) do you want to display in those short clips of the comment or reply? The recommended setting is <code>200</code> characters, but you can change this to whatever you like. A very large number will prevent comments from being clipped at all.', $this->plugin->text_domain), esc_html($this->plugin->name)).'</p>'
					                )).
				                '  </tbody>'.
				                '</table>';

				echo $this->panel(__('Email Notification Clips', $this->plugin->text_domain), $_panel_body, array());

				unset($_panel_body); // Housekeeping.

				/* ----------------------------------------------------------------------------------------- */

				$_panel_body = '<table style="margin:0;">'.
				               ' <tbody>'.
				               $form_fields->select_row(
					               array(
						               'label'           => __('Enable SMTP Integration?', $this->plugin->text_domain),
						               'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
						               'name'            => 'smtp_enable',
						               'current_value'   => $current_value_for('smtp_enable'),
						               'allow_arbitrary' => FALSE, // Must be one of these.
						               'options'         => array(
							               '0' => __('No, use the wp_mail function (default behavior)', $this->plugin->text_domain),
							               '1' => __('Yes, integrate w/ an SMTP server of my choosing (as configured below)', $this->plugin->text_domain),
						               ),
						               'notes_after'     => '<div class="pmp-panel-if-enabled-show">'.
						                                    '   <p style="font-weight:bold; font-size:110%; margin:0;">'.__('When SMTP Server Integration is enabled:', $this->plugin->text_domain).'</p>'.
						                                    '   <ul class="pmp-list-items">'.
						                                    '      <li>'.sprintf(__('Instead of using the default <code>%2$s</code> function, %1$s will send email confirmation requests &amp; comment/reply notifications through an SMTP server of your choosing; i.e. all email processed by %1$s will be routed through an SMTP server that you\'ve dedicated to comment subscriptions. This is highly recommended, since it can significantly improve the deliverability rate of emails that are sent by %1$s. In addition, it may also speed up your site (i.e. reduce the burden on your own web server). This is because an SMTP host is generally associated with an external server that is dedicated to email processing.', $this->plugin->text_domain), esc_html($this->plugin->name), $this->plugin->utils_markup->x_anchor('https://developer.wordpress.org/reference/functions/wp_mail/', 'wp_mail')).'</li>'.
						                                    '      <li>'.sprintf(__('Instead of using the <code>%3$s</code>, <code>%4$s</code>, and <code>%2$s</code> email message headers configured elsewhere in %1$s, the values that you configure for the SMTP server will be used instead; i.e. what you configure here will override other email header options in %1$s. This allows you to be specific about what message headers are passed through your SMTP server whenever SMTP functionality is enabled.', $this->plugin->text_domain), esc_html($this->plugin->name), $this->plugin->utils_markup->x_anchor('http://en.wikipedia.org/wiki/Email#Message_header', 'Reply-To'), $this->plugin->utils_markup->x_anchor('http://en.wikipedia.org/wiki/Email#Message_header', 'From'), $this->plugin->utils_markup->x_anchor('http://en.wikipedia.org/wiki/Email#Message_header', 'Return-Path')).'</li>'.
						                                    '   </ul>'.
						                                    '  <p class="pmp-note pmp-info">'.sprintf(__('<strong>Note:</strong> If you are already running a plugin like %2$s (i.e. a plugin that reconfigures the <code>%3$s</code> function globally); that is usually enough, and you should generally NOT enable SMTP integration here also. In other words, if <code>%3$s</code> is already configured globally to route mail through an SMTP server, you would only need the options below if your intention was to override your existing SMTP configuration specifically for %1$s.', $this->plugin->text_domain), esc_html($this->plugin->name), $this->plugin->utils_markup->x_anchor('https://wordpress.org/plugins/wp-mail-smtp/', 'WP Mail SMTP'), $this->plugin->utils_markup->x_anchor('https://developer.wordpress.org/reference/functions/wp_mail/', 'wp_mail')).'</p>'.
						                                    '</div>',
					               )).
				               ' </tbody>'.
				               '</table>';

				$_panel_body .= '<div class="pmp-panel-if-enabled"><hr />'.

				                '<a href="http://aws.amazon.com/ses/" target="_blank">'.
				                '  <img src="'.esc_attr($this->plugin->utils_url->to('/client-s/images/aws-ses-rec.png')).'" class="pmp-right" style="margin:1em 0 0 3em;" />'.
				                '</a>'.

				                ' <table style="width:auto;">'.
				                '  <tbody>'.
				                $form_fields->input_row(
					                array(
						                'label'         => __('SMTP Host Name:', $this->plugin->text_domain),
						                'placeholder'   => __('e.g. email-smtp.us-east-1.amazonaws.com', $this->plugin->text_domain),
						                'name'          => 'smtp_host',
						                'current_value' => $current_value_for('smtp_host'),
						                'notes_after'   => '<p>'.__('e.g. <code>email-smtp.us-east-1.amazonaws.com</code>, <code>smtp.gmail.com</code>, or another of your choosing.', $this->plugin->text_domain).'</p>'
					                )).
				                '  </tbody>'.
				                ' </table>'.

				                ' <table style="width:auto;">'.
				                '  <tbody>'.
				                $form_fields->input_row(
					                array(
						                'type'          => 'number',
						                'label'         => __('SMTP Port Number:', $this->plugin->text_domain),
						                'placeholder'   => __('e.g. 465', $this->plugin->text_domain),
						                'name'          => 'smtp_port',
						                'current_value' => $current_value_for('smtp_port'),
						                'notes_after'   => '<p>'.__('With Amazon&reg; SES (or GMail&trade;) please use: <code>465</code>', $this->plugin->text_domain).'</p>'
					                )).
				                '  </tbody>'.
				                ' </table>'.

				                ' <table style="width:auto;">'.
				                '  <tbody>'.
				                $form_fields->select_row(
					                array(
						                'label'           => __('SMTP Authentication Type:', $this->plugin->text_domain),
						                'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
						                'name'            => 'smtp_secure',
						                'current_value'   => $current_value_for('smtp_secure'),
						                'allow_arbitrary' => FALSE, // Must be one of these.
						                'options'         => array(
							                ''    => __('Plain Text Authentication', $this->plugin->text_domain),
							                'ssl' => __('SSL Authentication (most common)', $this->plugin->text_domain),
							                'tls' => __('TLS Authentication', $this->plugin->text_domain),
						                ),
						                'notes_after'     => '<p>'.__('With Amazon&reg; SES (or GMail&trade;) over port 465, please choose: <code>SSL</code>', $this->plugin->text_domain).'</p>'
					                )).
				                '  </tbody>'.
				                ' </table>'.

				                '<hr />'.

				                ' <table>'.
				                '  <tbody>'.
				                $form_fields->input_row(
					                array(
						                'label'         => __('SMTP Username:', $this->plugin->text_domain),
						                'placeholder'   => __('e.g. AKIAJSA57DDLS5I6GCA; e.g. me@example.com', $this->plugin->text_domain),
						                'name'          => 'smtp_username',
						                'current_value' => $current_value_for('smtp_username'),
						                'notes_after'   => '<p>'.__('With Amazon&reg; SES use your Access Key ID. With GMail&trade; use your login name, or full email address.', $this->plugin->text_domain).'</p>'
					                )).
				                '  </tbody>'.
				                ' </table>'.

				                ' <table>'.
				                '  <tbody>'.
				                $form_fields->input_row(
					                array(
						                'type'          => 'password',
						                'label'         => __('SMTP Password:', $this->plugin->text_domain),
						                'placeholder'   => __('e.g. AWS secret key, or email account password', $this->plugin->text_domain),
						                'name'          => 'smtp_password',
						                'current_value' => $current_value_for('smtp_password'),
						                'notes_after'   => '<p>'.__('With Amazon&reg; SES use your Secret Key. With GMail&trade; use your password.', $this->plugin->text_domain).'</p>'
					                )).
				                '  </tbody>'.
				                ' </table>'.

				                '<hr />'.

				                ' <table>'.
				                '  <tbody>'.
				                $form_fields->input_row(
					                array(
						                'label'         => __('SMTP <code>From</code> and <code>Return-Path</code> Name:', $this->plugin->text_domain),
						                'placeholder'   => __('e.g. MySite.com', $this->plugin->text_domain),
						                'name'          => 'smtp_from_name',
						                'current_value' => $current_value_for('smtp_from_name'),
						                'notes_after'   => '<p>'.sprintf(__('The name used in the <code>%3$s:</code> and <code>%4$s:</code> headers; e.g. <code>MySite.com</code>', $this->plugin->text_domain), esc_html($this->plugin->name), $this->plugin->utils_markup->x_anchor('http://en.wikipedia.org/wiki/Email#Message_header', 'Reply-To'), $this->plugin->utils_markup->x_anchor('http://en.wikipedia.org/wiki/Email#Message_header', 'From'), $this->plugin->utils_markup->x_anchor('http://en.wikipedia.org/wiki/Email#Message_header', 'Return-Path')).'</p>'
					                )).
				                '  </tbody>'.
				                ' </table>'.

				                ' <table>'.
				                '  <tbody>'.
				                $form_fields->input_row(
					                array(
						                'type'          => 'email',
						                'label'         => __('SMTP <code>From</code> and <code>Return-Path</code> Email Address:', $this->plugin->text_domain),
						                'placeholder'   => __('e.g. moderator@mysite.com', $this->plugin->text_domain),
						                'name'          => 'smtp_from_email',
						                'current_value' => $current_value_for('smtp_from_email'),
						                'notes_after'   => '<p>'.sprintf(__('Email used in the <code>%3$s:</code> and <code>%4$s:</code> headers; e.g. <code>moderator@mysite.com</code>', $this->plugin->text_domain), esc_html($this->plugin->name), $this->plugin->utils_markup->x_anchor('http://en.wikipedia.org/wiki/Email#Message_header', 'Reply-To'), $this->plugin->utils_markup->x_anchor('http://en.wikipedia.org/wiki/Email#Message_header', 'From'), $this->plugin->utils_markup->x_anchor('http://en.wikipedia.org/wiki/Email#Message_header', 'Return-Path')).'</p>'.
						                                   '<p class="pmp-note pmp-info">'.__('<strong>Note:</strong> most SMTP servers will require this email address to match up with specific users and/or specific domains; else mail is rejected automatically. Please be sure to check the documentation for your SMTP host before entering this address. For instance, with Amazon&reg; SES you will need to setup at least one Verified Sender and then enter that address here. With GMail&trade;, you will need to enter the email address that is associated with the Username/Password you entered above.', $this->plugin->text_domain).'</p>'
					                )).
				                '  </tbody>'.
				                ' </table>'.

				                '<hr />'.

				                ' <table>'.
				                '  <tbody>'.
				                $form_fields->input_row(
					                array(
						                'type'          => 'email',
						                'label'         => __('SMTP <code>Reply-To</code> Email Address:', $this->plugin->text_domain),
						                'placeholder'   => __('e.g. moderator@mysite.com', $this->plugin->text_domain),
						                'name'          => 'smtp_reply_to_email',
						                'current_value' => $current_value_for('smtp_reply_to_email'),
						                'notes_after'   => '<p>'.sprintf(__('Email used in the <code>%2$s:</code> header; e.g. <code>moderator@mysite.com</code>. This makes it so that if someone happens to reply to an email notification, that reply will be directed to a specific email address that you prefer. Some site owners like to use something like <code>noreply@mysite.com</code>, while others find it best to use a real email address that can monitor replies. It is a matter of preference. In the future, %1$s may support replies to comments via email, and this field could become more important at that time.', $this->plugin->text_domain), esc_html($this->plugin->name), $this->plugin->utils_markup->x_anchor('http://en.wikipedia.org/wiki/Email#Message_header', 'Reply-To'), $this->plugin->utils_markup->x_anchor('http://en.wikipedia.org/wiki/Email#Message_header', 'From'), $this->plugin->utils_markup->x_anchor('http://en.wikipedia.org/wiki/Email#Message_header', 'Return-Path')).'</p>'
					                )).
				                '  </tbody>'.
				                ' </table>'.

				                /* This is currently forced to a value of `1`.
				                ' <table>'.
				                '  <tbody>'.
				                $form_fields->select_row(
					                array(
						                'label'           => __('Force <code>From:</code> &amp; <code>Return-Path:</code> Headers?', $this->plugin->text_domain),
						                'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
						                'name'            => 'smtp_force_from',
						                'current_value'   => $current_value_for('smtp_force_from'),
						                'allow_arbitrary' => FALSE, // Must be one of these.
						                'options'         => array(
							                '1' => __('Yes, always use the "Name" <address> I\'ve given (recommended)', $this->plugin->text_domain),
							                '0' => __('No, use "Name" <address> I\'ve given by default, but allow individual emails to override', $this->plugin->text_domain),
						                ),
					                )).
				                '  </tbody>'.
				                ' </table>'. */

				                '<hr />'.

				                ' <table>'.
				                '  <tbody>'.
				                $form_fields->input_row(
					                array(
						                'type'        => 'email',
						                'label'       => __('Test SMTP Server Settings?', $this->plugin->text_domain),
						                'placeholder' => __('e.g. me@mysite.com', $this->plugin->text_domain),
						                'name'        => 'mail_smtp_test', // Not an actual option key; but the `save_options` handler picks this up.
						                'notes_after' => sprintf(__('Enter an email address to have %1$s&trade; send a test message when you save these options, and report back about any success or failure.', $this->plugin->text_domain), esc_html($this->plugin->name)),
					                )).
				                '  </tbody>'.
				                ' </table>'.
				                '</div>';

				echo $this->panel(__('SMTP Server Integration', $this->plugin->text_domain), $_panel_body, array());

				unset($_panel_body); // Housekeeping.

				/* ----------------------------------------------------------------------------------------- */

				$_panel_body = '<table>'.
				               '  <tbody>'.
				               $form_fields->textarea_row(
					               array(
						               'label'         => __('Email Blacklist Patterns (One Per Line)', $this->plugin->text_domain),
						               'placeholder'   => __('e.g. webmaster@*', $this->plugin->text_domain),
						               'name'          => 'email_blacklist_patterns',
						               'rows'          => 15, // Give them some room here.
						               'other_attrs'   => 'spellcheck="false"',
						               'current_value' => $current_value_for('email_blacklist_patterns'),
						               'notes_before'  => '<p>'.__('These email addresses will not be allowed to subscribe.', $this->plugin->text_domain).'</p>',
						               'notes_after'   => '<p>'.__('One pattern per line please. A <code>*</code> wildcard character can be used to match zero or more characters of any kind. A <code>^</code> caret symbol can be used to match zero or more characters that are NOT the <code>@</code> symbol.', $this->plugin->text_domain).'</p>'.
						                                  '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> It is suggested that you blacklist role-based email addresses to avoid sending email notifications to addresses not associated w/ individuals. Role-based email addresses (like admin@, help@, sales@) are email addresses that are not associated with a particular person, but rather with a company, department, position or group of recipients. They are not generally intended for personal use, as they typically include a distribution list of recipients.', $this->plugin->text_domain).'</p>',
					               )).
				               '  </tbody>'.
				               '</table>';

				echo $this->panel(__('Blacklisted Email Addresses', $this->plugin->text_domain), $_panel_body, array());

				unset($_panel_body); // Housekeeping.

				/* ----------------------------------------------------------------------------------------- */

				$_panel_body = '<table>'.
				               '  <tbody>'.
				               $form_fields->input_row(
					               array(
						               'type'          => 'number',
						               'label'         => __('Max Execution Time (In Seconds)', $this->plugin->text_domain),
						               'placeholder'   => __('e.g. 30', $this->plugin->text_domain),
						               'name'          => 'sub_cleaner_max_time',
						               'current_value' => $current_value_for('sub_cleaner_max_time'),
						               'other_attrs'   => 'min="10" max="3600"',
						               'notes_after'   => '<p>'.sprintf(__('The Subscription Cleaner automatically deletes unconfirmed and trashed subscriptions. It runs via %1$s every hour. This setting determines how much time you want to allow each cleaning process to run for. The minimum allowed value is <code>10</code> seconds. Maximum allowed value is <code>3600</code> seconds. A good default value is <code>30</code> seconds. That\'s more than adequate for most sites.', $this->plugin->text_domain), $this->plugin->utils_markup->x_anchor('http://www.smashingmagazine.com/2013/10/16/schedule-events-using-wordpress-cron/', 'WP-Cron')).'</p>'
					               )).
				               '  </tbody>'.
				               '</table>';

				$_panel_body .= '<table>'.
				                '  <tbody>'.
				                $form_fields->input_row(
					                array(
						                'label'         => __('Unconfirmed Expiration Time', $this->plugin->text_domain),
						                'placeholder'   => __('e.g. 60 days', $this->plugin->text_domain),
						                'name'          => 'unconfirmed_expiration_time',
						                'current_value' => $current_value_for('unconfirmed_expiration_time'),
						                'notes_after'   => '<p>'.sprintf(__('How long should unconfirmed subscriptions be kept in the database? e.g. <code>2 days</code>, <code>1 week</code>, <code>2 months</code>. Anything compatible with PHP\'s %1$s function will work here.', $this->plugin->text_domain).'</p>'.
						                                                 '<p class="pmp-note pmp-info">'.__('If you empty this field, unconfirmed subscriptions will not be cleaned, they will remain indefinitely.', $this->plugin->text_domain), $this->plugin->utils_markup->x_anchor('http://php.net/manual/en/function.strtotime.php', 'strtotime')).'</p>'
					                )).
				                '  </tbody>'.
				                '</table>';

				$_panel_body .= '<table>'.
				                '  <tbody>'.
				                $form_fields->input_row(
					                array(
						                'label'         => __('Trash Expiration Time', $this->plugin->text_domain),
						                'placeholder'   => __('e.g. 60 days', $this->plugin->text_domain),
						                'name'          => 'trashed_expiration_time',
						                'current_value' => $current_value_for('trashed_expiration_time'),
						                'notes_after'   => '<p>'.sprintf(__('How long should trashed subscriptions be kept in the database? e.g. <code>2 days</code>, <code>1 week</code>, <code>2 months</code>. Anything compatible with PHP\'s %1$s function will work here.', $this->plugin->text_domain).'</p>'.
						                                                 '<p class="pmp-note pmp-info">'.__('If you empty this field, trashed subscriptions will not be cleaned, they will remain indefinitely.', $this->plugin->text_domain), $this->plugin->utils_markup->x_anchor('http://php.net/manual/en/function.strtotime.php', 'strtotime')).'</p>'
					                )).
				                '  </tbody>'.
				                '</table>';

				echo $this->panel(__('Sub. Cleaner Adjustments', $this->plugin->text_domain), $_panel_body, array());

				unset($_panel_body); // Housekeeping.

				/* ----------------------------------------------------------------------------------------- */

				$_panel_body = '<table>'.
				                '  <tbody>'.
				                $form_fields->input_row(
					                array(
						                'type'          => 'number',
						                'label'         => __('Max Execution Time (In Seconds)', $this->plugin->text_domain),
						                'placeholder'   => __('e.g. 30', $this->plugin->text_domain),
						                'name'          => 'log_cleaner_max_time',
						                'current_value' => $current_value_for('log_cleaner_max_time'),
						                'other_attrs'   => 'min="10" max="3600"',
						                'notes_after'   => '<p>'.sprintf(__('The Log Cleaner can automatically delete very old event log entries. It runs via %1$s every hour. This setting determines how much time you want to allow each cleaning process to run for. The minimum allowed value is <code>10</code> seconds. Maximum allowed value is <code>3600</code> seconds. A good default value is <code>30</code> seconds. That\'s more than adequate for most sites.', $this->plugin->text_domain), $this->plugin->utils_markup->x_anchor('http://www.smashingmagazine.com/2013/10/16/schedule-events-using-wordpress-cron/', 'WP-Cron')).'</p>'
					                )).
				                '  </tbody>'.
				                '</table>';

				$_panel_body .= '<table>'.
				                '  <tbody>'.
				                $form_fields->input_row(
					                array(
						                'label'         => __('Sub. Event Log Expiration Time', $this->plugin->text_domain),
						                'placeholder'   => __('e.g. 7 years', $this->plugin->text_domain),
						                'name'          => 'sub_event_log_expiration_time',
						                'current_value' => $current_value_for('sub_event_log_expiration_time'),
						                'notes_after'   => '<p>'.sprintf(__('How long should should subscription event log entries be kept in the database? e.g. <code>90 days</code>, <code>1 year</code>, <code>10 years</code>. Anything compatible with PHP\'s %1$s function will work here.', $this->plugin->text_domain).'</p>'.
						                                                 '<p class="pmp-note pmp-info">'.__('If you empty this field, log entries will not be cleaned; they will remain indefinitely (default behavior). By default, log entries remain indefinitely since these are the underlying data used for statistical reporting. However, if you are not concerned about long-term historical data, feel free to define an expiration time. If you do, it is recommended that your expiration time be <code>1 year</code> (or more) so that statistical reporting will still function properly for short-term data.', $this->plugin->text_domain), $this->plugin->utils_markup->x_anchor('http://php.net/manual/en/function.strtotime.php', 'strtotime')).'</p>'
					                )).
				                '  </tbody>'.
				                '</table>';

				$_panel_body .= '<table>'.
				                '  <tbody>'.
				                $form_fields->input_row(
					                array(
						                'label'         => __('Queue Event Log Expiration Time', $this->plugin->text_domain),
						                'placeholder'   => __('e.g. 7 years', $this->plugin->text_domain),
						                'name'          => 'queue_event_log_expiration_time',
						                'current_value' => $current_value_for('queue_event_log_expiration_time'),
						                'notes_after'   => '<p>'.sprintf(__('How long should should queue event log entries be kept in the database? e.g. <code>90 days</code>, <code>1 year</code>, <code>10 years</code>. Anything compatible with PHP\'s %1$s function will work here.', $this->plugin->text_domain).'</p>'.
						                                                 '<p class="pmp-note pmp-info">'.__('If you empty this field, log entries will not be cleaned; they will remain indefinitely (default behavior). By default, log entries remain indefinitely since these are the underlying data used for statistical reporting. However, if you are not concerned about long-term historical data, feel free to define an expiration time. If you do, it is recommended that your expiration time be <code>1 year</code> (or more) so that statistical reporting will still function properly for short-term data.', $this->plugin->text_domain), $this->plugin->utils_markup->x_anchor('http://php.net/manual/en/function.strtotime.php', 'strtotime')).'</p>'
					                )).
				                '  </tbody>'.
				                '</table>';

				echo $this->panel(__('Log Cleaner Adjustments', $this->plugin->text_domain), $_panel_body, array());

				unset($_panel_body); // Housekeeping.

				/* ----------------------------------------------------------------------------------------- */

				$_panel_body = '<table>'.
				                '  <tbody>'.
				                $form_fields->input_row(
					                array(
						                'type'          => 'number',
						                'label'         => __('Max Execution Time (In Seconds)', $this->plugin->text_domain),
						                'placeholder'   => __('e.g. 30', $this->plugin->text_domain),
						                'name'          => 'queue_processor_max_time',
						                'current_value' => $current_value_for('queue_processor_max_time'),
						                'other_attrs'   => 'min="10" max="300"',
						                'notes_after'   => '<p>'.sprintf(__('The Queue Processor sends email notifications. It runs via %1$s every 5 minutes. This setting determines how much time you want to allow each process to run for. The minimum allowed value is <code>10</code> seconds. Maximum allowed value is <code>300</code> seconds. A good default value is <code>30</code> seconds. That\'s more than adequate for most sites.', $this->plugin->text_domain), $this->plugin->utils_markup->x_anchor('http://www.smashingmagazine.com/2013/10/16/schedule-events-using-wordpress-cron/', 'WP-Cron')).'</p>'
					                )).
				                '  </tbody>'.
				                '</table>';

				$_panel_body .= '<table>'.
				                '  <tbody>'.
				                $form_fields->input_row(
					                array(
						                'type'          => 'number',
						                'label'         => __('Delay Time (In Milliseconds)', $this->plugin->text_domain),
						                'placeholder'   => __('e.g. 250', $this->plugin->text_domain),
						                'name'          => 'queue_processor_delay',
						                'current_value' => $current_value_for('queue_processor_delay'),
						                'other_attrs'   => 'min="0"',
						                'notes_before'  => '<p><em>1000 milliseconds = 1 second; 500 milliseconds = .5 seconds; 250 milliseconds = .25 seconds</em></p>',
						                'notes_after'   => '<p>'.__('The Queue Processor has the ability to send multiple email notifications consecutively when it runs. However, you can force a delay between each email that it sends while it is running. This will help reduce server load and also reduce the chance of your server being flagged as a bulk sender. The minimum allowed value is <code>0</code> milliseconds (<code>0</code> will disable the delay completely). The maximum allowed value (converted to seconds) is <code>([configured Max Execution Time] - 5 seconds)</code>. A good default value is <code>250</code> milliseconds. That\'s perfect for most sites. That said, if you have a lot of emails ending up in the spam folder, try raising this in <code>250</code> millisecond increments until things improve.', $this->plugin->text_domain).'</p>'
					                )).
				                '  </tbody>'.
				                '</table>';

				$_panel_body .= '<table>'.
				                '  <tbody>'.
				                $form_fields->input_row(
					                array(
						                'type'          => 'number',
						                'label'         => __('Max Email Notifications Per Process', $this->plugin->text_domain),
						                'placeholder'   => __('e.g. 100', $this->plugin->text_domain),
						                'name'          => 'queue_processor_max_limit',
						                'current_value' => $current_value_for('queue_processor_max_limit'),
						                'other_attrs'   => 'min="1"',
						                'notes_after'   => '<p>'.__('The Queue Processor will pull X number of pending notifications from the database each time it runs, and then work on those for as long as it can, given your configuration above. This setting allows you to control the max number of email notifications that it should work on in each process. In short, you can use this option to control the maximum number of emails that can ever be sent by each queue runner. Keep in mind, the Queue Processor runs once every 5 minutes. The limit that you define here will allow X number of emails to be sent each time that it runs. The minimum allowed value is <code>1</code>. Maximum allowed value is <code>1000</code> (for security reasons). However, this upper limit can be raised further (if absolutely necessary) through a WP filter.', $this->plugin->text_domain).'</p>'.
						                                   '<p class="pmp-note pmp-info">'.__('It\'s important to realize that what you define here may not always be possible; i.e. this is a maximum limit, not an exact number that will always be processed. For instance, if you set this to <code>1000</code> but you change Max Execution Time to <code>10</code>, there is very little chance that 1000 email notifications can be sent in just <code>10</code> seconds. In such a scenario, the Queue Processor will attempt to process up to <code>1000</code>, but stop after <code>10</code> seconds and work on whatever remains later.', $this->plugin->text_domain).'</p>'
					                )).
				                '  </tbody>'.
				                '</table>';

				$_panel_body .= '<hr />';

				$_panel_body .= '<table>'.
				                '  <tbody>'.
				                $form_fields->input_row(
					                array(
						                'type'          => 'number',
						                'label'         => __('Real-Time Queue Processor; Max Email Notifications in Real-Time', $this->plugin->text_domain),
						                'placeholder'   => __('e.g. 5', $this->plugin->text_domain),
						                'name'          => 'queue_processor_realtime_max_limit',
						                'current_value' => $current_value_for('queue_processor_realtime_max_limit'),
						                'other_attrs'   => 'min="0" max="100"',
						                'notes_after'   => '<p>'.__('In addition to the Queue Processor running via WP-Cron, it can also run in real-time as a comment is being posted (assuming that particular comment is automatically approved; i.e. that it doesn\'t require administrative approval). In cases where it\'s possible, real-time queue processing allows for easier testing and for more-immediate notifications. It is particularly helpful on posts that only have just a few subscribers anyway. There is no mass-mailing needed in such a scenario.', $this->plugin->text_domain).'</p>'.
						                                   '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> It is recommended that you keep this number very low; i.e. just a few notifications should be attempted in real-time. The rest (if there are any) can be handled by queue processes running via WP-Cron. A suggested setting for this option is <code>5</code>. If you set this to <code>0</code> it will effectively disable real-time queue processing if you wish. There\'s an upper limit of <code>100</code> to avoid serious real-time processing delays for end-users. Under no circumstance (no matter what you configure here), will real-time processing ever be allowed to continue for more than <code>10</code> seconds. Therefore, whatever you configure here will be a maximum allowed within the <code>10</code> second timeframe. If you set this too high for completion within <code>10</code> seconds, whatever remains will be processed by WP-Cron queue runners later.', $this->plugin->text_domain).'</p>'
					                )).
				                '  </tbody>'.
				                '</table>';

				echo $this->panel(__('Queue Processor Adjustments', $this->plugin->text_domain), $_panel_body, array());

				unset($_panel_body); // Housekeeping.

				/* ----------------------------------------------------------------------------------------- */

				$_panel_body = '<table>'.
				               '  <tbody>'.
				               $form_fields->input_row(
					               array(
						               'label'         => __('WordPress Capability Required to Manage Subscriptions', $this->plugin->text_domain),
						               'placeholder'   => __('e.g. moderate_comments', $this->plugin->text_domain),
						               'name'          => 'manage_cap',
						               'current_value' => $current_value_for('manage_cap'),
						               'notes_after'   => '<p>'.sprintf(__('If you can <code>%2$s</code>, you can always manage subscriptions and %1$s options, no matter what you configure here. However, if you have other users that help manage your site, you can set a specific %3$s they\'ll need in order for %1$s to allow them access. Users w/ this capability will be allowed to manage subscriptions, the mail queue, event logs, and statistics; i.e. everything <em>except</em> change %1$s options. To alter %1$s options you\'ll always need the <code>%2$s</code> capability.', $this->plugin->text_domain), esc_html($this->plugin->name), $this->plugin->utils_markup->x_anchor('http://codex.wordpress.org/Roles_and_Capabilities#'.$this->plugin->cap, $this->plugin->cap), $this->plugin->utils_markup->x_anchor('http://codex.wordpress.org/Roles_and_Capabilities', __('WordPress Capability', $this->plugin->text_domain))).'</p>',
					               )).
				               '  </tbody>'.
				               '</table>';

				echo $this->panel(__('Subscription Management Access', $this->plugin->text_domain), $_panel_body, array());

				unset($_panel_body); // Housekeeping.

				/* ----------------------------------------------------------------------------------------- */

				$_panel_body = '<table>'.
				               '  <tbody>'.
				               $form_fields->input_row(
					               array(
						               'label'         => __('Don\'t Show Meta Boxes for these Post Types:', $this->plugin->text_domain),
						               'placeholder'   => __('e.g. link,comment,revision,attachment,nav_menu_item,snippet,redirect', $this->plugin->text_domain),
						               'name'          => 'excluded_meta_box_post_types',
						               'current_value' => $current_value_for('excluded_meta_box_post_types'),
						               'notes_after'   => '<p>'.sprintf(__('These are %2$s NOT associated w/ comments in any way; i.e. %1$s will not display its meta boxes in the post editing station for these types of posts. The default list is adequate for most sites. However, if you have other %2$s enabled by a theme/plugin, you might wish to include those here. e.g. <code>portfolio,gallery</code> might be two %3$s that you add to the default list, assuming these are not to be associated w/ comments.', $this->plugin->text_domain), esc_html($this->plugin->name), $this->plugin->utils_markup->x_anchor('http://codex.wordpress.org/Post_Types', __('Post Types', $this->plugin->text_domain)), $this->plugin->utils_markup->x_anchor('http://codex.wordpress.org/Post_Types#Custom_Post_Types', __('Custom Post Types', $this->plugin->text_domain))).'</p>',
					               )).
				               '  </tbody>'.
				               '</table>';

				echo $this->panel(__('Post Meta Box Settings', $this->plugin->text_domain), $_panel_body, array());

				unset($_panel_body); // Housekeeping.

				/* ----------------------------------------------------------------------------------------- */

				$_panel_body = '<table>'.
				               '  <tbody>'.
				               $form_fields->input_row(
					               array(
						               'type'          => 'number',
						               'label'         => __('"My Subscriptions" Summary; Max Subscriptions Per Page', $this->plugin->text_domain),
						               'placeholder'   => __('e.g. 25', $this->plugin->text_domain),
						               'name'          => 'sub_manage_summary_max_limit',
						               'current_value' => $current_value_for('sub_manage_summary_max_limit'),
						               'other_attrs'   => 'min="1" max="1000"',
						               'notes_after'   => '<p>'.sprintf(__('On the front-end of %1$s, the "My Subscriptions" summary page will list all of the subscriptions currently associated with a subscriber\'s email address. This controls the maximum number of subscriptions to list per page. Minimum value is <code>1</code> subscription per page. Maximum value is <code>1000</code> subscriptions per page. The recommended setting is <code>25</code> subscriptions per page. Based on your setting here; if there are too many to display on a single page, pagination links will appear automatically.', $this->plugin->text_domain), esc_html($this->plugin->name)).'</p>',
					               )).
				               '  </tbody>'.
				               '</table>';

				$_panel_body .= '<hr />';

				$_panel_body .= '<table>'.
				                '  <tbody>'.
				                $form_fields->select_row(
					                array(
						                'label'           => __('Select Menu Options; List Posts?', $this->plugin->text_domain),
						                'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
						                'field_class'     => 'no-if-enabled',
						                'name'            => 'post_select_options_enable',
						                'current_value'   => $current_value_for('post_select_options_enable'),
						                'allow_arbitrary' => FALSE,
						                'options'         => array(
							                '1' => __('Yes, enable post select menu options', $this->plugin->text_domain),
							                '0' => __('No, disable post selection; users can enter post IDs manually', $this->plugin->text_domain),
						                ),
						                'notes_after'     => '<p>'.sprintf(__('On both the back and front-end of %1$s, when you add/edit a subscription, %1$s can provide a drop-down menu with a list of all existing posts for you to choose from. Would you like to enable or disable this feature? If disabled, you will need to enter any post IDs manually instead of being able to choose from a drop-down menu. Since this impacts the front-end too, it is generally a good idea to enable select menu options for your users.', $this->plugin->text_domain), esc_html($this->plugin->name)).'</p>',
					                )).
				                '  </tbody>'.
				                '</table>';

				$_panel_body .= '<table>'.
				                '  <tbody>'.
				                $form_fields->select_row(
					                array(
						                'label'           => __('Post Select Menu Options; Include Media?', $this->plugin->text_domain),
						                'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
						                'field_class'     => 'no-if-enabled',
						                'name'            => 'post_select_options_media_enable',
						                'current_value'   => $current_value_for('post_select_options_media_enable'),
						                'allow_arbitrary' => FALSE,
						                'options'         => array(
							                '0' => __('No, exclude media attachments (save space); I don\'t receive comments on media', $this->plugin->text_domain),
							                '1' => __('Yes, include enable media attachments in any post select menu options', $this->plugin->text_domain),
						                ),
						                'notes_after'     => '<p>'.sprintf(__('On both the back and front-end of %1$s, when you add/edit a subscription, %1$s can provide a drop-down menu with a list of all existing posts for you to choose from. This feature can be enabled/disabled above. If enabled, do you want the post select menu options to include media attachments too? If you have a lot of posts, it\'s a good idea to exclude media attachments from the select menu options to save space. Most people don\'t leave comments on media attachments.', $this->plugin->text_domain), esc_html($this->plugin->name)).'</p>',
					                )).
				                '  </tbody>'.
				                '</table>';

				$_panel_body .= '<hr />';

				$_panel_body .= '<table>'.
				                '  <tbody>'.
				                $form_fields->select_row(
					                array(
						                'label'           => __('Select Menu Options; List Comments?', $this->plugin->text_domain),
						                'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
						                'field_class'     => 'no-if-enabled',
						                'name'            => 'comment_select_options_enable',
						                'current_value'   => $current_value_for('comment_select_options_enable'),
						                'allow_arbitrary' => FALSE,
						                'options'         => array(
							                '1' => __('Yes, enable comment select menu options', $this->plugin->text_domain),
							                '0' => __('No, disable comment selection; users enter comment IDs manually', $this->plugin->text_domain),
						                ),
						                'notes_after'     => '<p>'.sprintf(__('On both the back and front-end of %1$s, when you add/edit a subscription, %1$s can provide a drop-down menu with a list of all existing comments (on a given post) for you to choose from. Would you like to enable or disable this feature? If disabled, you will need to enter any comment IDs manually instead of being able to choose from a drop-down menu. Since this impacts the front-end too, it is generally a good idea to enable select menu options for your users.', $this->plugin->text_domain), esc_html($this->plugin->name)).'</p>',
					                )).
				                '  </tbody>'.
				                '</table>';

				$_panel_body .= '<hr />';

				$_panel_body .= '<table>'.
				                '  <tbody>'.
				                $form_fields->select_row(
					                array(
						                'label'           => __('Select Menu Options; List Users?', $this->plugin->text_domain),
						                'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
						                'field_class'     => 'no-if-enabled',
						                'name'            => 'user_select_options_enable',
						                'current_value'   => $current_value_for('user_select_options_enable'),
						                'allow_arbitrary' => FALSE,
						                'options'         => array(
							                '1' => __('Yes, enable user select menu options', $this->plugin->text_domain),
							                '0' => __('No, disable user selection; I can enter user IDs manually', $this->plugin->text_domain),
						                ),
						                'notes_after'     => '<p>'.sprintf(__('On the back-end of %1$s, when you add/edit a subscription, %1$s can provide a drop-down menu with a list of all existing users for you to choose from. Would you like to enable or disable this feature? If disabled, you will need to enter any user IDs manually instead of being able to choose from a drop-down menu.', $this->plugin->text_domain), esc_html($this->plugin->name)).'</p>',
					                )).
				                '  </tbody>'.
				                '</table>';

				$_panel_body .= '<hr />';

				$_panel_body .= '<table>'.
				                '  <tbody>'.
				                $form_fields->input_row(
					                array(
						                'type'          => 'number',
						                'label'         => __('Maximum Select Menu Options Before Input Fallback:', $this->plugin->text_domain),
						                'placeholder'   => __('e.g. 2000', $this->plugin->text_domain),
						                'name'          => 'max_select_options',
						                'current_value' => $current_value_for('max_select_options'),
						                'notes_after'   => '<p>'.sprintf(__('If %1$s detects that any select menu may contain more than this number of options (e.g. if you have several thousands posts, comments, users, etc); then it will automatically fallback on a regular text input field instead. This prevents memory issues in browsers that may be unable to deal with super long select menus. Recommended setting for this option is <code>2000</code>.', $this->plugin->text_domain), esc_html($this->plugin->name)).'</p>'.
						                                   '<p class="pmp-note pmp-info">'.sprintf(__('<strong>Tip:</strong> You\'ll be happy to know that %1$s is quite capable of including hundreds of select menu options w/o issue. It even makes each select menu searchable for you. However, there is a limit to what is possible. We recommend setting this to a value of around <code>1000</code> or more. It should never be set higher than <code>10000</code> though. Most browsers will be unable to deal with that many menu options; no matter the software.', $this->plugin->text_domain), esc_html($this->plugin->name)).'</p>',
					                )).
				                '  </tbody>'.
				                '</table>';

				echo $this->panel(__('Misc. UI-Related Settings', $this->plugin->text_domain), $_panel_body, array());

				unset($_panel_body); // Housekeeping.

				/* ----------------------------------------------------------------------------------------- */

				echo '         <div class="pmp-save">'."\n";
				echo '            <button type="submit">'.__('Save All Changes', $this->plugin->text_domain).' <i class="fa fa-save"></i></button>'."\n";
				echo '         </div>'."\n";

				echo '      </div>'."\n";
				echo '   </form>'."\n";
				echo '</div>';
			}

			/**
			 * Displays menu page.
			 *
			 * @since 141111 First documented version.
			 */
			protected function import_export_()
			{
				echo '<div class="'.esc_attr($this->plugin->slug.'-menu-page '.$this->plugin->slug.'-menu-page-import-export '.$this->plugin->slug.'-menu-page-area').'">'."\n";

				echo '   '.$this->heading(__('Import/Export', $this->plugin->text_domain), 'logo.png').
				     '   '.$this->notes(); // Heading/notifications.

				echo '   <div class="pmp-body">'."\n";

				echo '         '.$this->all_panel_togglers();

				/* ----------------------------------------------------------------------------------------- */

				echo '      <h2 class="pmp-section-heading">'.
				     '         '.__('Import/Export Subscriptions', $this->plugin->text_domain).
				     '         <small>'.sprintf(__('This allows you to import/export %1$s&trade; subscriptions.', $this->plugin->text_domain), esc_html($this->plugin->name)).'</small>'.
				     '      </h2>';

				/* --------------------------------------------------------------------------------------------------------------------------------------------------------------------------- */

				$_form_field_args = array(
					'ns_id_suffix'   => '-import-subs-form',
					'ns_name_suffix' => '[import]',
					'class_prefix'   => 'pmp-import-subs-form-',
				);
				$_form_fields     = new form_fields($_form_field_args);

				$_panel_body = '<form method="post" enctype="multipart/form-data" action="'.esc_attr($this->plugin->utils_url->page_nonce_only()).'" novalidate="novalidate">'."\n";

				$_panel_body .= ' <h3 style="margin-bottom:0;">'.sprintf(__('Import New %1$s&trade; Subscriptions, or Update Existing Subscriptions', $this->plugin->text_domain), esc_html($this->plugin->name)).'</h3>';
				$_panel_body .= ' <p>'.sprintf(__('The importation routine will accept direct CSV input in the textarea below, or you can choose to upload a prepared CSV file.', $this->plugin->text_domain), esc_html($this->plugin->name)).'</p>';
				$_panel_body .= ' <p class="pmp-note pmp-notice" style="font-size:90%;">'.sprintf(__('<strong>Note:</strong> The format required for importation is %2$s. For mass updates, an <code>"ID"</code> is the only column that is absolutely required. The <code>"ID"</code> column (if present) indicates that you want to update an existing subscription with a particular ID. However, for new subscriptions; please omit the <code>"ID"</code> column. When importing new subscriptions, your CSV file need only contain the <code>"email"</code> and <code>"post_id"</code> columns. There are %3$s w/ a full list of all possible import columns. In either case (direct input or file upload) the first line should be a list of columns you\'re importing; aka: headers.', $this->plugin->text_domain), esc_html($this->plugin->name), $this->plugin->utils_markup->x_anchor('http://en.wikipedia.org/wiki/Comma-separated_values', 'CSV (Comma Separated Values)'), $this->plugin->utils_markup->x_anchor('https://github.com/websharks/comment-mail/wiki/CSV-Subscription-Imports', __('additional details here', $this->plugin->text_domain))).'</p>';
				$_panel_body .= ' <p class="pmp-note pmp-info" style="font-size:90%;">'.sprintf(__('<strong>Tip:</strong> If you\'re looking for more elaborate examples, you can simply use the "CSV Export" panel on this page. The easiest way to see how this works is by looking at a CSV export file generated by %1$s&trade; itself. That\'s the format you should follow please. In fact, you could even pull an export, make changes to the file, and then import that modified file to mass update existing subscriptions.', $this->plugin->text_domain), esc_html($this->plugin->name)).'</p>';
				$_panel_body .= ' <p class="pmp-note pmp-warning" style="font-size:90%;">'.sprintf(__('<strong>Upper Limits:</strong> There is an upper limit of <code>5000</code> lines allowed per import; i.e. you must limit each import to this number of lines so as to avoid extremely long-running PHP processes. In addition, given your current web host (i.e. PHP configuration); if you choose to upload a prepared CSV file, the maximum allowed file upload size is currently: <code>%1$s</code>.', $this->plugin->text_domain), esc_html($this->plugin->utils_fs->bytes_abbr($this->plugin->utils_env->max_upload_size()))).'</p>';

				$_panel_body .= ' <table>'.
				                '   <tbody>'.
				                $_form_fields->textarea_row(
					                array(
						                'label'         => __('Direct CSV Input Data:', $this->plugin->text_domain),
						                'placeholder'   => __('"email", "post_id", "status"'."\n".'"john@example.com", "1", "subscribed"', $this->plugin->text_domain),
						                'name'          => 'data',
						                'rows'          => 15,
						                'current_value' => !empty($_REQUEST[__NAMESPACE__]['import']['data']) ? trim(stripslashes((string)$_REQUEST[__NAMESPACE__]['import']['data'])) : NULL,
						                'notes_before'  => '<p>'.__('The first line of this input should be CSV headers; e.g. <code>"email", "post_id", "status"</code>', $this->plugin->text_domain).'</p>',
					                )).
				                '   </tbody>'.
				                ' </table>';

				$_panel_body .= ' <hr />';

				$_panel_body .= ' <table>'.
				                '   <tbody>'.
				                $_form_fields->input_row(
					                array(
						                'type'         => 'file',
						                'label'        => __('Or, a Prepared CSV File Upload:', $this->plugin->text_domain),
						                'placeholder'  => __('e.g. comment-subscriptions.csv', $this->plugin->text_domain),
						                'name'         => 'data_file',
						                'notes_before' => '<p>'.__('The first line of this file should be CSV headers; e.g. <code>"email", "post_id", "status"</code>', $this->plugin->text_domain).'</p>',
						                'notes_after'  => '<p>'.__('If you upload a file, it will be used instead of any direct input above; i.e. a file takes precedence over direct input.', $this->plugin->text_domain).'</p>',
					                )).
				                '   </tbody>'.
				                ' </table>';

				if(!$this->plugin->options['auto_confirm_force_enable'])
				{
					$_panel_body .= ' <hr />';

					$_panel_body .= '  <table>'.
					                '    <tbody>'.
					                $_form_fields->input_row(
						                array(
							                'type'           => 'checkbox',
							                'label'          => __('Process Email Confirmations?', $this->plugin->text_domain),
							                'checkbox_label' => __('Yes, send an email confirmation to anyone being inserted or updated with an <code>unconfirmed</code> status.', $this->plugin->text_domain),
							                'name'           => 'process_confirmations',
							                'current_value'  => '1',
							                'notes_before'   => '<p class="pmp-note pmp-warning">'.__('<strong>Warning:</strong> Please be cautious with this choice. If you import new subscriptions and don\'t specify a particular status, the default status is <code>unconfirmed</code>. Thus, checking this box will attempt to confirm new subscriptions via email. Depending on the number of subscriptions you\'re importing, this could be a very large number of emails going out all at one time! Please use this with extreme caution.', $this->plugin->text_domain).'</p>'
						                )).
					                '    </tbody>'.
					                '  </table>';
				}
				$_panel_body .= ' <hr />';

				$_panel_body .= ' <div style="display:none;">'.
				                '  '.$_form_fields->hidden_input(array('name' => 'type', 'current_value' => 'subs')).
				                ' </div>';

				$_panel_body .= ' <button type="submit" style="width:100%;">'.
				                '  '.__('Import Now', $this->plugin->text_domain).' <i class="fa fa-upload"></i>'.
				                ' </button>';

				$_panel_body .= '</form>';

				echo $this->panel(__('CSV Import and/or Mass Update', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-upload"></i>'));

				unset($_form_field_args, $_form_fields, $_panel_body); // Housekeeping.

				/* ----------------------------------------------------------------------------------------- */

				if(import_stcr::data_exists())
				{
					$_form_field_args = array(
						'ns_id_suffix'   => '-import-stcr-form',
						'ns_name_suffix' => '[import]',
						'class_prefix'   => 'pmp-import-stcr-form-',
					);
					$_form_fields     = new form_fields($_form_field_args);

					$_panel_body = '<form method="post" enctype="multipart/form-data" action="'.esc_attr($this->plugin->utils_url->page_nonce_only()).'"'.
					               ' target="'.esc_attr(__NAMESPACE__.'_import_stcr_iframe').'" novalidate="novalidate">'."\n";

					$_panel_body .= ' <table style="table-layout:auto;">'.
					                '    <tbody>'.
					                '       <tr>';
					$_panel_body .= '          <td style="white-space:nowrap;">'.
					                '             <button type="submit" class="pmp-left">'.
					                '                '.__('Begin StCR Auto-Importation', $this->plugin->text_domain).' <i class="fa fa-magic"></i>'.
					                '             </button>'.
					                '          </td>';
					$_panel_body .= '          <td style="width:100%;">'.
					                '             <p>'.sprintf(__('%1$s&trade; has detected that you have data in your WordPress database tables containing comment subscriptions associated with Subscribe to Comments Reloaded (StCR). If you would like %1$s to import this data automagically, please click this button to proceed.', $this->plugin->text_domain), esc_html($this->plugin->name)).'</p>'.
					                '          </td>';
					$_panel_body .= '       </tr>'.
					                '    </tbody>'.
					                ' </table>';

					$_panel_body .= ' <div style="display:none;">'.
					                '  '.$_form_fields->hidden_input(array('name' => 'type', 'current_value' => 'stcr')).
					                ' </div>';

					$_panel_body .= ' <p class="pmp-note pmp-info" style="font-size:90%;">'.sprintf(__('<strong>Note:</strong> This process may take several minutes. %1$s will work through each post in your database, collecting all of the StCR subscriptions that exist (just a few at a time to prevent any script timeouts). The status bar below may refresh several times during this process. When it\'s complete, you should see a message that reads "<strong>Import complete!</strong>", along with a few details regarding the importation. When it is finished, you may <a href="%2$s">click here</a> to view a list of all subscriptions; which will include any that were imported from StCR. If the importation is interrupted for any reason, you may simply click the button again and %1$s will resume where it left off.', $this->plugin->text_domain), esc_html($this->plugin->name), esc_attr($this->plugin->utils_url->subs_menu_page_only())).'</p>';

					$_panel_body .= '</form>';

					$_panel_body .= '<iframe src="'.esc_attr($this->plugin->utils_url->to('/client-s/blanks/cccccc.html')).'" name="'.esc_attr(__NAMESPACE__.'_import_stcr_iframe').'" class="pmp-import-iframe-output"></iframe>';

					echo $this->panel(__('Subscribe to Comments Reloaded (StCR)', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-upload"></i>', 'open' => !import_stcr::ever_imported()));

					unset($_form_field_args, $_form_fields, $_panel_body); // Housekeeping.
				}
				/* ----------------------------------------------------------------------------------------- */

				$_form_field_args = array(
					'ns_id_suffix'   => '-export-subs-form',
					'ns_name_suffix' => '[export]',
					'class_prefix'   => 'pmp-export-subs-form-',
				);
				$_form_fields     = new form_fields($_form_field_args);

				$_panel_body = '<form method="post" enctype="multipart/form-data" action="'.esc_attr($this->plugin->utils_url->page_nonce_only()).'" novalidate="novalidate">'."\n";

				$_total_subs_in_db = $this->plugin->utils_sub->query_total(NULL, array('auto_discount_trash' => FALSE));
				$_panel_body .= ' <h3 style="margin-bottom:0;">'.sprintf(__('Export All of your %1$s&trade; Subscriptions', $this->plugin->text_domain), esc_html($this->plugin->name)).'</h3>';
				$_panel_body .= ' <p>'.sprintf(__('There are currently %1$s in the database. You can export these in sets of however many you like, as configured below.', $this->plugin->text_domain), esc_html($this->plugin->utils_i18n->subscriptions($_total_subs_in_db))).'</p>';

				$_panel_body .= ' <table>'.
				                '    <tbody>'.
				                $_form_fields->input_row(
					                array(
						                'type'          => 'number',
						                'label'         => __('Start Position:', $this->plugin->text_domain),
						                'placeholder'   => __('e.g. 1', $this->plugin->text_domain),
						                'name'          => 'start_from',
						                'current_value' => '1',
						                'other_attrs'   => 'min="1"',
						                'notes_after'   => '<p>'.__('e.g. If you already downloaded the first 1000, set this to <code>1001</code> to export the next set.', $this->plugin->text_domain).'</p>'
					                )).
				                '    </tbody>'.
				                ' </table>';

				$_panel_body .= '  <table>'.
				                '    <tbody>'.
				                $_form_fields->input_row(
					                array(
						                'type'          => 'number',
						                'label'         => __('Max Subscriptions in this Set:', $this->plugin->text_domain),
						                'placeholder'   => __('e.g. 1000', $this->plugin->text_domain),
						                'name'          => 'max_limit',
						                'current_value' => '1000',
						                'other_attrs'   => 'min="1" max="5000"',
						                'notes_after'   => '<p>'.__('e.g. If you start from <code>1</code> and set this to <code>1000</code>, you will get the first 1000 DB rows. If you want the next 1000 rows, set Start Position to <code>1001</code> and leave this as-is.', $this->plugin->text_domain).'</p>'.
						                                   '<p class="pmp-note pmp-warning">'.__('<strong>Upper Limit:</strong> There is an upper limit of <code>5000</code> per file to prevent extremely slow DB queries; i.e. you cannot set this higher than <code>5000</code>.', $this->plugin->text_domain).'</p>'
					                )).
				                '    </tbody>'.
				                '  </table>';

				$_panel_body .= ' <hr />';

				$_panel_body .= '  <table>'.
				                '    <tbody>'.
				                $_form_fields->input_row(
					                array(
						                'type'           => 'checkbox',
						                'label'          => __('Include UTF-8 BOM (Byte Order Marker)?', $this->plugin->text_domain),
						                'checkbox_label' => __('Yes, my spreadsheet application needs this to detect UTF-8 encoding properly.', $this->plugin->text_domain),
						                'name'           => 'include_utf8_bom',
						                'current_value'  => '1',
					                )).
				                '    </tbody>'.
				                '  </table>';

				$_panel_body .= ' <hr />';

				$_panel_body .= ' <div style="display:none;">'.
				                '  '.$_form_fields->hidden_input(array('name' => 'type', 'current_value' => 'subs')).
				                ' </div>';

				$_panel_body .= ' <button type="submit" style="width:100%;">'.
				                '  '.__('Download CSV Export File', $this->plugin->text_domain).' <i class="fa fa-download"></i>'.
				                ' </button>';

				$_panel_body .= '</form>';

				echo $this->panel(__('CSV Export (File Download)', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-download"></i>'));

				unset($_form_field_args, $_form_fields, $_total_subs_in_db, $_panel_body); // Housekeeping.

				/* ----------------------------------------------------------------------------------------- */

				echo '      <h2 class="pmp-section-heading">'.
				     '         '.__('Import/Export Config. Options', $this->plugin->text_domain).
				     '         <small>'.sprintf(__('This allows you to import/export %1$s&trade; configuration options.', $this->plugin->text_domain), esc_html($this->plugin->name)).'</small>'.
				     '      </h2>';

				/* --------------------------------------------------------------------------------------------------------------------------------------------------------------------------- */

				$_form_field_args = array(
					'ns_id_suffix'   => '-import-ops-form',
					'ns_name_suffix' => '[import]',
					'class_prefix'   => 'pmp-import-ops-form-',
				);
				$_form_fields     = new form_fields($_form_field_args);

				$_panel_body = '<form method="post" enctype="multipart/form-data" action="'.esc_attr($this->plugin->utils_url->page_nonce_only()).'" novalidate="novalidate">'."\n";

				$_panel_body .= ' <h3 style="margin-bottom:0;">'.sprintf(__('Import a New Set of %1$s&trade; Config. Options', $this->plugin->text_domain), esc_html($this->plugin->name)).'</h3>';
				$_panel_body .= ' <p>'.sprintf(__('Configuration options are imported using a JSON-encoded file obtained from another copy of %1$s&trade;.', $this->plugin->text_domain), esc_html($this->plugin->name)).'</p>';
				$_panel_body .= ' <p class="pmp-note pmp-info" style="font-size:90%;">'.sprintf(__('<strong>Tip:</strong> To save time you can import your options from another WordPress installation where you\'ve already configured %1$s&trade; before.', $this->plugin->text_domain), esc_html($this->plugin->name)).'</p>';

				$_panel_body .= ' <table>'.
				                '   <tbody>'.
				                $_form_fields->input_row(
					                array(
						                'type'        => 'file',
						                'label'       => __('JSON Config. Options File:', $this->plugin->text_domain),
						                'placeholder' => __('e.g. config-options.json', $this->plugin->text_domain),
						                'name'        => 'data_file',
					                )).
				                '   </tbody>'.
				                ' </table>';

				$_panel_body .= ' <div style="display:none;">'.
				                '  '.$_form_fields->hidden_input(array('name' => 'type', 'current_value' => 'ops')).
				                ' </div>';

				$_panel_body .= ' <button type="submit" style="width:100%;">'.
				                '  '.__('Import JSON Config. Options File', $this->plugin->text_domain).' <i class="fa fa-upload"></i>'.
				                ' </button>';

				$_panel_body .= '</form>';

				echo $this->panel(__('Import Config. Options', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-upload"></i>'));

				unset($_form_field_args, $_form_fields, $_panel_body); // Housekeeping.

				/* ----------------------------------------------------------------------------------------- */

				$_form_field_args = array(
					'ns_id_suffix'   => '-export-ops-form',
					'ns_name_suffix' => '[export]',
					'class_prefix'   => 'pmp-export-ops-form-',
				);
				$_form_fields     = new form_fields($_form_field_args);

				$_panel_body = '<form method="post" enctype="multipart/form-data" action="'.esc_attr($this->plugin->utils_url->page_nonce_only()).'" novalidate="novalidate">'."\n";

				$_panel_body .= ' <h3 style="margin-bottom:0;">'.sprintf(__('Export All of your %1$s&trade; Config. Options', $this->plugin->text_domain), esc_html($this->plugin->name)).'</h3>';
				$_panel_body .= ' <p>'.__('Configuration options are downloaded as a JSON-encoded file.', $this->plugin->text_domain).'</p>';
				$_panel_body .= ' <p class="pmp-note pmp-info" style="font-size:90%;">'.__('<strong>Tip:</strong> Export your configuration on this site, and then import it into another WordPress installation to save time in the future.', $this->plugin->text_domain).'</p>';

				$_panel_body .= ' <div style="display:none;">'.
				                '  '.$_form_fields->hidden_input(array('name' => 'type', 'current_value' => 'ops')).
				                ' </div>';

				$_panel_body .= ' <button type="submit" style="width:100%;">'.
				                '  '.__('Download JSON Config. Options File', $this->plugin->text_domain).' <i class="fa fa-download"></i>'.
				                ' </button>';

				$_panel_body .= '</form>';

				echo $this->panel(__('Export Config. Options', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-download"></i>'));

				unset($_form_field_args, $_form_fields, $_panel_body); // Housekeeping.

				/* ----------------------------------------------------------------------------------------- */

				echo '   </div>'."\n";
				echo '</div>';
			}

			/**
			 * Displays menu page.
			 *
			 * @since 141111 First documented version.
			 */
			protected function email_templates_()
			{
				$_this             = $this;
				$form_field_args   = array(
					'ns_id_suffix'   => '-email-templates-form',
					'ns_name_suffix' => '[save_options]',
					'class_prefix'   => 'pmp-email-templates-form-',
				);
				$form_fields       = new form_fields($form_field_args);
				$current_value_for = function ($key) use ($_this)
				{
					if(strpos($key, 'template__') === 0)
						if(isset($_this->plugin->options[$key]))
						{
							if($_this->plugin->options[$key])
								return $_this->plugin->options[$key];

							$file             = template::option_key_to_file($key);
							$default_template = new template($file, TRUE);

							return $default_template->file_contents();
						}
					return isset($_this->plugin->options[$key]) ? $_this->plugin->options[$key] : NULL;
				};
				/* ----------------------------------------------------------------------------------------- */

				echo '<div class="'.esc_attr($this->plugin->slug.'-menu-page '.$this->plugin->slug.'-menu-page-email-templates '.$this->plugin->slug.'-menu-page-area').'">'."\n";
				echo '   <form method="post" enctype="multipart/form-data" action="'.esc_attr($this->plugin->utils_url->page_nonce_only()).'" novalidate="novalidate">'."\n";

				echo '      '.$this->heading(__('Email Templates', $this->plugin->text_domain), 'logo.png').
				     '      '.$this->notes(); // Heading/notifications.

				echo '      <div class="pmp-body">'."\n";

				echo '         '.$this->all_panel_togglers();

				/* ----------------------------------------------------------------------------------------- */

				echo '         <h2 class="pmp-section-heading">'.
				     '            '.__('Email Header/Footer Templates', $this->plugin->text_domain).
				     '            <small>'.__('These are used in all emails; i.e. global header/footer.', $this->plugin->text_domain).'</small>'.
				     '         </h2>';

				/* --------------------------------------------------------------------------------------------------------------------------------------------------------------------------- */

				$_panel_body = '<table>'.
				               '  <tbody>'.
				               $form_fields->textarea_row(
					               array(
						               'label'         => __('Email Header Template', $this->plugin->text_domain),
						               'placeholder'   => __('Template Content...', $this->plugin->text_domain),
						               'cm_mode'       => 'application/x-httpd-php',
						               'name'          => 'template__email__email_header',
						               'current_value' => $current_value_for('template__email__email_header'),
						               'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for popular email clients; i.e. you shouldn\'t need to customize. However, if don\'t like the defaults; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>',
						               'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
					               )).
				               '  </tbody>'.
				               '</table>';

				echo $this->panel(__('Email Header', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-code"></i>'));

				unset($_panel_body); // Housekeeping.

				/* ----------------------------------------------------------------------------------------- */

				$_panel_body = '<table>'.
				               '  <tbody>'.
				               $form_fields->textarea_row(
					               array(
						               'label'         => __('Email Header Styles Template', $this->plugin->text_domain),
						               'placeholder'   => __('Template Content...', $this->plugin->text_domain),
						               'cm_mode'       => 'application/x-httpd-php',
						               'name'          => 'template__email__email_header_styles',
						               'current_value' => $current_value_for('template__email__email_header_styles'),
						               'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for popular email clients; i.e. you shouldn\'t need to customize. However, if don\'t like the defaults; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>',
						               'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
					               )).
				               '  </tbody>'.
				               '</table>';

				echo $this->panel(__('Email Header Styles', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-code"></i>'));

				unset($_panel_body); // Housekeeping.

				/* ----------------------------------------------------------------------------------------- */

				$_panel_body = '<table>'.
				               '  <tbody>'.
				               $form_fields->textarea_row(
					               array(
						               'label'         => __('Email Header Scripts Template', $this->plugin->text_domain),
						               'placeholder'   => __('Template Content...', $this->plugin->text_domain),
						               'cm_mode'       => 'application/x-httpd-php',
						               'name'          => 'template__email__email_header_scripts',
						               'current_value' => $current_value_for('template__email__email_header_scripts'),
						               'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for popular email clients; i.e. you shouldn\'t need to customize. However, if don\'t like the defaults; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>',
						               'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
					               )).
				               '  </tbody>'.
				               '</table>';

				echo $this->panel(__('Email Header Scripts', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-code"></i>'));

				unset($_panel_body); // Housekeeping.

				/* ----------------------------------------------------------------------------------------- */

				$_panel_body = '<table>'.
				               '  <tbody>'.
				               $form_fields->textarea_row(
					               array(
						               'label'         => __('Email Header Easy Template', $this->plugin->text_domain),
						               'placeholder'   => __('Template Content...', $this->plugin->text_domain),
						               'cm_mode'       => 'application/x-httpd-php',
						               'name'          => 'template__email__email_header_easy',
						               'current_value' => $current_value_for('template__email__email_header_easy'),
						               'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for popular email clients; i.e. you shouldn\'t need to customize. However, if don\'t like the defaults; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>',
						               'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
					               )).
				               '  </tbody>'.
				               '</table>';

				echo $this->panel(__('Email Header Easy', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-code"></i>', 'note' => 'Recommended for Simple Branding Changes'));

				unset($_panel_body); // Housekeeping.

				/* ----------------------------------------------------------------------------------------- */

				$_panel_body = '<table>'.
				               '  <tbody>'.
				               $form_fields->textarea_row(
					               array(
						               'label'         => __('Email Footer Easy Template', $this->plugin->text_domain),
						               'placeholder'   => __('Template Content...', $this->plugin->text_domain),
						               'cm_mode'       => 'application/x-httpd-php',
						               'name'          => 'template__email__email_footer_easy',
						               'current_value' => $current_value_for('template__email__email_footer_easy'),
						               'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for popular email clients; i.e. you shouldn\'t need to customize. However, if don\'t like the defaults; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>',
						               'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
					               )).
				               '  </tbody>'.
				               '</table>';

				echo $this->panel(__('Email Footer Easy', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-code"></i>', 'note' => 'Recommended for Simple Branding Changes'));

				unset($_panel_body); // Housekeeping.

				/* ----------------------------------------------------------------------------------------- */

				$_panel_body = '<table>'.
				               '  <tbody>'.
				               $form_fields->textarea_row(
					               array(
						               'label'         => __('Email Footer Template', $this->plugin->text_domain),
						               'placeholder'   => __('Template Content...', $this->plugin->text_domain),
						               'cm_mode'       => 'application/x-httpd-php',
						               'name'          => 'template__email__email_footer',
						               'current_value' => $current_value_for('template__email__email_footer'),
						               'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for popular email clients; i.e. you shouldn\'t need to customize. However, if don\'t like the defaults; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>',
						               'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
					               )).
				               '  </tbody>'.
				               '</table>';

				echo $this->panel(__('Email Footer', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-code"></i>'));

				unset($_panel_body); // Housekeeping.

				/* ----------------------------------------------------------------------------------------- */

				echo '         <h2 class="pmp-section-heading">'.
				     '            '.__('Email Subscr. Confirmation Templates', $this->plugin->text_domain).
				     '            <small>'.sprintf(__('Email subject line &amp; message used in %1$s&trade; confirmation requests.', $this->plugin->text_domain), esc_html($this->plugin->name)).'</small>'.
				     '         </h2>';

				/* --------------------------------------------------------------------------------------------------------------------------------------------------------------------------- */

				$_panel_body = '<table>'.
				               '  <tbody>'.
				               $form_fields->textarea_row(
					               array(
						               'label'         => __('Subscr. Confirmation Subject Line Template', $this->plugin->text_domain),
						               'placeholder'   => __('Template Content...', $this->plugin->text_domain),
						               'cm_mode'       => 'application/x-httpd-php',
						               'name'          => 'template__email__sub_confirmation__subject',
						               'current_value' => $current_value_for('template__email__sub_confirmation__subject'),
						               'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for popular email clients; i.e. you shouldn\'t need to customize. However, if don\'t like the defaults; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>',
						               'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
					               )).
				               '  </tbody>'.
				               '</table>';

				echo $this->panel(__('Subscr. Confirmation Subject', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-code"></i>'));

				unset($_panel_body); // Housekeeping.

				/* ----------------------------------------------------------------------------------------- */

				$_panel_body = '<table>'.
				               '  <tbody>'.
				               $form_fields->textarea_row(
					               array(
						               'label'         => __('Subscr. Confirmation Message Template', $this->plugin->text_domain),
						               'placeholder'   => __('Template Content...', $this->plugin->text_domain),
						               'cm_mode'       => 'application/x-httpd-php',
						               'name'          => 'template__email__sub_confirmation__message',
						               'current_value' => $current_value_for('template__email__sub_confirmation__message'),
						               'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for popular email clients; i.e. you shouldn\'t need to customize. However, if don\'t like the defaults; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>',
						               'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
					               )).
				               '  </tbody>'.
				               '</table>';

				echo $this->panel(__('Subscr. Confirmation Message', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-code"></i>'));

				unset($_panel_body); // Housekeeping.

				/* ----------------------------------------------------------------------------------------- */

				echo '         <h2 class="pmp-section-heading">'.
				     '            '.__('Comment Notification Email Templates', $this->plugin->text_domain).
				     '            <small>'.sprintf(__('Email subject line &amp; message used in %1$s&trade; notifications.', $this->plugin->text_domain), esc_html($this->plugin->name)).'</small>'.
				     '         </h2>';

				/* --------------------------------------------------------------------------------------------------------------------------------------------------------------------------- */

				$_panel_body = '<table>'.
				               '  <tbody>'.
				               $form_fields->textarea_row(
					               array(
						               'label'         => __('Comment Notification Subject Line Template', $this->plugin->text_domain),
						               'placeholder'   => __('Template Content...', $this->plugin->text_domain),
						               'cm_mode'       => 'application/x-httpd-php',
						               'name'          => 'template__email__comment_notification__subject',
						               'current_value' => $current_value_for('template__email__comment_notification__subject'),
						               'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for popular email clients; i.e. you shouldn\'t need to customize. However, if don\'t like the defaults; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>',
						               'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
					               )).
				               '  </tbody>'.
				               '</table>';

				echo $this->panel(__('Comment Notification Subject', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-code"></i>'));

				unset($_panel_body); // Housekeeping.

				/* ----------------------------------------------------------------------------------------- */

				$_panel_body = '<table>'.
				               '  <tbody>'.
				               $form_fields->textarea_row(
					               array(
						               'label'         => __('Comment Notification Message Template', $this->plugin->text_domain),
						               'placeholder'   => __('Template Content...', $this->plugin->text_domain),
						               'cm_mode'       => 'application/x-httpd-php',
						               'name'          => 'template__email__comment_notification__message',
						               'current_value' => $current_value_for('template__email__comment_notification__message'),
						               'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for popular email clients; i.e. you shouldn\'t need to customize. However, if don\'t like the defaults; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>',
						               'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
					               )).
				               '  </tbody>'.
				               '</table>';

				echo $this->panel(__('Comment Notification Message', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-code"></i>'));

				unset($_panel_body); // Housekeeping.

				/* ----------------------------------------------------------------------------------------- */

				echo '         <div class="pmp-save">'."\n";
				echo '            <button type="submit">'.__('Save All Changes', $this->plugin->text_domain).' <i class="fa fa-save"></i></button>'."\n";
				echo '         </div>'."\n";

				echo '      </div>'."\n";
				echo '   </form>'."\n";
				echo '</div>';
			}

			/**
			 * Displays menu page.
			 *
			 * @since 141111 First documented version.
			 */
			protected function site_templates_()
			{
				$_this             = $this;
				$form_field_args   = array(
					'ns_id_suffix'   => '-site-templates-form',
					'ns_name_suffix' => '[save_options]',
					'class_prefix'   => 'pmp-site-templates-form-',
				);
				$form_fields       = new form_fields($form_field_args);
				$current_value_for = function ($key) use ($_this)
				{
					if(strpos($key, 'template__') === 0)
						if(isset($_this->plugin->options[$key]))
						{
							if($_this->plugin->options[$key])
								return $_this->plugin->options[$key];

							$file             = template::option_key_to_file($key);
							$default_template = new template($file, TRUE);

							return $default_template->file_contents();
						}
					return isset($_this->plugin->options[$key]) ? $_this->plugin->options[$key] : NULL;
				};
				/* ----------------------------------------------------------------------------------------- */

				echo '<div class="'.esc_attr($this->plugin->slug.'-menu-page '.$this->plugin->slug.'-menu-page-site-templates '.$this->plugin->slug.'-menu-page-area').'">'."\n";
				echo '   <form method="post" enctype="multipart/form-data" action="'.esc_attr($this->plugin->utils_url->page_nonce_only()).'" novalidate="novalidate">'."\n";

				echo '      '.$this->heading(__('Site Templates', $this->plugin->text_domain), 'logo.png').
				     '      '.$this->notes(); // Heading/notifications.

				echo '      <div class="pmp-body">'."\n";

				echo '         '.$this->all_panel_togglers();

				/* ----------------------------------------------------------------------------------------- */

				echo '         <h2 class="pmp-section-heading">'.
				     '            '.__('Site Header/Footer Templates', $this->plugin->text_domain).
				     '            <small>'.__('These are used in all portions of the front-end UI; i.e. global header/footer.', $this->plugin->text_domain).'</small>'.
				     '         </h2>';

				/* --------------------------------------------------------------------------------------------------------------------------------------------------------------------------- */

				$_panel_body = '<table>'.
				               '  <tbody>'.
				               $form_fields->textarea_row(
					               array(
						               'label'         => __('Site Header Template', $this->plugin->text_domain),
						               'placeholder'   => __('Template Content...', $this->plugin->text_domain),
						               'cm_mode'       => 'application/x-httpd-php',
						               'name'          => 'template__site__site_header',
						               'current_value' => $current_value_for('template__site__site_header'),
						               'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for most WordPress installs; i.e. you shouldn\'t need to customize. However, if you don\'t like the defaults; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>',
						               'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
					               )).
				               '  </tbody>'.
				               '</table>';

				echo $this->panel(__('Site Header', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-code"></i>'));

				unset($_panel_body); // Housekeeping.

				/* ----------------------------------------------------------------------------------------- */

				$_panel_body = '<table>'.
				               '  <tbody>'.
				               $form_fields->textarea_row(
					               array(
						               'label'         => __('Site Header Styles Template', $this->plugin->text_domain),
						               'placeholder'   => __('Template Content...', $this->plugin->text_domain),
						               'cm_mode'       => 'application/x-httpd-php',
						               'name'          => 'template__site__site_header_styles',
						               'current_value' => $current_value_for('template__site__site_header_styles'),
						               'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for most WordPress installs; i.e. you shouldn\'t need to customize. However, if you don\'t like the defaults; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>',
						               'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
					               )).
				               '  </tbody>'.
				               '</table>';

				echo $this->panel(__('Site Header Styles', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-code"></i>'));

				unset($_panel_body); // Housekeeping.

				/* ----------------------------------------------------------------------------------------- */

				$_panel_body = '<table>'.
				               '  <tbody>'.
				               $form_fields->textarea_row(
					               array(
						               'label'         => __('Site Header Scripts Template', $this->plugin->text_domain),
						               'placeholder'   => __('Template Content...', $this->plugin->text_domain),
						               'cm_mode'       => 'application/x-httpd-php',
						               'name'          => 'template__site__site_header_scripts',
						               'current_value' => $current_value_for('template__site__site_header_scripts'),
						               'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for most WordPress installs; i.e. you shouldn\'t need to customize. However, if you don\'t like the defaults; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>',
						               'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
					               )).
				               '  </tbody>'.
				               '</table>';

				echo $this->panel(__('Site Header Scripts', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-code"></i>'));

				unset($_panel_body); // Housekeeping.

				/* ----------------------------------------------------------------------------------------- */

				$_panel_body = '<table>'.
				               '  <tbody>'.
				               $form_fields->textarea_row(
					               array(
						               'label'         => __('Site Header Easy Template', $this->plugin->text_domain),
						               'placeholder'   => __('Template Content...', $this->plugin->text_domain),
						               'cm_mode'       => 'application/x-httpd-php',
						               'name'          => 'template__site__site_header_easy',
						               'current_value' => $current_value_for('template__site__site_header_easy'),
						               'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for most WordPress installs; i.e. you shouldn\'t need to customize. However, if you don\'t like the defaults; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>',
						               'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
					               )).
				               '  </tbody>'.
				               '</table>';

				echo $this->panel(__('Site Header Easy', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-code"></i>', 'note' => 'Recommended for Simple Branding Changes'));

				unset($_panel_body); // Housekeeping.

				/* ----------------------------------------------------------------------------------------- */

				$_panel_body = '<table>'.
				               '  <tbody>'.
				               $form_fields->textarea_row(
					               array(
						               'label'         => __('Site Footer Easy Template', $this->plugin->text_domain),
						               'placeholder'   => __('Template Content...', $this->plugin->text_domain),
						               'cm_mode'       => 'application/x-httpd-php',
						               'name'          => 'template__site__site_footer_easy',
						               'current_value' => $current_value_for('template__site__site_footer_easy'),
						               'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for most WordPress installs; i.e. you shouldn\'t need to customize. However, if you don\'t like the defaults; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>',
						               'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
					               )).
				               '  </tbody>'.
				               '</table>';

				echo $this->panel(__('Site Footer Easy', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-code"></i>', 'note' => 'Recommended for Simple Branding Changes'));

				unset($_panel_body); // Housekeeping.

				/* ----------------------------------------------------------------------------------------- */

				$_panel_body = '<table>'.
				               '  <tbody>'.
				               $form_fields->textarea_row(
					               array(
						               'label'         => __('Site Footer Template', $this->plugin->text_domain),
						               'placeholder'   => __('Template Content...', $this->plugin->text_domain),
						               'cm_mode'       => 'application/x-httpd-php',
						               'name'          => 'template__site__site_footer',
						               'current_value' => $current_value_for('template__site__site_footer'),
						               'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for most WordPress installs; i.e. you shouldn\'t need to customize. However, if you don\'t like the defaults; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>',
						               'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
					               )).
				               '  </tbody>'.
				               '</table>';

				echo $this->panel(__('Site Footer', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-code"></i>'));

				unset($_panel_body); // Housekeeping.

				/* ----------------------------------------------------------------------------------------- */

				echo '         <h2 class="pmp-section-heading">'.
				     '            '.__('Subscr. Action Templates', $this->plugin->text_domain).
				     '            <small>'.__('These are shown to a subscriber when they confirm and/or unsubscribe.', $this->plugin->text_domain).'</small>'.
				     '         </h2>';

				/* --------------------------------------------------------------------------------------------------------------------------------------------------------------------------- */

				$_panel_body = '<table>'.
				               '  <tbody>'.
				               $form_fields->textarea_row(
					               array(
						               'label'         => __('Subscr. Confirmed Template', $this->plugin->text_domain),
						               'placeholder'   => __('Template Content...', $this->plugin->text_domain),
						               'cm_mode'       => 'application/x-httpd-php',
						               'name'          => 'template__site__sub_actions__confirmed',
						               'current_value' => $current_value_for('template__site__sub_actions__confirmed'),
						               'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for most WordPress themes; i.e. you shouldn\'t need to customize. However, if your theme is not playing well with the default; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>',
						               'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
					               )).
				               '  </tbody>'.
				               '</table>';

				echo $this->panel(__('Subscr. Confirmed', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-code"></i>'));

				unset($_panel_body); // Housekeeping.

				/* ----------------------------------------------------------------------------------------- */

				$_panel_body = '<table>'.
				               '  <tbody>'.
				               $form_fields->textarea_row(
					               array(
						               'label'         => __('Unsubscribed Template', $this->plugin->text_domain),
						               'placeholder'   => __('Template Content...', $this->plugin->text_domain),
						               'cm_mode'       => 'application/x-httpd-php',
						               'name'          => 'template__site__sub_actions__unsubscribed',
						               'current_value' => $current_value_for('template__site__sub_actions__unsubscribed'),
						               'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for most WordPress themes; i.e. you shouldn\'t need to customize. However, if your theme is not playing well with the default; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>',
						               'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
					               )).
				               '  </tbody>'.
				               '</table>';

				echo $this->panel(__('Unsubscribed', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-code"></i>'));

				unset($_panel_body); // Housekeeping.

				/* ----------------------------------------------------------------------------------------- */

				$_panel_body = '<table>'.
				               '  <tbody>'.
				               $form_fields->textarea_row(
					               array(
						               'label'         => __('Unsubscribed All Template', $this->plugin->text_domain),
						               'placeholder'   => __('Template Content...', $this->plugin->text_domain),
						               'cm_mode'       => 'application/x-httpd-php',
						               'name'          => 'template__site__sub_actions__unsubscribed_all',
						               'current_value' => $current_value_for('template__site__sub_actions__unsubscribed_all'),
						               'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for most WordPress themes; i.e. you shouldn\'t need to customize. However, if your theme is not playing well with the default; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>',
						               'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
					               )).
				               '  </tbody>'.
				               '</table>';

				echo $this->panel(__('Unsubscribed All', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-code"></i>'));

				unset($_panel_body); // Housekeeping.

				/* ----------------------------------------------------------------------------------------- */

				echo '         <h2 class="pmp-section-heading">'.
				     '            '.__('Subscr. Summary Templates', $this->plugin->text_domain).
				     '            <small>'.__('Related to the Summary (aka: "My Subscriptions") page and add/edit form.', $this->plugin->text_domain).'</small>'.
				     '         </h2>';

				/* --------------------------------------------------------------------------------------------------------------------------------------------------------------------------- */

				$_panel_body = '<table>'.
				               '  <tbody>'.
				               $form_fields->textarea_row(
					               array(
						               'label'         => __('Summary Template', $this->plugin->text_domain),
						               'placeholder'   => __('Template Content...', $this->plugin->text_domain),
						               'cm_mode'       => 'application/x-httpd-php',
						               'name'          => 'template__site__sub_actions__manage_summary',
						               'current_value' => $current_value_for('template__site__sub_actions__manage_summary'),
						               'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for most WordPress themes; i.e. you shouldn\'t need to customize. However, if your theme is not playing well with the default; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>',
						               'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
					               )).
				               '  </tbody>'.
				               '</table>';

				echo $this->panel(__('Summary', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-code"></i>'));

				unset($_panel_body); // Housekeeping.

				/* ----------------------------------------------------------------------------------------- */

				$_panel_body = '<table>'.
				               '  <tbody>'.
				               $form_fields->textarea_row(
					               array(
						               'label'         => __('Add/Edit Form Template', $this->plugin->text_domain),
						               'placeholder'   => __('Template Content...', $this->plugin->text_domain),
						               'cm_mode'       => 'application/x-httpd-php',
						               'name'          => 'template__site__sub_actions__manage_sub_form',
						               'current_value' => $current_value_for('template__site__sub_actions__manage_sub_form'),
						               'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for most WordPress themes; i.e. you shouldn\'t need to customize. However, if your theme is not playing well with the default; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>',
						               'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
					               )).
				               '  </tbody>'.
				               '</table>';

				echo $this->panel(__('Add/Edit Form', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-code"></i>'));

				unset($_panel_body); // Housekeeping.

				/* ----------------------------------------------------------------------------------------- */

				$_panel_body = '<table>'.
				               '  <tbody>'.
				               $form_fields->textarea_row(
					               array(
						               'label'         => __('Add/Edit Form Template (Comment ID Row via AJAX)', $this->plugin->text_domain),
						               'placeholder'   => __('Template Content...', $this->plugin->text_domain),
						               'cm_mode'       => 'application/x-httpd-php',
						               'name'          => 'template__site__sub_actions__manage_sub_form_comment_id_row_via_ajax',
						               'current_value' => $current_value_for('template__site__sub_actions__manage_sub_form_comment_id_row_via_ajax'),
						               'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for most WordPress themes; i.e. you shouldn\'t need to customize. However, if your theme is not playing well with the default; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>',
						               'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
					               )).
				               '  </tbody>'.
				               '</table>';

				echo $this->panel(__('Comment ID Row via AJAX', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-code"></i>'));

				unset($_panel_body); // Housekeeping.

				/* ----------------------------------------------------------------------------------------- */

				echo '         <h2 class="pmp-section-heading">'.
				     '            '.__('Comment Form Templates', $this->plugin->text_domain).
				     '            <small>'.__('Provides options that allow commenters to subscribe &amp; receive notifications.', $this->plugin->text_domain).'</small>'.
				     '         </h2>';

				/* --------------------------------------------------------------------------------------------------------------------------------------------------------------------------- */

				$_panel_body = '<table>'.
				               '  <tbody>'.
				               $form_fields->textarea_row(
					               array(
						               'label'         => __('Comment Form Subscr. Options Template', $this->plugin->text_domain),
						               'placeholder'   => __('Template Content...', $this->plugin->text_domain),
						               'cm_mode'       => 'application/x-httpd-php',
						               'name'          => 'template__site__comment_form__sub_ops',
						               'current_value' => $current_value_for('template__site__comment_form__sub_ops'),
						               'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for most WordPress themes; i.e. you shouldn\'t need to customize. However, if your theme is not playing well with the default; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>',
						               'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
					               )).
				               '  </tbody>'.
				               '</table>';

				echo $this->panel(__('Comment Form Subscr. Options', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-code"></i>'));

				unset($_panel_body); // Housekeeping.

				/* ----------------------------------------------------------------------------------------- */

				$_panel_body = '<table>'.
				               '  <tbody>'.
				               $form_fields->textarea_row(
					               array(
						               'label'         => __('Comment Form Scripts for Subscr. Options', $this->plugin->text_domain),
						               'placeholder'   => __('Template Content...', $this->plugin->text_domain),
						               'cm_mode'       => 'application/x-httpd-php',
						               'name'          => 'template__site__comment_form__sub_op_scripts',
						               'current_value' => $current_value_for('template__site__comment_form__sub_op_scripts'),
						               'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for most WordPress themes; i.e. you shouldn\'t need to customize. However, if your theme is not playing well with the default; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>',
						               'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
					               )).
				               '  </tbody>'.
				               '</table>';

				echo $this->panel(__('Comment Form Scripts for Subscr. Options', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-code"></i>'));

				unset($_panel_body); // Housekeeping.

				/* ----------------------------------------------------------------------------------------- */

				echo '         <div class="pmp-save">'."\n";
				echo '            <button type="submit">'.__('Save All Changes', $this->plugin->text_domain).' <i class="fa fa-save"></i></button>'."\n";
				echo '         </div>'."\n";

				echo '      </div>'."\n";
				echo '   </form>'."\n";
				echo '</div>';
			}

			/**
			 * Displays menu page.
			 *
			 * @since 141111 First documented version.
			 */
			protected function subs_()
			{
				switch(!empty($_REQUEST['action']) ? $_REQUEST['action'] : '')
				{
					case 'new': // Add new subscription.

						$this->_sub_new(); // Display form.

						break; // Break switch handler.

					case 'edit': // Edit existing subscription.

						$this->_sub_edit(); // Display form.

						break; // Break switch handler.

					case '': // Also the default case handler.
					default: // Everything else is handled by subs. table.

						echo '<div class="'.esc_attr($this->plugin->slug.'-menu-page-subs '.$this->plugin->slug.'-menu-page-table '.$this->plugin->slug.'-menu-page-area wrap').'">'."\n";
						echo '   <form method="post" enctype="multipart/form-data" action="'.esc_attr($this->plugin->utils_url->page_nonce_table_nav_vars_only()).'" novalidate="novalidate">'."\n";

						echo '      <h2>'.sprintf(__('%1$s&trade; &raquo; Subscriptions', $this->plugin->text_domain), esc_html($this->plugin->name)).' <i class="'.esc_attr('wsi-'.$this->plugin->slug).'"></i>'.
						     '       <a href="'.esc_attr($this->plugin->utils_url->new_sub_short()).'" class="add-new-h2">'.__('Add New', $this->plugin->text_domain).'</a></h2>'."\n";

						new menu_page_subs_table(); // Displays table.

						echo '   </form>';
						echo '</div>'."\n";
				}
			}

			/**
			 * Displays menu page.
			 *
			 * @since 141111 First documented version.
			 */
			protected function _sub_new()
			{
				echo '<div class="'.esc_attr($this->plugin->slug.'-menu-page-sub-new '.$this->plugin->slug.'-menu-page-form '.$this->plugin->slug.'-menu-page-area wrap').'">'."\n";
				echo '   <form method="post" enctype="multipart/form-data" action="'.esc_attr($this->plugin->utils_url->page_nonce_table_nav_vars_only(array('action'))).'" novalidate="novalidate">'."\n";

				echo '      <h2>'.sprintf(__('%1$s&trade; &raquo; New Subscription', $this->plugin->text_domain), esc_html($this->plugin->name)).' <i class="'.esc_attr('wsi-'.$this->plugin->slug.'-one').'"></i></h2>'."\n";

				new menu_page_sub_new_form(); // Displays form to add new subscription.

				echo '   </form>';
				echo '</div>'."\n";
			}

			/**
			 * Displays menu page.
			 *
			 * @since 141111 First documented version.
			 */
			protected function _sub_edit()
			{
				echo '<div class="'.esc_attr($this->plugin->slug.'-menu-page-sub-edit '.$this->plugin->slug.'-menu-page-form '.$this->plugin->slug.'-menu-page-area wrap').'">'."\n";
				echo '   <form method="post" enctype="multipart/form-data" action="'.esc_attr($this->plugin->utils_url->page_nonce_table_nav_vars_only(array('action', 'subscription'))).'" novalidate="novalidate">'."\n";

				echo '      <h2>'.sprintf(__('%1$s&trade; &raquo; Edit Subscription', $this->plugin->text_domain), esc_html($this->plugin->name)).' <i class="'.esc_attr('wsi-'.$this->plugin->slug.'-one').'"></i></h2>'."\n";

				new menu_page_sub_edit_form(!empty($_REQUEST['subscription']) ? (integer)$_REQUEST['subscription'] : 0); // Displays form.

				echo '   </form>';
				echo '</div>'."\n";
			}

			/**
			 * Displays menu page.
			 *
			 * @since 141111 First documented version.
			 */
			protected function sub_event_log_()
			{
				echo '<div class="'.esc_attr($this->plugin->slug.'-menu-page-sub-event-log '.$this->plugin->slug.'-menu-page-table '.$this->plugin->slug.'-menu-page-area wrap').'">'."\n";
				echo '   <form method="post" enctype="multipart/form-data" action="'.esc_attr($this->plugin->utils_url->page_nonce_table_nav_vars_only()).'" novalidate="novalidate">'."\n";

				echo '      <h2>'.sprintf(__('%1$s&trade; &raquo; Subscriptions &raquo; Event Log', $this->plugin->text_domain), esc_html($this->plugin->name)).' <i class="fa fa-history"></i></h2>'."\n";

				new menu_page_sub_event_log_table(); // Displays table.

				echo '   </form>';
				echo '</div>'."\n";
			}

			/**
			 * Displays menu page.
			 *
			 * @since 141111 First documented version.
			 */
			protected function queue_()
			{
				echo '<div class="'.esc_attr($this->plugin->slug.'-menu-page-queue '.$this->plugin->slug.'-menu-page-table '.$this->plugin->slug.'-menu-page-area wrap').'">'."\n";
				echo '   <form method="post" enctype="multipart/form-data" action="'.esc_attr($this->plugin->utils_url->page_nonce_table_nav_vars_only()).'" novalidate="novalidate">'."\n";

				echo '      <h2>'.sprintf(__('%1$s&trade; &raquo; Queued (Pending) Notifications', $this->plugin->text_domain), esc_html($this->plugin->name)).' <i class="fa fa-envelope-o"></i></h2>'."\n";

				new menu_page_queue_table(); // Displays table.

				echo '   </form>';
				echo '</div>'."\n";
			}

			/**
			 * Displays menu page.
			 *
			 * @since 141111 First documented version.
			 */
			protected function queue_event_log_()
			{
				echo '<div class="'.esc_attr($this->plugin->slug.'-menu-page-queue-event-log '.$this->plugin->slug.'-menu-page-table '.$this->plugin->slug.'-menu-page-area wrap').'">'."\n";
				echo '   <form method="post" enctype="multipart/form-data" action="'.esc_attr($this->plugin->utils_url->page_nonce_table_nav_vars_only()).'" novalidate="novalidate">'."\n";

				echo '      <h2>'.sprintf(__('%1$s&trade; &raquo; Queue &raquo; Event Log', $this->plugin->text_domain), esc_html($this->plugin->name)).' <i class="fa fa-paper-plane"></i></h2>'."\n";

				new menu_page_queue_event_log_table(); // Displays table.

				echo '   </form>';
				echo '</div>'."\n";
			}

			/**
			 * Displays menu page.
			 *
			 * @since 141111 First documented version.
			 */
			protected function stats_()
			{
				$_this             = $this;
				$timezone          = $this->plugin->utils_date->i18n('T');
				$current_value_for = function ($key) use ($_this)
				{
					return isset($_REQUEST[__NAMESPACE__]['stats'][$key])
						? trim(stripslashes((string)$_REQUEST[__NAMESPACE__]['stats'][$key])) : NULL;
				};
				echo '<div class="'.esc_attr($this->plugin->slug.'-menu-page-stats '.$this->plugin->slug.'-menu-page-area wrap').'">'."\n";

				echo '   <h2>'.sprintf(__('%1$s&trade; &raquo; Statistics/Charts', $this->plugin->text_domain), esc_html($this->plugin->name)).' <i class="fa fa-bar-chart"></i></h2>'."\n";

				echo '   <div class="pmp-postbox-container postbox-container">'.
				     '      <div class="pmp-postbox-holder metabox-holder">';

				/* ----------------------------------------------------------------------------------------- */

				$date_info        = // For use in JavaScript alerts (as seen below).
					__('You can type (or select) a particular date/time. Upon clicking the input field a date picker will open for you.', $this->plugin->text_domain)."\n\n".
					__('TIP: you can also type things like: now, 30 days ago, -30 days, -2 weeks, and more. Anything compatible with PHP\'s strtotime() function will work here.', $this->plugin->text_domain)."\n\n".
					__('As expected, relative dates like: -30 days; are based on your current local time when used in the From Date; i.e. your current local time -30 days.', $this->plugin->text_domain)."\n\n".
					__('However, relative dates used in the To Date are slightly different. With the exception of the phrase "now", relative To Date phrases are relative to the From Date you\'ve given.', $this->plugin->text_domain)."\n\n".
					__('Typing (or selecting) a specific date in either field will behave as expected; i.e. you get data from (or to) that specific date. Only relative dates (i.e. phrases) are impacted by the above.', $this->plugin->text_domain);
				$date_info_anchor = '<a href="#" onclick="alert(\''.esc_attr($this->plugin->utils_string->esc_js_sq($date_info)).'\'); return false;" style="text-decoration:none;">'.__('[?]', $this->plugin->text_domain).'</a>';

				/* ----------------------------------------------------------------------------------------- */

				$_postbox_view = 'subs_overview'; // This statistical view.

				$_form_field_args = array(
					'ns_id_suffix'   => '-stats-form-'.str_replace('_', '-', $_postbox_view),
					'ns_name_suffix' => '[stats_chart_data_via_ajax]',
					'class_prefix'   => 'pmp-stats-form-',
				);
				$_form_fields     = new form_fields($_form_field_args);

				$_postbox_body = $this->stats_view(
					$_postbox_view,
					array(
						$_form_fields->hidden_input(
							array(
								'name'          => 'view',
								'current_value' => $_postbox_view,
							)),
						$_form_fields->select_row(
							array(
								'label'           => __('Chart Type', $this->plugin->text_domain),
								'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
								'name'            => 'type',
								'current_value'   => $this->coalesce($current_value_for('type'), 'event_subscribed_totals'),
								'allow_arbitrary' => FALSE,
								'options'         => array(
									'subscribed_totals'                   => __('Actual/Current Subscr. Totals', $this->plugin->text_domain),
									'event_subscribed_totals'             => __('Subscr. Totals (Based on Event Logs)', $this->plugin->text_domain),
									'event_subscribed_most_popular_posts' => __('Most Popular Posts (Based on Event Logs)', $this->plugin->text_domain),
								),
							)),
						$_form_fields->input_row(
							array(
								'label'         => sprintf(__('From Date (%1$s) %2$s', $this->plugin->text_domain), esc_html($timezone), $date_info_anchor),
								'placeholder'   => sprintf(__('e.g. 7 days ago; %1$s 00:00', $this->plugin->text_domain), esc_html($this->plugin->utils_date->i18n('M j, Y', strtotime('-7 days')))),
								'name'          => 'from',
								'other_attrs'   => 'data-toggle="date-time-picker"',
								'current_value' => $this->coalesce($current_value_for('from'), '7 days ago'),
							)),
						$_form_fields->input_row(
							array(
								'label'         => sprintf(__('To Date (%1$s) %2$s', $this->plugin->text_domain), esc_html($timezone), $date_info_anchor),
								'placeholder'   => sprintf(__('e.g. now; %1$s 00:00', $this->plugin->text_domain), esc_html($this->plugin->utils_date->i18n('M j, Y'))),
								'name'          => 'to',
								'other_attrs'   => 'data-toggle="date-time-picker"',
								'current_value' => $this->coalesce($current_value_for('to'), 'now'),
							)),
						$_form_fields->select_row(
							array(
								'label'           => __('Breakdown By', $this->plugin->text_domain),
								'placeholder'     => __('e.g. hours, days, weeks, months, years', $this->plugin->text_domain),
								'name'            => 'by',
								'current_value'   => $this->coalesce($current_value_for('by'), 'days'),
								'allow_arbitrary' => FALSE,
								'options'         => array(
									'hours'  => __('hours', $this->plugin->text_domain),
									'days'   => __('days', $this->plugin->text_domain),
									'weeks'  => __('weeks', $this->plugin->text_domain),
									'months' => __('months', $this->plugin->text_domain),
									'years'  => __('years', $this->plugin->text_domain),
								),
							)),
					),
					array('auto_chart' => $current_value_for('view') === $_postbox_view));

				echo $this->postbox(__('Subscriptions Overview', $this->plugin->text_domain), $_postbox_body,
				                    array('icon' => '<i class="fa fa-bar-chart"></i>', 'open' => !$current_value_for('view') || $current_value_for('view') === $_postbox_view));

				unset($_postbox_view, $_postbox_body); // Housekeeping.

				/* ----------------------------------------------------------------------------------------- */

				$_postbox_view = 'subs_by_post_id'; // This statistical view.

				$_form_field_args = array(
					'ns_id_suffix'   => '-stats-form-'.str_replace('_', '-', $_postbox_view),
					'ns_name_suffix' => '[stats_chart_data_via_ajax]',
					'class_prefix'   => 'pmp-stats-form-',
				);
				$_form_fields     = new form_fields($_form_field_args);

				$_postbox_body = $this->stats_view(
					$_postbox_view,
					array(
						$_form_fields->hidden_input(
							array(
								'name'          => 'view',
								'current_value' => $_postbox_view,
							)),
						$_form_fields->select_row(
							array(
								'label'               => __('Post ID', $this->plugin->text_domain),
								'placeholder'         => __('Select an Option...', $this->plugin->text_domain),
								'name'                => 'post_id',
								'current_value'       => $this->coalesce($current_value_for('post_id'), NULL),
								'options'             => '%%posts%%',
								'input_fallback_args' => array(
									'type'                     => 'number',
									'placeholder'              => '',
									'maxlength'                => 20,
									'current_value_empty_on_0' => TRUE,
									'other_attrs'              => 'min="1" max="18446744073709551615"',
								),
							)),
						$_form_fields->select_row(
							array(
								'label'           => __('Chart Type', $this->plugin->text_domain),
								'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
								'name'            => 'type',
								'current_value'   => $this->coalesce($current_value_for('type'), 'event_subscribed_totals'),
								'allow_arbitrary' => FALSE,
								'options'         => array(
									'subscribed_totals'       => __('Actual/Current Subscr. Totals', $this->plugin->text_domain),
									'event_subscribed_totals' => __('Subscr. Totals (Based on Event Logs)', $this->plugin->text_domain),
								),
							)),
						$_form_fields->input_row(
							array(
								'label'         => sprintf(__('From Date (%1$s) %2$s', $this->plugin->text_domain), esc_html($timezone), $date_info_anchor),
								'placeholder'   => sprintf(__('e.g. 7 days ago; %1$s 00:00', $this->plugin->text_domain), esc_html($this->plugin->utils_date->i18n('M j, Y', strtotime('-7 days')))),
								'name'          => 'from',
								'other_attrs'   => 'data-toggle="date-time-picker"',
								'current_value' => $this->coalesce($current_value_for('from'), '7 days ago'),
							)),
						$_form_fields->input_row(
							array(
								'label'         => sprintf(__('To Date (%1$s) %2$s', $this->plugin->text_domain), esc_html($timezone), $date_info_anchor),
								'placeholder'   => sprintf(__('e.g. now; %1$s 00:00', $this->plugin->text_domain), esc_html($this->plugin->utils_date->i18n('M j, Y'))),
								'name'          => 'to',
								'other_attrs'   => 'data-toggle="date-time-picker"',
								'current_value' => $this->coalesce($current_value_for('to'), 'now'),
							)),
						$_form_fields->select_row(
							array(
								'label'           => __('Breakdown By', $this->plugin->text_domain),
								'placeholder'     => __('e.g. hours, days, weeks, months, years', $this->plugin->text_domain),
								'name'            => 'by',
								'current_value'   => $this->coalesce($current_value_for('by'), 'days'),
								'allow_arbitrary' => FALSE,
								'options'         => array(
									'hours'  => __('hours', $this->plugin->text_domain),
									'days'   => __('days', $this->plugin->text_domain),
									'weeks'  => __('weeks', $this->plugin->text_domain),
									'months' => __('months', $this->plugin->text_domain),
									'years'  => __('years', $this->plugin->text_domain),
								),
							)),
					),
					array('auto_chart' => $current_value_for('view') === $_postbox_view));

				echo $this->postbox(__('Subscriptions by Post ID', $this->plugin->text_domain), $_postbox_body,
				                    array('icon' => '<i class="fa fa-bar-chart"></i>', 'open' => !$current_value_for('view') || $current_value_for('view') === $_postbox_view));

				unset($_postbox_view, $_postbox_body); // Housekeeping.

				/* ----------------------------------------------------------------------------------------- */

				echo '      </div>'.
				     '   </div>';

				echo '</div>';
			}

			/**
			 * Constructs menu page heading.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string $title Title of this menu page.
			 * @param string $logo_icon Logo/icon for this menu page.
			 *
			 * @return string The heading for this menu page.
			 */
			protected function heading($title, $logo_icon)
			{
				$title     = (string)$title;
				$logo_icon = (string)$logo_icon;
				$heading   = ''; // Initialize.

				$heading .= '<div class="pmp-heading">'."\n";

				$heading .= '  <img class="pmp-logo-icon" src="'.$this->plugin->utils_url->to('/client-s/images/'.$logo_icon).'" alt="'.esc_attr($title).'" />'."\n";

				$heading .= '  <div class="pmp-heading-links">'."\n";

				$heading .= '  <a href="'.esc_attr($this->plugin->utils_url->main_menu_page_only()).'"'.
				            ($this->plugin->utils_env->is_menu_page(__NAMESPACE__) ? ' class="pmp-active"' : '').'>'.
				            '<i class="fa fa-gears"></i> '.__('Options', $this->plugin->text_domain).'</a>'."\n";

				$heading .= '  <a href="'.esc_attr($this->plugin->utils_url->import_export_menu_page_only()).'"'.
				            ($this->plugin->utils_env->is_menu_page(__NAMESPACE__.'_import_export') ? ' class="pmp-active"' : '').'>'.
				            '<i class="fa fa-upload"></i> '.__('Import/Export', $this->plugin->text_domain).
				            (!$this->plugin->utils_env->is_menu_page(__NAMESPACE__.'_import_export') // Call to action for StCR users.
				             && $this->plugin->install_time() > strtotime('-2 days') && import_stcr::data_exists() && !import_stcr::ever_imported()
					            ? '<span class="pmp-blink">'.__('StCR Auto-Import', $this->plugin->text_domain).'</span>' : '').'</a>'."\n";

				$heading .= '  <a href="'.esc_attr($this->plugin->utils_url->email_templates_menu_page_only()).'"'.
				            ($this->plugin->utils_env->is_menu_page(__NAMESPACE__.'_email_templates') ? ' class="pmp-active"' : '').'>'.
				            '<i class="fa fa-code"></i> '.__('Email Templates', $this->plugin->text_domain).'</a>'."\n";

				$heading .= '  <a href="'.esc_attr($this->plugin->utils_url->site_templates_menu_page_only()).'"'.
				            ($this->plugin->utils_env->is_menu_page(__NAMESPACE__.'_site_templates') ? ' class="pmp-active"' : '').'>'.
				            '<i class="fa fa-code"></i> '.__('Site Templates', $this->plugin->text_domain).'</a>'."\n";

				if(!$this->plugin->is_pro) // Display pro preview/upgrade related links?
				{
					$heading .= '  <a href="'.esc_attr($this->plugin->utils_url->pro_preview()).'"'.
					            ($this->plugin->utils_env->is_pro_preview() ? ' class="pmp-active"' : '').'>'.
					            '<i class="fa fa-eye"></i> '.__('Preview Pro Features', $this->plugin->text_domain).'</a>'."\n";

					$heading .= '  <a href="'.esc_attr($this->plugin->utils_url->product_page()).'" target="_blank"><i class="fa fa-heart-o"></i> '.__('Pro Upgrade', $this->plugin->text_domain).'</a>'."\n";
				}
				$heading .= '     <a href="'.esc_attr($this->plugin->utils_url->subscribe_page()).'" target="_blank"><i class="fa fa-envelope"></i> '.__('Newsletter (Subscribe)', $this->plugin->text_domain).'</a>'."\n";
				$heading .= '     <a href="#" data-pmp-action="'.esc_attr($this->plugin->utils_url->restore_default_options()).'" data-pmp-confirmation="'.esc_attr(__('Restore default plugin options? You will lose all of your current settings! Are you absolutely sure?', $this->plugin->text_domain)).'"><i class="fa fa-ambulance"></i> '.__('Restore Default Options', $this->plugin->text_domain).'</a>'."\n";

				$heading .= '  </div>'."\n";

				$heading .= '</div>'."\n";

				return $heading; // Menu page heading.
			}

			/**
			 * All-panel togglers.
			 *
			 * @since 141111 First documented version.
			 *
			 * @return string Markup for all-panel togglers.
			 */
			protected function all_panel_togglers()
			{
				$togglers = '<div class="pmp-all-panel-togglers">'."\n";
				$togglers .= ' <a href="#" class="pmp-panels-open" title="'.esc_attr(__('Open All Panels', $this->plugin->text_domain)).'"><i class="fa fa-chevron-circle-down"></i></a>'."\n";
				$togglers .= ' <a href="#" class="pmp-panels-close" title="'.esc_attr(__('Close All Panels', $this->plugin->text_domain)).'"><i class="fa fa-chevron-circle-up"></i></a>'."\n";
				$togglers .= '</div>'."\n";

				return $togglers; // Toggles all panels open/closed.
			}

			/**
			 * Constructs menu page notes.
			 *
			 * @since 141111 First documented version.
			 *
			 * @return string The notes for this menu page.
			 */
			protected function notes()
			{
				$notes = ''; // Initialize notes.

				if($this->plugin->utils_env->is_pro_preview())
				{
					$notes .= '<div class="pmp-note pmp-info">'."\n";
					$notes .= '  <a href="'.esc_attr($this->plugin->utils_url->page_only()).'" style="float:right; margin:0 0 15px 25px; font-variant:small-caps; text-decoration:none;">'.__('close', $this->plugin->text_domain).' <i class="fa fa-eye-slash"></i></a>'."\n";
					$notes .= '  <i class="fa fa-eye"></i> '.sprintf(__('<strong>Pro Features (Preview)</strong> ~ New option panels below. Please explore before <a href="%1$s" target="_blank">upgrading <i class="fa fa-heart-o"></i></a>.', $this->plugin->text_domain), esc_attr($this->plugin->utils_url->product_page())).'<br />'."\n";
					$notes .= '  '.sprintf(__('<small>NOTE: the free version of %1$s (i.e. this lite version); is more-than-adequate for most sites. Please upgrade only if you desire advanced features or would like to support the developer.</small>', $this->plugin->text_domain), esc_html($this->plugin->name))."\n";
					$notes .= '</div>'."\n";
				}
				if($this->plugin->install_time() > strtotime('-48 hours') && $this->plugin->utils_env->is_menu_page(__NAMESPACE__.'_*_templates'))
				{
					$notes .= '<div class="pmp-note pmp-notice">'."\n";
					$notes .= '  '.__('All templates come preconfigured; customization is optional <i class="fa fa-smile-o"></i>', $this->plugin->text_domain)."\n";
					$notes .= '</div>'."\n";
				}
				return $notes; // All notices; if any apply.
			}

			/**
			 * Constructs a menu page stats view.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string $view Statistical view specification.
			 * @param array  $form_field_rows An array of form fields rows needed by this view.
			 * @param array  $args Any additional specs/behavorial args.
			 *
			 * @return string Markup for this menu page stats view.
			 */
			protected function stats_view($view, array $form_field_rows = array(), array $args = array())
			{
				$view = trim(strtolower((string)$view));

				$default_args = array(
					'auto_chart' => FALSE,
				);
				$args         = array_merge($default_args, $args);
				$args         = array_intersect_key($args, $default_args);

				$auto_chart = (boolean)$args['auto_chart'];

				$view = '<div class="'.esc_attr('pmp-stats-view pmp-stats-view-'.str_replace('_', '-', $view)).'">'."\n";
				$view .= '  <form novalidate="novalidate" onsubmit="return false;">'."\n";

				foreach($form_field_rows as $_form_field_row)
					if(stripos($_form_field_row, '<input type="hidden"') !== FALSE)
						$view .= '<div style="display:none;">'.$_form_field_row.'</div>'."\n";
				unset($_form_field_row); // Housekeeping.

				$view .= '     <table class="pmp-stats-view-table">'."\n";
				$view .= '        <tbody>'."\n";
				$view .= '           <tr>'."\n";

				foreach($form_field_rows as $_form_field_row)
					if(stripos($_form_field_row, '<input type="hidden"') === FALSE)
					{
						$view .= '        <td class="pmp-stats-view-col">'."\n";
						$view .= '           <table><tbody>'.$_form_field_row.'</tbody></table>'."\n";
						$view .= '        </td>'."\n";
					}
				unset($_form_field_row); // Housekeeping.

				$view .= '              <td class="pmp-stats-view-col pmp-stats-view-submit">'."\n";
				$view .= '                 <button type="button" class="button button-primary"'.($auto_chart ? ' data-auto-chart' : '').'>'."\n";
				$view .= '                    '.__('Display Chart', $this->plugin->text_domain)."\n";
				$view .= '                 </button>'."\n";
				$view .= '              </td>'."\n";

				$view .= '           </tr>'."\n";
				$view .= '        </tbody>'."\n";
				$view .= '     </table>'."\n";

				$view .= '  </form>'."\n";
				$view .= '</div>'."\n";

				return $view; // Markup for this stats view.
			}

			/**
			 * Constructs a menu page panel.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string $title Panel title.
			 * @param string $body Panel body; i.e. HTML markup.
			 * @param array  $args Any additional specs/behavorial args.
			 *
			 * @return string Markup for this menu page panel.
			 */
			protected function panel($title, $body, array $args = array())
			{
				$title = (string)$title;
				$body  = (string)$body;

				$default_args = array(
					'note'     => '',
					'icon'     =>
						'<i class="fa fa-gears"></i>',
					'pro_only' => FALSE,
					'open'     => FALSE,
				);
				$args         = array_merge($default_args, $args);
				$args         = array_intersect_key($args, $default_args);

				$note     = trim((string)$args['note']);
				$icon     = trim((string)$args['icon']);
				$pro_only = (boolean)$args['pro_only'];
				$open     = (boolean)$args['open'];

				if($pro_only && !$this->plugin->is_pro && !$this->plugin->utils_env->is_pro_preview())
					return ''; // Not applicable; not pro, or not a pro preview.

				$panel = '<div class="pmp-panel'.esc_attr($pro_only && !$this->plugin->is_pro ? ' pmp-pro-preview' : '').'">'."\n";
				$panel .= '   <a href="#" class="pmp-panel-heading'.($open ? ' open' : '').'">'."\n";
				$panel .= '      '.$icon.' '.$title."\n";
				$panel .= $note ? '<span class="pmp-panel-heading-note">'.$note.'</span>' : '';
				$panel .= '   </a>'."\n";

				$panel .= '   <div class="pmp-panel-body'.($open ? ' open' : '').' pmp-clearfix">'."\n";

				$panel .= '      '.$body."\n";

				$panel .= '   </div>'."\n";
				$panel .= '</div>'."\n";

				return $panel; // Markup for this panel.
			}

			/**
			 * Constructs a menu page postbox.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string $title Postbox title.
			 * @param string $body Postbox body; i.e. HTML markup.
			 * @param array  $args Any additional specs/behavorial args.
			 *
			 * @return string Markup for this menu page postbox.
			 */
			protected function postbox($title, $body, array $args = array())
			{
				$title = (string)$title;
				$body  = (string)$body;

				$default_args = array(
					'note'     => '',
					'icon'     =>
						'<i class="fa fa-gears"></i>',
					'pro_only' => FALSE,
					'open'     => FALSE,
				);
				$args         = array_merge($default_args, $args);
				$args         = array_intersect_key($args, $default_args);

				$note     = trim((string)$args['note']);
				$icon     = trim((string)$args['icon']);
				$pro_only = (boolean)$args['pro_only'];
				$open     = (boolean)$args['open'];

				$id = 'pb-'.md5($title.$icon.$note); // Auto-generate.

				if($pro_only && !$this->plugin->is_pro && !$this->plugin->utils_env->is_pro_preview())
					return ''; // Not applicable; not pro, or not a pro preview.

				$postbox = '<div id="'.esc_attr($id).'"'. // Expected by `postbox.js` in WP core.
				           ' class="pmp-postbox postbox'.esc_attr((!$open ? ' closed' : ''). // Add `closed` class.
				                                                  ($pro_only && !$this->plugin->is_pro ? ' pmp-pro-preview' : '')).'">'."\n";
				$postbox .= '  <div class="pmp-postbox-handle handlediv"><br /></div>'."\n";

				$postbox .= '  <h3 class="pmp-postbox-hndle hndle">'."\n";
				$postbox .= '      '.$icon.' '.$title."\n";
				$postbox .= $note ? '<span class="pmp-postbox-hndle-note">'.$note.'</span>' : '';
				$postbox .= '  </h3>'."\n";

				$postbox .= '  <div class="pmp-postbox-inside inside">'."\n";
				$postbox .= '     '.$body."\n";
				$postbox .= '  </div>'."\n";

				$postbox .= '</div>'."\n";

				return $postbox; // Markup for this postbox.
			}
		}
	}
}