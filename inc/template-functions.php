<?php
/**
 * Functions which enhance the theme by hooking into WordPress
 *
 * @package Whatever
 */


// Return all books ID with selected language
function get_books_ID_with_selected_lang($selected_language_id){
	$posts_with_language = [];
	$all_books = get_posts([
		'post_type' => 'books',
		'posts_per_page' => -1,
		'fields' => 'ids'
	]);

	foreach($all_books as $book_id) {
		if(have_rows('translations', $book_id)) {
			while(have_rows('translations', $book_id)) {
				the_row();
				if($language = get_sub_field('language')) {
					foreach($language as $lang) {
						if($lang->term_id == $selected_language_id) {
							$posts_with_language[] = $book_id;
							break 2; // Прерываем оба цикла, если нашли язык
						}
					}
				}
			}
		}
	}

	return $posts_with_language;
}


// return menu on archive page with ctps have posts in selected language
function post_type_menu_filtered_by_language($post_type, $lang_id){
	$post_type_menu =[];
	$post_type_menu_html = '';

	if ( !empty($lang_id) && is_int($lang_id) ){
		$all_cpt_array = ['reviews', 'articles', 'papers', 'interviews', 'videos', 'audios', 'press', 'memorials', 'projects', 'unpublished', 'books'];
		$key = array_search($post_type, $all_cpt_array);
		unset($all_cpt_array[$key]);

		//for every cpt check posts with selected language
		foreach ($all_cpt_array as $cpt){
			if ( 'books' == $cpt ){
				$cpt_post = get_books_ID_with_selected_lang($lang_id);
			} else {
				$args = array(
					'post_type' => $cpt,
					'posts_per_page' => 1,
					'tax_query' => array(
						array(
							'taxonomy' => 'language',
							'field'    => 'id',
							'terms'    => $lang_id
						)
					)
				);
				$cpt_post = get_posts($args);
			}

			//add menu item with current cpt if get post with selected language
			if ( !empty($cpt_post) ){
				$site_url = home_url();
				$menu_item = '<snap><a style="color: grey" href="' . $site_url . '/' . $cpt .  '/?book_language=' . $lang_id . '">' . $cpt . '</a></snap>';
				array_push($post_type_menu, $menu_item);
			}
		}

		if ( !empty( $post_type_menu ) ) {
			$post_type_menu_html = '<div class="page-notice"><p>You may also read other materials in this language. See ' . implode(", ", $post_type_menu ) . ' in chosen language.</p></div>';
		}

	}

	return $post_type_menu_html;
}


//filter custom post types by languages on archive page
function filter_cpt_by_language($query) {
    if(!is_admin() && $query->is_main_query() && isset($query->query_vars['post_type'])) {
		$cpt_array = ['reviews', 'articles', 'papers', 'interviews', 'videos', 'audios', 'press', 'memorials', 'projects', 'unpublished'];
		$query_post_type = $query->get('post_type');

        if(isset($_GET['book_language']) && !empty($_GET['book_language'])) {
			$selected_language_id = intval($_GET['book_language']);

			if ( is_post_type_archive( 'books' ) ){
				$posts_with_language = get_books_ID_with_selected_lang($selected_language_id);

				if(!empty($posts_with_language)) {
					$query->set('post__in', $posts_with_language);
				} else {
					// Если не найдено книг с выбранным языком, возвращаем пустой результат
					$query->set('post__in', [0]);
				}
			} elseif( is_archive() && in_array($query_post_type, $cpt_array, true)) {
				$query->set('tax_query', array(
					array(
						'taxonomy' => 'language',
						'field'    => 'id',
						'terms'    => $selected_language_id,
					),
				));
			}
        } else { //book_language unset or empty
			if ( in_array($query_post_type, $cpt_array, true) ) {
				$query->set('tax_query', array(
					array(
						'taxonomy' => 'language',
						'operator' => 'NOT EXISTS',
					),
				));
			}
		}
    }
    return $query;
}
add_action('pre_get_posts', 'filter_cpt_by_language');

