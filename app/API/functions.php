<?php
/**
* Primary plugin API functions
*/
use Favorites\Entities\Favorite\Favorite; // Added by Jack
use Favorites\Entities\Favorite\FavoriteButton;
use Favorites\Entities\Post\FavoriteCount;
use Favorites\Entities\User\UserFavorites;
use Favorites\Entities\Post\PostFavorites;
use Favorites\Entities\Favorite\ClearFavoritesButton;

/**
* Get the favorite button
* @param $post_id int, defaults to current post
* @param $site_id int, defaults to current blog/site
* @return html
*/
function get_favorites_button($post_id = null, $site_id = null, $group_id = null)
{
	global $blog_id;
	if ( !$post_id ) $post_id = get_the_id();
	if ( !$group_id ) $group_id = 1;
	$site_id = ( is_multisite() && is_null($site_id) ) ? $blog_id : $site_id;
	if ( !is_multisite() ) $site_id = 1;
	$button = new FavoriteButton($post_id, $site_id);
	return $button->display();
}


/**
* Echos the favorite button
* @param $post_id int, defaults to current post
* @param $site_id int, defaults to current blog/site
* @return html
*/
function the_favorites_button($post_id = null, $site_id = null, $group_id = null)
{	
	echo get_favorites_button($post_id, $site_id, $group_id);
}


/**
* Get the Favorite Total Count for a Post
* @param $post_id int, defaults to current post
* @param $site_id int, defaults to current blog/site
* @param $html bool, whether to return html (returns simple integer if false)
* @return html
*/
function get_favorites_count($post_id = null, $site_id = null, $html = true)
{
	global $blog_id;
	$site_id = ( is_multisite() && is_null($site_id) ) ? $blog_id : $site_id;
	if ( !$post_id ) $post_id = get_the_id();
	$count = new FavoriteCount();
	$count = $count->getCount($post_id, $site_id);
	$out = "";
	if ( $html ) $out .= '<span data-favorites-post-count-id="' . $post_id . '" data-siteid="' . $site_id . '">';
	$out .= $count;
	if ( $html ) $out .= '</span>';
	return $out;
}


/**
* Echo the Favorite Count
* @param $post_id int, defaults to current post
* @param $site_id int, defaults to current blog/site
* @return html
*/
function the_favorites_count($post_id = null, $site_id = null, $html = true)
{
	echo get_favorites_count($post_id, $site_id, $html);
}


/**
* Get an array of User Favorites
* @param $user_id int, defaults to current user
* @param $site_id int, defaults to current blog/site
* @param $filters array of post types/taxonomies
* @return array
*/
function get_user_favorites($user_id = null, $site_id = null, $filters = null)
{
	global $blog_id;
	$site_id = ( is_multisite() && is_null($site_id) ) ? $blog_id : $site_id;
	if ( !is_multisite() ) $site_id = 1;
	$favorites = new UserFavorites($user_id, $site_id, $links = false, $filters);
	return $favorites->getFavoritesArray();
}


/**
* HTML List of User Favorites
* @param $user_id int, defaults to current user
* @param $site_id int, defaults to current blog/site
* @param $filters array of post types/taxonomies
* @param $include_button boolean, whether to include the favorite button for each item
* @param $include_thumbnails boolean, whether to include the thumbnail for each item
* @param $thumbnail_size string, the thumbnail size to display
* @param $include_excpert boolean, whether to include the excerpt for each item
* @return html
*/
function get_user_favorites_list($user_id = null, $site_id = null, $include_links = false, $filters = null, $include_button = false, $include_thumbnails = false, $thumbnail_size = 'thumbnail', $include_excerpt = false)
{
	global $blog_id;
	$site_id = ( is_multisite() && is_null($site_id) ) ? $blog_id : $site_id;
	if ( !is_multisite() ) $site_id = 1;
	$favorites = new UserFavorites($user_id, $site_id, $include_links, $filters);
	return $favorites->getFavoritesList($include_button, $include_thumbnails, $thumbnail_size, $include_excerpt);
}


