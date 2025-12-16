<?php
/**
 * Template part for displaying posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Whatever
 *
 * @var $args array
 */
$a = wtvr_localize_args( $args, [
  'no-thumbnail',
] );

$post_type        = get_post_type();
$post_type_layout = get_field( $post_type . '-layout', 'option' );

$custom_features = get_field( 'post-options' );
$attachments     = get_field( 'post-attachments' );
$extenal         = isset( $attachments['external-link'] );
$extenal_author_name_set  = isset( $attachments['external-author']['external-author-name'] ) && strlen( $attachments['external-author']['external-author-name'] ) > 1;
$extenal_author_link_set  = isset( $attachments['external-author']['external-author-link'] ) && strlen( $attachments['external-author']['external-author-link'] ) > 1;


if ( isset( $custom_features['layout'] ) ) {
	if ( 'landscape' !== $custom_features['layout'] ) {
		$layout = $custom_features['layout'];
	} elseif ( $post_type_layout ) {
		$layout = $post_type_layout;
	} else {
		$layout = 'landscape';
	}
} elseif ( $post_type_layout ) {
	$layout = $post_type_layout;
} else {
	$layout = 'landscape';
}

// FEATURED VIDEO SUPPORT FIX
$feature_type = get_field('feature_type');

if ( $feature_type === 'video' ) {

    // ACF field for video URL
    $video_url  = get_field('featured_video_url');

    // ACF field for uploaded video
    $video_file = get_field('featured_video_file');

    if ( $video_url ) {
        echo '<div class="featured-video-wrapper">';
        echo wp_oembed_get( $video_url ); // YouTube/Vimeo auto-embed
        echo '</div>';
        return;
    }

    if ( !empty($video_file['url']) ) {
        echo '<div class="featured-video-wrapper">';
        echo '<video controls preload="metadata" style="max-width:100%;height:auto;">';
        echo '<source src="' . esc_url($video_file['url']) . '" type="video/mp4">';
        echo '</video>';
        echo '</div>';
        return;
    }
}


$images = wtvr_get_featured_images( get_the_ID() );

$video = get_field( 'featured_video' );
?>

