<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_Wordpress_Block_Post_View_Comments extends Fishpig_Wordpress_Block_Post_Abstract
{
	/**
	 * Returns a collection of comments for the current post
	 *
	 * @return Fishpig_Wordpress_Model_Resource_Post_Comments_Collection
	 */
	public function getComments()
	{
		if (!$this->hasComments()) {
			if ($this->getCommentCount() > 0 && ($post = $this->getPost()) !== false) {
				$this->setComments($post->getResource()->getPostComments($post));

				if (($pager = $this->getChild('pager')) !== false) {
					$this->_getData('comments')->setPageSize($pager->getLimit());
				}
			}
			else {
				$this->setComments(new Varien_Data_Collection());
			}
		}
		
		return $this->_getData('comments');
	}
	
	/**
	 * Retrieve the amount of comments for the current post
	 *
	 * @return int
	 */
	public function getCommentCount()
	{
		return $this->getPost()->getCommentCount();
	}

	/**
	 * Setup the pager and comments form blocks
	 *
	 */
	protected function _beforeToHtml()
	{
		if (!$this->getTemplate()) {
			$this->setTemplate('wordpress/post/view/comments.phtml');
		}

		if ($this->getCommentCount() > 0 && ($pagerBlock = $this->getChild('pager')) !== false) {
			$pagerBlock->setCollection($this->getComments());
		}

		if (($form = $this->getChild('form')) !== false) {
			$form->setPost($this->getPost());
		}

		parent::_beforeToHtml();
	}
	
	/**
	 * Returns the HTML for the comment form
	 *
	 * @return string
	 */
	public function getCommentFormHtml()
	{
		if ($this->isCommentsEnabled()) {
			return $this->getChildHtml('form');
		}
		
		return '';
	}
	
	/**
	 * Get the HTML for the pager block
	 *
	 * @return null|string
	 */
	public function getPagerHtml()
	{
		if ($this->helper('wordpress')->getWpOption('page_comments', false)) {
			return $this->getChildHtml('pager');
		}
	}
	
	/**
	 * Determine whether comments are enabled
	 *
	 * @return bool
	 */
	public function isCommentsEnabled()
	{
		return $this->getPost()->getCommentStatus() !== 'closed';
	}
	
	/**
	 * Get the HTML of the child comments
	 *
	 * @param Fishpig_Wordpress_Model_Post_Comment $comment
	 * @return string
	 */
	public function getChildrenCommentsHtml(Fishpig_Wordpress_Model_Post_Comment $comment)
	{
		return $this->getLayout()
			->createBlock($this->getType())
			->setTemplate($this->getTemplate())
			->setParentId($comment->getId())
			->setComments($comment->getChildrenComments())
			->toHtml();
	}
}