/**
* Echo HTML List of User Favorites
* @param $user_id int, defaults to current user
* @param $site_id int, defaults to current blog/site
* @param $filters array of post types/taxonomies
* @param $include_button boolean, whether to include the favorite button for each item
* @param $include_thumbnails boolean, whether to include the thumbnail for each item
* @param $thumbnail_size string, the thumbnail size to display
* @param $include_excpert boolean, whether to include the excerpt for each item
* @return html
*/
function the_user_favorites_list($user_id = null, $site_id = null, $include_links = false, $filters = null, $include_button = false, $include_thumbnails = false, $thumbnail_size = 'thumbnail', $include_excerpt = false)
{
	echo get_user_favorites_list($user_id, $site_id, $include_links, $filters, $include_button, $include_thumbnails, $thumbnail_size, $include_excerpt);
}


/**
* Get the number of posts a specific user has favorited
* @param $user_id int, defaults to current user
* @param $site_id int, defaults to current blog/site
* @param $filters array of post types/taxonomies
* @param $html boolean, whether to output html (important for AJAX updates). If false, an integer is returned
* @return int
*/
function get_user_favorites_count($user_id = null, $site_id = null, $filters = null, $html = false)
{
	$favorites = get_user_favorites($user_id, $site_id, $filters);
	$posttypes = ( isset($filters['post_type']) ) ? implode(',', $filters['post_type']) : 'all';
	$count = ( isset($favorites[0]['site_id']) ) ? count($favorites[0]['posts']) : count($favorites);
	$out = "";
	if ( !$site_id ) $site_id = 1;
	if ( $html ) $out .= '<span class="simplefavorites-user-count" data-posttypes="' . $posttypes . '" data-siteid="' . $site_id . '">';
	$out .= $count;
	if ( $html ) $out .= '</span>';
	return $out;
}


/**
* Print the number of posts a specific user has favorited
* @param $user_id int, defaults to current user
* @param $site_id int, defaults to current blog/site
* @param $filters array of post types/taxonomies
* @return html
*/
function the_user_favorites_count($user_id = null, $site_id = null, $filters = null)
{
	echo get_user_favorites_count($user_id, $site_id, $filters);
}


/**
* Get an array of users who have favorited a post
* @param $post_id int, defaults to current post
* @param $site_id int, defaults to current blog/site
* @param $user_role string, defaults to all
* @return array of user objects
*/
function get_users_who_favorited_post($post_id = null, $site_id = null, $user_role = null)
{
	$users = new PostFavorites($post_id, $site_id, $user_role);
	return $users->getUsers();
}


/**
* Get a list of users who favorited a post
* @param $post_id int, defaults to current post
* @param $site_id int, defaults to current blog/site
* @param $separator string, custom separator between items (defaults to HTML list)
* @param $include_anonmyous boolean, whether to include anonmyous users
* @param $anonymous_label string, label for anonymous user count
* @param $anonymous_label_single string, singular label for anonymous user count
* @param $user_role string, defaults to all
*/
function the_users_who_favorited_post($post_id = null, $site_id = null, $separator = 'list', $include_anonymous = true, $anonymous_label = 'Anonymous Users', $anonymous_label_single = 'Anonymous User', $user_role = null)
{
	$users = new PostFavorites($post_id, $site_id, $user_role);
	echo $users->userList($separator, $include_anonymous, $anonymous_label, $anonymous_label_single);
}

/**
 * Get the number of anonymous users who favorited a post
 * @param  $post_id int Defaults to current post
 * @return int Just anonymous users
 */
function get_anonymous_users_who_favourited_post( $post_id = null ) {
	$user = new PostFavorites( $post_id );
	return $users->anonymousCount();
}

/**
 * Echo the number of anonymous users who favorited a post
 * @param  $post_id int Defaults to current post
 * @return string Just anonymous users
 */
function the_anonymous_users_who_favourited_post( $post_id = null ) {
	echo get_anonymous_users_who_favourited_post( $post_id );
}