// Очищаем кэш языков при обновлении постов типа books
function clear_books_languages_cache($post_id) {
    if(get_post_type($post_id) === 'books') {
        delete_transient('books_languages');
    }
}
add_action('save_post', 'clear_books_languages_cache');
add_action('deleted_post', 'clear_books_languages_cache');

/**
 * Adds custom classes to the array of body classes.
 *
 * @param array $classes Classes for the body element.
 *
 * @return array
 */
function wtvr_body_classes( $classes ) {

	// Adds a class of no-sidebar when there is no sidebar present.
	if ( ! is_active_sidebar( 'sidebar' ) ) {
		$classes[] = 'no-sidebar';
	}

	return $classes;
}

add_filter( 'body_class', 'wtvr_body_classes' );

/**
 * Add a pingback url auto-discovery header for single posts, pages, or attachments.
 */
function wtvr_pingback_header() {
	if ( is_singular() && pings_open() ) {
		printf( '<link rel="pingback" href="%s">', esc_url( get_bloginfo( 'pingback_url' ) ) );
	}
}

add_action( 'wp_head', 'wtvr_pingback_header' );

/**
 * Checks if a string is a reserved Wordpress name
 */
function wtvr_is_reserved( $str ) {
	// todo might wanna set this as a global for use in tooltip
	$reserved = [
	  'attachment',
	  'attachment_id',
	  'author',
	  'author_name',
	  'calendar',
	  'cat',
	  'category',
	  'category__and',
	  'category__in',
	  'category__not_in',
	  'category_name',
	  'comments_per_page',
	  'comments_popup',
	  'custom',
	  'customize_messenger_channel',
	  'customized',
	  'cpage',
	  'day',
	  'debug',
	  'embed',
	  'error',
	  'exact',
	  'feed',
	  'fields',
	  'hour',
	  'link_category',
	  'm',
	  'minute',
	  'monthnum',
	  'more',
	  'name',
	  'nav_menu',
	  'nonce',
	  'nopaging',
	  'offset',
	  'order',
	  'orderby',
	  'p',
	  'page',
	  'page_id',
	  'paged',
	  'pagename',
	  'pb',
	  'perm',
	  'post',
	  'post__in',
	  'post__not_in',
	  'post_format',
	  'post_mime_type',
	  'post_status',
	  'post_tag',
	  'post_type',
	  'posts',
	  'posts_per_archive_page',
	  'posts_per_page',
	  'preview',
	  'robots',
	  's',
	  'search',
	  'second',
	  'sentence',
	  'showposts',
	  'static',
	  'status',
	  'subpost',
	  'subpost_id',
	  'tag',
	  'tag__and',
	  'tag__in',
	  'tag__not_in',
	  'tag_id',
	  'tag_slug__and',
	  'tag_slug__in',
	  'taxonomy',
	  'tb',
	  'term',
	  'terms',
	  'theme',
	  'title',
	  'type',
	  'types',
	  'w',
	  'withcomments',
	  'withoutcomments',
	  'year'
	];

	return in_array( $str, $reserved );

}

/**
 * Helper stuff
 *
 */

/**
 * Echoes results of print_r in pre tag
 *
 * @param mixed $value any value for print_r
 * @param string $format 'echo' to render, 'comment' to html comment, 'var' to store
 * @param string $print Pre-set message
 * @param string $access 'super' default, 'guest' for everyone else
 *
 * @return string only if 'var' format
 */
function kstshn_pre_print( $value, string $format = 'echo', string $print = '', string $access = 'super' ) : string {
	if ( is_super_admin() || 'guest' === $access ) {

		$print .= ( 'comment' === $format ? '<!-- ' . $print : '<pre> ' . $print );
		$print .= print_r( $value, true );
		$print .= ( 'comment' === $format ? ' -->' : ' </pre>' );

		if ('echo' === $format || 'comment' === $format ) {
		    echo $print;
        } elseif ('var' === $format) {
		    return $print;
        }
	}
    return false;
}


/**
 * Get variable value if it is set
 * Else return something else (false by default)
 *
 * @param mixed $variable Variable to check
 * @param mixed $else Return if variable not set
 *
 * @return mixed Variable or else!
 * */
function kstshn_empty_or_else( $variable, $else = false ) {
	return ( empty( $variable ) ? $else : $variable );
}

