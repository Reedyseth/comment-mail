<?php
namespace comment_mail;

/**
 * @var plugin $plugin Plugin class.
 *
 * Other variables made available in this template file:
 *
 * @note This file is automatically included as a child of other templates.
 *    Therefore, this template will ALSO receive any variable(s) passed to the parent template file,
 *    where the parent automatically calls upon this template. In short, if you see a variable documented in
 *    another template file, that particular variable will ALSO be made available in this file too;
 *    as this file is automatically included as a child of other parent templates.
 *
 * -------------------------------------------------------------------
 * @note In addition to plugin-specific variables & functionality,
 *    you may also use any WordPress functions that you like.
 */
?>
<?php
/*
 * Here we define a few variables of our own.
 */
// Site home page URL; i.e. back to the main site.
$home_url = home_url('/'); // Multisite compatible.

// A clip of the blog's name; as configured in WordPress.
$blog_name_clip = $plugin->utils_string->clip(get_bloginfo('name'));

// Summary return URL; w/ all summary navigation vars preserved.
$sub_summary_return_url = $plugin->utils_url->sub_manage_summary_url(!empty($sub_key) ? $sub_key : '', NULL, TRUE);

// Current `host[/path]` with support for multisite network and child blogs.
$current_host_path = $plugin->utils_url->current_host_path();

// Privacy policy URL; as configured in plugin options via the dashboard.
$privacy_policy_url = $plugin->options['can_spam_privacy_policy_url'];
?>

<footer style="margin-bottom:20px;">
	<div class="row">

		<div class="col-md-6 text-left">

			<a href="<?php echo esc_attr($sub_summary_return_url); ?>">
				<i class="<?php echo esc_attr('wsi-'.$plugin->slug); ?>"></i>
				<?php echo __('My Subscriptions', $plugin->text_domain); ?>
			</a>

			<span class="text-muted">|</span>

			<a href="<?php echo esc_attr($home_url); ?>">
				<i class="fa fa-home"></i> <?php echo sprintf(__('Return to "%1$s"', $plugin->text_domain), esc_html($blog_name_clip)); ?>
			</a>

			<?php if($privacy_policy_url): ?>
				<span class="text-muted">|</span>
				<a href="<?php echo esc_attr($privacy_policy_url); ?>">
					<?php echo __('Privacy Policy', $plugin->text_domain); ?>
				</a>
			<?php endif; ?>

		</div>

		<div class="col-md-6 text-right">
			<?php echo $plugin->utils_markup->powered_by(); ?>
		</div>

	</div>
</footer>