/**
* Get the clear favorites button
* @param $site_id int, defaults to current blog/site
* @param $text string, button text - defaults to site setting
* @return html
*/
function get_clear_favorites_button($site_id = null, $text = null)
{
	$button = new ClearFavoritesButton($site_id, $text);
	return $button->display();
}


/**
* Print the clear favorites button
* @param $site_id int, defaults to current blog/site
* @param $text string, button text - defaults to site setting
* @return html
*/
function the_clear_favorites_button($site_id = null, $text = null)
{
	echo get_clear_favorites_button($site_id, $text);
}

/**
* Get the total number of favorites, for all posts and users
* @param $site_id int, defaults to current blog/site
* @return html
*/
function get_total_favorites_count($site_id = null)
{
	$count = new FavoriteCount();
	return $count->getAllCount($site_id);
}

/**
* Print the total number of favorites, for all posts and users
* @param $site_id int, defaults to current blog/site
* @return html
*/
function the_total_favorites_count($site_id = null)
{
	echo get_total_favorites_count($site_id);
}



/**
* Favourite REST API
* @author Jack
* @see http://v2.wp-api.org/extending/adding/
*/
/**
* Get an array of User Favorites
* @param $user_id int, defaults to current user
* @param $site_id int, defaults to current blog/site
* @param $filters array of post types/taxonomies
* @return array
*/
function rest_sk8tech_get_user_favorites( $data = null ) {
  $user_id = null;
  if (!is_user_logged_in()) {
    return "User is not logged in";
  } else {
    $user_id = get_current_user_id();
  }
  $idList = get_user_favorites($user_id, $site_id, $filters );
  if (empty($idList)) {
    return $idList;
  } 
  // $filter = array('post_type' => 'cf47rs_property',
  //                 'post__in' => $idList);
  $favouriteArray = get_posts($filter);
  return $idList;
}
add_action( 'rest_api_init', function () {
  register_rest_route( 'favorites/v1', '/my', array(
    'methods' => 'GET',
    'callback' => 'rest_sk8tech_get_user_favorites',
    'args' => array(
    ),
  ) );
} );
/**
* Get the total favorite count for a post
* Post ID not required if inside the loop
* @param int $post_id
*/
function rest_sk8tech_get_favorites_count( $data = null ) {
  $post_id = $data['id'];
  $favouriteNumber = get_favorites_count($post_id);
  return $favouriteNumber;
}
add_action( 'rest_api_init', function () {
  register_rest_route( 'favorites/v1', '/(?P<id>\d+)', array(
    'methods' => 'GET',
    'callback' => 'rest_sk8tech_get_favorites_count',
    'args' => array(
      'id' => array(
        'validate_callback' => function($param, $request, $key) {
          return is_numeric( $param );
        }
      ),
    ),
  ) );
} );

/**
* Get the favorite button
* @param $post_id int, defaults to current post
* @param $site_id int, defaults to current blog/site
* @return html
*/
function user_favorites_posts($post_id = null, $status = null, $site_id = null)
{
	
	$favorite = new Favorite;
	$favorite->update($post_id, $status, $site_id, $site_id);
	return get_favorites_count($post_id);
}
/**
* Get the total favorite count for a post
* Post ID not required if inside the loop
* @param int $post_id
*/
function rest_sk8tech_user_favorites_post( $request = null ) {
  $parameters = $request->get_json_params();
  $post_id = $parameters['id'];
  $status = $parameters['status'];
  $site_id = $parameters['site_id'];
  $new_favourite_count = user_favorites_posts($post_id, $status, $site_id);
  return $new_favourite_count;
}
add_action( 'rest_api_init', function () {
  register_rest_route( 'favorites/v1', '/(?P<id>\d+)', array(
    'methods' => 'POST',
    'callback' => 'rest_sk8tech_user_favorites_post',
    'args' => array(
      'id' => array(
        'validate_callback' => function($param, $request, $key) {
          return is_numeric( $param );
        }
      ),
    ),
  ) );
} );