function kstshn_array_remove_empty( $haystack ) {
	foreach ( $haystack as $key => $value ) {
		if ( is_array( $value ) ) {
			$haystack[ $key ] = kstshn_array_remove_empty( $haystack[ $key ] );
		}

		if ( empty( $haystack[ $key ] ) ) {
			unset( $haystack[ $key ] );
		}
	}

	return $haystack;
}

/**
 * Determine if array is associative
 *
 * @param mixed $arr Array to check
 *
 * @return bool
 * */
function kstshn_isAssoc( array $arr ) {
	if ( array() === $arr ) {
		return false;
	}

	return array_keys( $arr ) !== range( 0, count( $arr ) - 1 );
}

/**
 * Get post featured image(s)
 *
 * @return mixed images Array OR and image String
 */
function wtvr_get_featured_images( $post_id ) {

    $post_gallery = get_field( 'featured_gallery', $post_id );
    if ( $post_gallery ) {
        return $post_gallery;
    }

    $post_thumb = get_post_thumbnail_id( $post_id );
    if ( $post_thumb ) {
        return [ $post_thumb ];
    }

    // FIX: safe-check before accessing array offsets
    $attachment_rows = get_field( 'files', $post_id );

    if ( !empty($attachment_rows) && is_array($attachment_rows) ) {

        $first = $attachment_rows[0] ?? false;

        if ( $first && !empty($first['file']['ID']) ) {
            return [ $first['file']['ID'] ];
        }
    }

    return false;
}
/**
 * Responsive aspect ratio image
 */
function wtvr_thumbnail( $image = false, $cropping = 'cover', $format = 'id array' ) {

	echo '<div class="ratio-wrap' . ( ! $image ? ' placeholder' : '' ) . '">';

	if ( $image && 'id array' === $format ) {
		// srcset for lazy sizes
		$srcset = wp_get_attachment_image_srcset( $image );
		// src array with 0 - url, 1 - width, 2- height
		$src = wp_get_attachment_image_src( $image, 'full' );

		if ( is_array( $src ) ) {

			$ratio = round( 100 * $src[2] / $src[1], 2 );

//			if ( $ratio > 100 && $cropping = 'cover' ) {
//				$cropping = 'contain';
//			}
//			if ( $ratio < 100 && $cropping = 'contain' ) {
//				$cropping = 'cover';
//			}

			echo sprintf( '<img class="lazyload %s" data-w="%s" data-h="%s"
											 data-r="%s" data-srcset="%s" data-src="%s" data-sizes="auto">',
			  $cropping,
			  $src[1], // Height
			  $src[2], // Width
			  $ratio,
			  $srcset,
			  $src[0],
			);
		}

	} elseif ( is_string( $image ) && 'url string' === $format ) {

		echo sprintf( '<img class="lazyload url-only" data-src="%1$s">',
		  $image,
		);

	}

	echo '</div>';

}

/**
 * Localize args passed to a template file and make unset false
 *
 * @param $args array passed in get_template_part()
 * @param $expected_args array expected args
 *
 * @return array Array with set or 'false' values
 */

function wtvr_localize_args( $args, $expected_args ) {
	$a = [];
	foreach ( $expected_args as $key => $arg ) {

		$a[ $arg ] = ( isset( $args[ $arg ] ) ) ? $args[ $arg ] : false;
	}

	return $a;
}


/**
 * Render post slider from post IDs array
 *
 * @param $posts_IDs array Array of post IDs
 *
 * @return bool false if no ids
 *
 * todo: add checks for post existence?
 */
function wtvr_posts_slider( $posts_IDs ) {

	if ( is_array( $posts_IDs ) ) : ?>

        <div class="slider related-items-slider">
            <div class="slides">
				<?php foreach ( $posts_IDs as $post_ID ) : ?>
                    <div class="slide">
						<?php
						get_template_part( 'template-parts/content', 'related', [
						  'id' => $post_ID,
						] );
						?>
                    </div>
				<?php endforeach; ?>
            </div>
        </div>

	<?php endif;

	return false;
}


