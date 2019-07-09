<?php
/**
 * Posts widget.
 *
 * @package Stein Kit
 * @since   1.0
 */

defined( 'ABSPATH' ) || die( 'Cheatin\' Uh?' );

if ( ! class_exists( 'Stein_Kit_Posts_Widget' ) && class_exists( 'WP_Widget' ) ) {
	/**
	 * Posts widget class.
	 *
	 * @since 1.0
	 */
	final class Stein_Kit_Posts_Widget extends WP_Widget {

		/**
		 * The class constructor.
		 *
		 * @since  1.0
		 * @access protected
		 * @return void
		 */
		public function __construct() {
			parent::__construct(
				'stein_kit_posts',
				esc_html__( 'Posts', 'sandstein' ),
				array(
					'classname'   => 'widget_stein_kit_posts',
					'description' => esc_html__( 'Displays your site\'s posts.', 'sandstein' ),
				)
			);
		}

		/**
		 * Widget default args.
		 *
		 * @since  1.0
		 * @access protected
		 * @return array
		 */
		public function defaults() {
			return apply_filters(
				'stein_kit_posts_widget_defaults',
				array(
					'title'       => '',
					'image_style' => '',
					'limit'       => 5,
					'offset'      => 0,
					'category'    => 0,
					'orderby'     => 'date',
					'order'       => 'DESC',
				)
			);
		}

		/**
		 * Update widget backend settings.
		 *
		 * @since  1.0
		 * @access protected
		 * @param  array $new New widget instance.
		 * @param  array $old Old widget instance.
		 * @return array
		 */
		public function update( $new, $old ) {
			$instance = (array) $old;
			$new      = wp_parse_args( (array) $new, $this->defaults() );

			$instance['title']       = sanitize_text_field( $new['title'] );
			$instance['image_style'] = sanitize_text_field( $new['image_style'] );
			$instance['limit']       = absint( $new['limit'] );
			$instance['offset']      = absint( $new['offset'] );
			$instance['category']    = absint( $new['category'] );
			$instance['orderby']     = sanitize_text_field( $new['orderby'] );
			$instance['order']       = sanitize_text_field( $new['order'] );

			if ( $instance['limit'] < 1 ) {
				$instance['limit'] = 1;
			}

			return $instance;
		}

		/**
		 * Outputs the settings update form.
		 *
		 * @since  1.0
		 * @access protected
		 * @param  array $instance Widget instance.
		 * @return void
		 */
		public function form( $instance ) {
			$instance    = wp_parse_args( (array) $instance, $this->defaults() );
			$orders      = apply_filters( 'stein_kit_posts_widget_order_choices', array() );
			$sorts       = apply_filters( 'stein_kit_posts_widget_sort_choices', array() );
			$categories  = apply_filters( 'stein_kit_posts_widget_category_choices', array() );
			$image_style = apply_filters(
				'stein_kit_posts_widget_image_style_choices',
				array(
					''             => esc_html__( 'Square', 'stein-kit' ),
					'rounded-full' => esc_html__( 'Circle', 'stein-kit' ),
				)
			);
			?>
				<p>
					<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
						<?php esc_html_e( 'Title:', 'stein-kit' ); ?>
					</label>
					<input id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>">
				</p>

				<?php if ( ! empty( $image_style ) ) : ?>
					<p>
						<label for="<?php echo esc_attr( $this->get_field_id( 'category' ) ); ?>">
							<?php esc_html_e( 'Image Style:', 'stein-kit' ); ?>
						</label>

						<select id="<?php echo esc_attr( $this->get_field_id( 'image_style' ) ); ?>" class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'image_style' ) ); ?>">
							<?php foreach ( $image_style as $id => $name ) : ?>
								<option value="<?php echo esc_attr( $id ); ?>"<?php selected( $instance['image_style'] === $id ); ?>><?php echo esc_html( $name ); ?></option>
							<?php endforeach; ?>
						</select>
					</p>
				<?php endif; ?>

				<?php if ( ! empty( $categories ) ) : ?>
					<p>
						<label for="<?php echo esc_attr( $this->get_field_id( 'category' ) ); ?>">
							<?php esc_html_e( 'Category:', 'stein-kit' ); ?>
						</label>

						<select id="<?php echo esc_attr( $this->get_field_id( 'category' ) ); ?>" class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'category' ) ); ?>">
							<option value="0"<?php selected( 0 === absint( $instance['category'] ) ); ?>><?php echo esc_html__( 'All', 'stein-kit' ); ?></option>
							<?php foreach ( $categories as $id => $name ) : ?>
								<option value="<?php echo esc_attr( $id ); ?>"<?php selected( absint( $instance['category'] ) === $id ); ?>><?php echo esc_html( $name ); ?></option>
							<?php endforeach; ?>
						</select>
					</p>
				<?php endif; ?>

				<p>
					<label for="<?php echo esc_attr( $this->get_field_id( 'limit' ) ); ?>">
						<?php esc_html_e( 'Limit:', 'stein-kit' ); ?>
					</label>

					<input id="<?php echo esc_attr( $this->get_field_id( 'limit' ) ); ?>" class="tiny-text" name="<?php echo esc_attr( $this->get_field_name( 'limit' ) ); ?>" type="number" min="1" value="<?php echo esc_attr( $instance['limit'] ); ?>">
				</p>

				<?php if ( ! empty( $orders ) ) : ?>
					<p>
						<label for="<?php echo esc_attr( $this->get_field_id( 'orderby' ) ); ?>">
							<?php esc_html_e( 'Order by:', 'stein-kit' ); ?>
						</label>

						<select id="<?php echo esc_attr( $this->get_field_id( 'orderby' ) ); ?>" class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'orderby' ) ); ?>">
							<?php foreach ( $orders as $key => $label ) : ?>
								<option value="<?php echo esc_attr( $key ); ?>"<?php selected( $instance['orderby'] === $key ); ?>><?php echo esc_html( $label ); ?></option>
							<?php endforeach; ?>
						</select>
					</p>
				<?php endif; ?>

				<?php if ( ! empty( $sorts ) ) : ?>
					<p>
						<label for="<?php echo esc_attr( $this->get_field_id( 'order' ) ); ?>">
							<?php esc_html_e( 'Sort:', 'stein-kit' ); ?>
						</label>

						<select id="<?php echo esc_attr( $this->get_field_id( 'order' ) ); ?>" class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'order' ) ); ?>">
							<?php foreach ( $sorts as $key => $label ) : ?>
								<option value="<?php echo esc_attr( $key ); ?>"<?php selected( $instance['order'] === $key ); ?>><?php echo esc_html( $label ); ?></option>
							<?php endforeach; ?>
						</select>
					</p>
				<?php endif; ?>

				<p>
					<label for="<?php echo esc_attr( $this->get_field_id( 'offset' ) ); ?>">
						<?php esc_html_e( 'Offset:', 'stein-kit' ); ?>
					</label>

					<input id="<?php echo esc_attr( $this->get_field_id( 'offset' ) ); ?>" class="tiny-text" name="<?php echo esc_attr( $this->get_field_name( 'offset' ) ); ?>" type="number" min="0" value="<?php echo esc_attr( $instance['offset'] ); ?>">
				</p>
			<?php
		}

		/**
		 * Outputs the widget content.
		 *
		 * @since  1.0
		 * @access protected
		 * @param  array $args Widget arguments.
		 * @param  array $instance Saved widget instance.
		 * @return void
		 */
		public function widget( $args, $instance ) {
			$instance = wp_parse_args( (array) $instance, $this->defaults() );

			echo apply_filters( 'before_widget', $args['before_widget'], $instance, $this->id_base ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

			$title = apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );

			if ( $title ) {
				echo apply_filters( 'before_title', $args['before_title'], $instance, $this->id_base ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo esc_html( $title );
				echo apply_filters( 'after_title', $args['after_title'], $instance, $this->id_base ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}

			$query = new WP_Query(
				array(
					'post_type'           => 'post',
					'ignore_sticky_posts' => 1,
					'posts_per_page'      => $instance['limit'],
					'offset'              => $instance['offset'],
					'cat'                 => $instance['category'],
					'orderby'             => $instance['orderby'],
					'order'               => $instance['order'],
				)
			);

			if ( $query->have_posts() ) :
				?>
				<ul class="tw--mb-6">
					<?php
					while ( $query->have_posts() ) :
						$query->the_post();
						?>
						<li class="tw-flex tw-mb-6">
							<?php if ( has_post_thumbnail() ) : ?>
								<div class="tw-w-20 tw-mr-4">
									<a href="<?php the_permalink(); ?>" class="tw-block tw-bg-overlay tw-aspect-ratio-1/1 tw-relative<?php echo esc_attr( ! empty( $instance['image_style'] ) ? ' tw-' . $instance['image_style'] : '' ); ?>">
										<?php
										the_post_thumbnail(
											'thumbnail',
											array(
												'class' => 'tw-absolute tw-left-0 tw-top-0 tw-h-full tw-w-full tw-object-cover' . ( ! empty( $instance['image_style'] ) ? ' tw-' . $instance['image_style'] : '' ),
												'alt'   => the_title_attribute( 'echo=0' ),
											)
										);
										?>
									</a>
								</div>
							<?php endif; ?>

							<div class="tw-flex-1">
								<h4 class="post-title tw-text-base tw-mb-1"><a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a></h4>

								<div class="post-meta post-meta-bottom tw-text-sm tw-text-soft">
									<time class="post-meta-date" datetime="<?php echo esc_html( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
								</div>
							</div>
						</li>
					<?php endwhile; ?>
				</ul>

				<?php
				wp_reset_postdata();
			endif;

			echo apply_filters( 'after_widget', $args['after_widget'], $instance, $this->id_base ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}
}

/**
 * Register widget.
 *
 * @package Stein Kit
 * @since   1.0
 */
add_action(
	'widgets_init',
	function() {
		register_widget( 'Stein_Kit_Posts_Widget' );
	}
);
