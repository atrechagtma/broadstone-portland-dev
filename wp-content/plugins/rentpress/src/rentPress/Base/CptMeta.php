<?php 

/**
* Custom post type meta info handler base class
*/
abstract class rentPress_Base_CptMeta
{
	protected $postID;

	public function __construct()
	{
		$this->postID = null;
        $this->options = new rentPress_Options();
	}

	public function fetchMeta($postID, $key)
	{
		$postID = $this->currentPostID($postID);
        $meta = get_post_meta($postID, $key, true);
		return esc_html__($meta, RENTPRESS_LANG_KEY);
	}

	public function fetchJsonMeta($postID, $key, $arrayFormat = false)
	{
		$postID = $this->currentPostID($postID);
		return json_decode(
			get_post_meta($postID, $key, true), $arrayFormat
		);
	}

	public function fetchTaxonomyTerms($postID, $taxonomySlug)
	{
		$postID = $this->currentPostID($postID);
		return get_the_terms($postID, $taxonomySlug);
	}

    public function fetchFeaturedImageUrl($postID)
    {
        $postID = $this->currentPostID($postID);
        return wp_get_attachment_url( get_post_thumbnail_id($postID) );
    }

    /**
     * Gets the value of postID.
     *
     * @return mixed
     */
    public function getPostID()
    {
        return $this->postID;
    }

    /**
     * Sets the value of postID.
     *
     * @param mixed $postID the post
     *
     * @return self
     */
    public function setPostID($postID)
    {
        $this->postID = $postID;

        return $this;
    }

    public function currentPostID($postID)
    {
		return null !== $this->getPostID() ? $this->getPostID() : $postID;
    }

}