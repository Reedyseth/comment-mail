<?php
/**
 * Comment Status
 *
 * @since 14xxxx First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\comment_status'))
	{
		/**
		 * Comment Status
		 *
		 * @since 14xxxx First documented version.
		 */
		class comment_status extends abstract_base
		{
			/**
			 * @var \stdClass|null Comment object (now).
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $comment;

			/**
			 * @var string New comment status applied now.
			 *    One of: `approve`, `hold`, `trash`, `spam`, `delete`.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $new_comment_status;

			/**
			 * @var string Old comment status from before.
			 *    One of: `approve`, `hold`, `trash`, `spam`, `delete`.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected $old_comment_status;

			/**
			 * Class constructor.
			 *
			 * @since 14xxxx First documented version.
			 *
			 * @param integer|string $new_comment_status New comment status.
			 *
			 *    One of the following:
			 *       - `0` (aka: ``, `hold`, `unapprove`, `unapproved`, `moderated`),
			 *       - `1` (aka: `approve`, `approved`),
			 *       - or `trash`, `post-trashed`, `spam`, `delete`.
			 *
			 * @param integer|string $old_comment_status Old comment status.
			 *
			 *    One of the following:
			 *       - `0` (aka: ``, `hold`, `unapprove`, `unapproved`, `moderated`),
			 *       - `1` (aka: `approve`, `approved`),
			 *       - or `trash`, `post-trashed`, `spam`, `delete`.
			 *
			 * @param \stdClass|null $comment Comment object (now).
			 */
			public function __construct($new_comment_status, $old_comment_status, $comment)
			{
				parent::__construct();

				$this->comment            = is_object($comment) ? $comment : NULL;
				$this->new_comment_status = $this->plugin->utils_db->comment_status__($new_comment_status);
				$this->old_comment_status = $this->plugin->utils_db->comment_status__($old_comment_status);

				$this->maybe_insert_queue();
				$this->maybe_delete_subs();
			}

			/**
			 * Insert/queue emails.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function maybe_insert_queue()
			{
				if(!isset($this->comment))
					return; // Not applicable.

				if($this->new_comment_status === 'approve' && $this->old_comment_status === 'hold')
					new queue_inserter($this->comment->comment_ID);
			}

			/**
			 * Delete subscriptions.
			 *
			 * @since 14xxxx First documented version.
			 */
			protected function maybe_delete_subs()
			{
				if(!isset($this->comment))
					return; // Not applicable.

				if($this->new_comment_status === 'delete' && $this->old_comment_status !== 'delete')
					new sub_deleter($this->comment->post_ID, $this->comment->comment_ID);
			}
		}
	}
}