<?php 

/**
* Base Repository Class
*/
interface rentPress_Base_Repository
{
	/**
	 * Persist item to WP 
	 * @param  JSON_Object $item [Property or Floor Plan item]
	 * @return string        	 [Success or failure response]
	 */
	public function persist($item);
}