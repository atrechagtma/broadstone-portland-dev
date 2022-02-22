<?php

class rentPress_Neighborhoods_WpAdminAddon{
	
	public function run() {
		add_filter('manage_neighborhoods_posts_columns', [$this, 'manage_posts_columns'], 9, 1);
		add_action('manage_neighborhoods_posts_custom_column', [$this, 'columns_content'], 10, 2);
	}

	public function manage_posts_columns($columns) {
		return array_merge($columns, [
			'numOfProperties' => 'Number Of Properties',
        ]);
	}

	public function columns_content($column, $postId) {

		switch ($column) {
			case 'numOfProperties':
				$propIds=get_posts([
					'post_type' => 'properties',
					'properties_from_neighborhood' => $postId,
					'nopaging' => true,
					'fields' => 'ids',
				]);

				$link=get_admin_url(null, "edit.php?post_type=properties&properties_from_neighborhood=".$postId);

				echo "<a href='". $link ."' target='_blank'>". count($propIds) ."</a>";

				break;

		}

	}

}