<div class="grid grid-3 grid-article">


	<?php if ( ! $a['no-thumbnail'] && 'people' !== $post_type) : ?>
        <section class="feature <?php echo $layout; ?>">
			<?php if ( $video ) {
				get_template_part( 'template-parts/snippet', 'video', [
				  'url'   => $video,
				  'class' => $layout,
				] );

			} else {

				get_template_part( 'template-parts/snippet', 'thumbnail', [
				  'class'    => $layout,
				  'images'   => $images,
				  'cropping' => ( isset( $custom_features['cropping'] ) ) ? $custom_features['cropping'] : 'cover'
				] );
			} ?>
        </section>

	<?php endif; ?>

	<?php
	// get related IDs
	$related_IDs = get_field( 'related_content' );
	// Only show sidebar if there's related content
	if ( $related_IDs ) :

		// get frontpage ID (to remove it)
		$frontpage_id = get_option( 'page_on_front' );

		// if front frontpage is related, remove
		if ( ( $key = array_search( $frontpage_id, $related_IDs ) ) !== false ) {
			unset( $related_IDs[ $key ] );
		} // todo: replace with a reusable function

		// continue if anything left
		if ( ! empty( $related_IDs ) ) : ?>

            <section class="meta <?php echo $layout; ?>-meta">

				<?php
				// get related content style ('group' is default, 'everything' is optional
				$related_content_style = get_field( 'related_style' );

				// if one slider with custom title
				if ( 'everything' === $related_content_style ) {

					// get custom title is set
					$related_content_title = get_field( 'related_content_title' );

					// if not set, use default
					$title = ( $related_content_title ? $related_content_title : 'Related content' );

					// render custom title
					echo '<h4 class="related-items-title">' . $title . '</h4>';
					// render slider with every related item
					wtvr_posts_slider( $related_IDs );

				} else {
					// if style not set or not 'everything' (expectedly, is 'group')

					// prepare sorted bin for related posts by post type
					$related_IDs_sorted = [];

					// cycle related items
					foreach ( $related_IDs as $related_ID ) {
						// get each item post type
						$related_type = get_post_type( $related_ID );
						$related_status = get_post_status( $related_ID );
						if ( "publish" == $related_status ){
							// put related id into the sorted basket under it's post type key
							$related_IDs_sorted[ $related_type ][] = $related_ID;
						}


					}

					// now cycle sorted bin by post type
					foreach ( $related_IDs_sorted as $related_post_type => $sorted_IDs ) {

                        // exclude memorial relations
                        if ('memorial-videos' !== $related_post_type && 'people' !== $post_type) {

                            // setup group title
                            if ( 'post' === $related_post_type ) {
                                // If post, use custom blog items naming
                                $title = 'Updates'; // todo get blog namin
                            } elseif ( 'page' === $related_post_type ) {
                                // If post, use custom blog items naming
                                $title = 'Related pages'; // todo get blog namin
                            } else {
                                // Else,just Capitalize post type slug
                                $title = ucfirst( $related_post_type );
                            }
                            // render related post type title
                            echo '<h4 class="related-items-title">' . $title . '</h4>';
                            // render related post type slider
                            wtvr_posts_slider( $sorted_IDs );
                        }

						?>

						<?php

					} // foreach $sorted_related
				} ?>

            </section>

		<?php endif; // if there are related items (after removal of frontpage)
		?>

	<?php endif; // if get_field('related_content')?>

    <article id="post-<?php the_ID(); ?>" <?php post_class( [ $layout, 'content' ] ); ?>>

        <?php ?>

        <header class="entry-header">

			<?php do_action( 'wtvr_before_title' ); ?>

			<?php

            if ('people' === $post_type && $images){
                echo '<div class="avatar">';
                wtvr_thumbnail($images[0]);
                echo '</div>';

            }

			if ( is_singular() ) :
				the_title( '<h1 class="entry-title">', '</h1>' );
			else :
				the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' );
			endif;

			$subtitle = get_field( 'subtitle' );
			if ( $subtitle ) : ?>
                <h3 class="subtitle">
					<?php echo $subtitle; ?>
                </h3>

        </header><!-- .entry-header -->

        <?php endif; ?>

        <?php do_action( 'wtvr_after_title' ); ?>

        <div class="entry-content">

			<?php
			if ( is_privacy_policy() ) {
			?>
				<p>Last updated: <?php echo get_the_date(); ?></p>
			<?php
			}
            // Populate then render joined by separator
			$attachment_links = [];
			// FILES
			if ( have_rows( 'files' ) ) :
				while ( have_rows( 'files' ) ) :
					the_row();
					$label = get_sub_field( 'label' );
					$file  = get_sub_field( 'file' );
					if ( $file ) :

						$url   = $file['url'];
						$ext   = pathinfo( $file['filename'], PATHINFO_EXTENSION );
						$label = ( $label ? $label : 'View / Download (.' . $ext . ')' );

						$attachment_links[] = sprintf(
						  '<a class="attachment file-link" href="%1$s" target="_blank">%2$s</a>',
						  $url,
						  $label

						);
					endif;
				endwhile;
			endif;
            // External Author link
            if ($extenal_author_name_set || $extenal_author_link_set){

                $extenal_author_name = ( $extenal_author_name_set ) ?
                    $attachments['external-author']['external-author-name'] :
                    'Author';

                $extenal_author_link = ( $extenal_author_link_set ) ?
                    $attachments['external-author']['external-author-link'] :
                    '';


                $attachment_links[] = sprintf( '<a class="external-author" href="%1$s" target="_blank">by %2$s</a>',
                    $extenal_author_link,
                    $extenal_author_name,
                );
            }
			// LINKS
            if ($extenal && strlen( $attachments['external-link'] ) > 1 ) :

				$attachment_links[] = sprintf( '<a class="attachment" href="%1$s" target="_blank">%2$s</a>',
				  $attachments['external-link'],
				  'External link'
				);

			endif;
			?>

            <p class="attachments">
				<?php echo join( '<span> | </span>', $attachment_links ); ?>

            </p>

            <div class="info">
	            <?php

	            if ( get_post_type() == 'videos'){

		            $str_fields = [
		              'year'      => 'Year',
		              'published' => 'Published',
		            ];


	            } else {
		            $str_fields = [
		              'year'      => 'Year',
		              'published' => 'Published',
		              'notes'     => 'Notes',
		            ];
	            }


	            $obj_fields = [
	              'co-authors'   => 'Co-author(s)',
	              'translators'  => 'Translator(s)',
	              'interviewers' => 'Interviewer(s)',
	            ];


	            foreach ( $str_fields as $field_slug => $label ) {

		            $value = get_field( $field_slug );
		            if ( $value ) : ?>
                        <span class="<?php echo $field_slug; ?>">
                            <b> <?php echo $label; ?>:</b> <?php echo $value; ?>
                        </span>
		            <?php endif;

	            }

	            foreach ( $obj_fields as $field_slug => $label ) {

		            $post_objects = get_field( $field_slug );
		            if ( $post_objects ) : ?>
                        <span class="<?php echo $field_slug; ?>">
                            <b> <?php echo $label; ?>:</b>
				            <?php $titles = array_map( function ( $post_obj ) {
					            return $post_obj->post_title;
				            }, $post_objects );

				            sort( $titles );

				            echo join( ', ', $titles ); ?>
                        </span>
		            <?php endif;

	            }
	            ?>
            </div>

			<?php
			$summary      = get_field( 'summary' );
			$hide_summary = get_field( 'hide_summary' );
			if ( $summary && ! $hide_summary ) : ?>
                <div class="summary">
					<?php echo wpautop( $summary ); ?>
                </div>
			<?php endif; ?>

			<?php
            do_action( 'wtvr_before_content' );
			the_content(
			  sprintf(
				wp_kses(
				/* translators: %s: Name of current post. Only visible to screen readers */
				  __( 'Continue reading<span class="screen-reader-text"> "%s"</span>', 'wtvr' ),
				  [
					'span' => [
					  'class' => [],
					],
				  ]
				),
				wp_kses_post( get_the_title() )
			  )
			);

            if ($related_IDs && 'people' === $post_type &&
                isset($related_IDs_sorted['memorial-videos']) &&
                is_array($related_IDs_sorted['memorial-videos'])) {

                foreach ($related_IDs_sorted['memorial-videos'] as $related_video_ID) {
                    $video = get_field('featured_video', $related_video_ID);

                    if ($video) {
                        get_template_part('template-parts/snippet', 'video', [
                            'url'   => $video,
                            'class' => 'grid',
                        ]);
                    }
                }
            }



            if ($related_IDs && 'people' === $post_type && is_array($related_IDs_sorted['memorials'])){


                foreach ($related_IDs_sorted['memorials'] as $related_memo_ID) {


                    echo get_the_excerpt($related_memo_ID);

                    echo sprintf(
                        '<p><a href="%s">— %s</a></p>',
                        get_permalink($related_memo_ID),
                        get_the_title($related_memo_ID)
                    );
                }
            }

			do_action( 'wtvr_after_content' );

            if ( get_field('date') ) {

                get_template_part( 'template-parts/snippet', 'share' );

            }
			?>
        </div><!-- .entry-content -->

        <footer class="entry-footer">
			<?php // wtvr_entry_footer(); ?>
        </footer><!-- .entry-footer -->


    </article><!-- #post-<?php the_ID(); ?> -->
</div>