function wtvr_get_language_choices() {

	$languages = [];
	//get languages;
	switch_to_blog( 1 );

	$language_terms = get_terms( [
	  'taxonomy'   => 'language',
	  'hide_empty' => false,
	] );
	restore_current_blog();

	if (!empty($language_terms) && is_array($language_terms)) {

		foreach ( $language_terms as $language_term ) {
			if ( 0 === $language_term->parent ) {
				$languages[ $language_term->slug ] = $language_term->name;
			} else {
				$parent_term                     = get_term( $language_term->parent );
				if (is_object($parent_term)) {
					$languages[ $parent_term->slug ] = $parent_term->name;
				}
			}
		}
	}
	return $languages;
}


function wtvr_get_main_site_content( $post_type ) {
	$posts = [];

	$blog_ID = get_current_blog_id();

	if ( $blog_ID == 1 ) {
		$connected_blog_ID = 3;
	} elseif ( $blog_ID == 3 ) {
		$connected_blog_ID = 1;
	}

	switch_to_blog( $connected_blog_ID );
	$query = get_posts( [
	  'post_type' => $post_type,
	  'nopaging'  => true,
	] );
	restore_current_blog();

	if ( !empty( $query ) && is_array( $query ) ) {
		foreach ( $query as $post ) {
			$posts[ $post->ID ] = $post->post_title;
		}
	}

	return $posts;
}

function kstshn_call_attachment_by_its_name( $name ) {
	$args = array(
	  'posts_per_page' => 1,
	  'post_type'      => 'attachment',
	  'name'           => trim( $name ),
	);

	$get_attachment = new WP_Query( $args );

	if ( ! $get_attachment || ! isset( $get_attachment->posts, $get_attachment->posts[0] ) ) {
		return false;
	}

	return $get_attachment->posts[0];
}

function enable_extended_upload( $mime_types = array() ) {
	$mime_types['jpg'] = 'image/jpeg';

	return $mime_types;
}

add_filter( 'upload_mimes', 'enable_extended_upload' );

function wtvr_thumbnail_by_YT_hash_to_media_library( $yt_hash, $title = false, $parent_id = 0 ) {

	$existing_attachment = kstshn_call_attachment_by_its_name( $yt_hash . '-thumbnail' );

	// check if an attachment for this hash exists
	if ( $existing_attachment ) {
		$attachment_ID = $existing_attachment->ID;
	} else {

		if ( ! class_exists( 'WP_Http' ) ) {
			include_once( ABSPATH . WPINC . '/class-http.php' );
		}

		$http = new WP_Http();

		$image_url = 'https://img.youtube.com/vi/' . $yt_hash . '/maxresdefault.jpg';

		$response = $http->request( $image_url );

		if ( $response['response']['code'] != 200 ) {

			$image_url = 'https://img.youtube.com/vi/' . $yt_hash . '/hqdefault.jpg';

			$response = $http->request( $image_url );

			if ( $response['response']['code'] != 200 ) {

				$image_url = 'https://img.youtube.com/vi/' . $yt_hash . '/mqdefault.jpg';

				$response = $http->request( $image_url );

				if ( $response['response']['code'] != 200 ) {

					$image_url = 'https://img.youtube.com/vi/' . $yt_hash . '/sddefault.jpg';

					$response = $http->request( $image_url );

					if ( $response['response']['code'] != 200 ) {

						return false;
					}
				}
			}
		}

		$upload = wp_upload_bits( $yt_hash . '.jpg', null, $response['body'] );
		if ( ! empty( $upload['error'] ) ) {
			return $upload['error'];
		}

		$file_path = $upload['file'];

		$filename      = basename( $image_url );
		$wp_filetype   = wp_check_filetype( $filename, null );
		$wp_upload_dir = wp_upload_dir();
		$post_title    = ( $title ? $title . ' Thumbnail' : 'Youtube' . $yt_hash . ' Thumbnail' );


		$attachment = array(
		  'guid'           => $wp_upload_dir['url'] . '/' . $yt_hash . '-thumbnail',
		  'post_mime_type' => $wp_filetype['type'],
		  'post_title'     => $post_title,
		  'post_name'      => $yt_hash . '-thumbnail',
		  'post_content'   => '',
		  'post_status'    => 'inherit',
		);

		$attachment_ID = wp_insert_attachment( $attachment, $file_path, $parent_id );
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		$attach_data = wp_generate_attachment_metadata( $attachment_ID, $file_path );
		wp_update_attachment_metadata( $attachment_ID, $attach_data );

	}

	return $attachment_ID;
}





