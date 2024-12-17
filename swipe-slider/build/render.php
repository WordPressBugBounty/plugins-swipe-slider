<?php
extract( $attributes );

foreach ( $slides as $index => $slide ) {
	$blocks = parse_blocks( $slide['content'] );
	$slideContent = '';

	foreach ( $blocks as $block ) {
		$slideContent .= render_block( $block );
	}

	$attributes['slides'][$index]['content'] = $slideContent;
}

$id = wp_unique_id( 'evssSlider-' );
?>
<div <?php echo get_block_wrapper_attributes([ 'class' => "align$align" ]); ?> id='<?php echo esc_attr( $id ); ?>' data-attributes='<?php echo esc_attr( wp_json_encode( $attributes ) ); ?>'></div>