function wtvr_build_term_tree_recursive( $parent, $taxonomy, $name = 'root-directory', $count = false){

    $in_root = ($name === 'root-directory');

    // RECURSION <LI>

    //      TITLE

    if ( !$in_root ){
        echo sprintf(
            '<div class="directory-title folder-title">%1$s <a data-term="%4$s" class="folder-toggle">%2$s</a>%3$s</div>',
            wtvr_svg('folder'),
            $name,
            ($count ? '<span class="directory-count">'.$count.'</span>' : ''),
            $parent,
        );
    }


    //      CONTENT
    echo '<ul class="directory-content">';

    //          (SUB) FOLDERS

    // Get child terms/folders (root terms, if first iteration)
    $direct_children_terms = wtvr_direct_child_terms($parent, $taxonomy);

    // If they exist, start recursion
    if ( is_array($direct_children_terms) ) {

        foreach ( $direct_children_terms as $term ) {

            echo '<li class="directory">';

            // RECURSION SEAM
            wtvr_build_term_tree_recursive( $term->term_id, $taxonomy, $term->name, $term->count );

            echo '</li>';

        }

        //          FILES
        // see ajax-loader.php
    }

    echo '</ul>';

    // RECURSION </LI>

}

/**
 * Deisplay book covers for book translations
 *
 * @param array $translation Массив с данными перевода
 */
function wtvr_output_cover($translation) {
    $cover = $translation['cover'];
    $language = $translation['language'][0]->name;
    ?>
    <div class="cover-slide" data-language="<?php echo esc_attr($language); ?>">
    <?php
    if (is_array($cover) && isset($cover['sizes'])):
    ?>
        <img src="<?php echo esc_url($cover['sizes']['medium']); ?>"
            srcset="<?php echo esc_attr(wp_get_attachment_image_srcset($cover['ID'], 'full')); ?>"
            sizes="(max-width: 768px) 100vw, 300px"
            alt="<?php echo esc_attr($translation['title']); ?>"
            class="translation-cover"
            width="<?php echo esc_attr($cover['sizes']['medium-width']); ?>"
            height="<?php echo esc_attr($cover['sizes']['medium-height']); ?>"
        >
    <?php
    else:
    ?>
        <img src="<?php echo esc_url($cover); ?>" alt="<?php echo esc_attr($translation['title']); ?>" class="translation-cover">
    <?php
    endif;
    ?>
    </div>
    <?php
}

/**
 * Get unique languages for a book
 *
 * @param int|null $post_id Post ID. If null, uses current post ID.
 * @return array Array of unique language names.
 */
function get_unique_languages($post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }

    $translations = get_field('translations', $post_id);
    $unique_languages = [];

    if ($translations) {
        foreach ($translations as $translation) {
            if (isset($translation['language']) && is_array($translation['language'])) {
                foreach ($translation['language'] as $language) {
                    if (isset($language->name)) {
                        $unique_languages[$language->name] = $language->name;
                    }
                }
            }
        }
        asort($unique_languages);
    }

    return $unique_languages;
}

/**
 * Get the earliest publication year from book translations
 *
 * @param int|null $post_id Post ID. If null, uses current post ID.
 * @return string|false Earliest publication year, or false if no year found.
 */
function get_earliest_publication_date($post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }

    $translations = get_field('translations', $post_id);
    $earliest_year = false;

    if ($translations) {
        foreach ($translations as $translation) {
            if (isset($translation['first_published']) && !empty($translation['first_published'])) {
                $year = trim($translation['first_published']);
                if (is_numeric($year) && strlen($year) === 4) {
                    if (!$earliest_year || $year < $earliest_year) {
                        $earliest_year = $year;
                    }
                }
            }
        }
    }

    return $earliest_year;
}
