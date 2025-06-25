<?php
$id = wp_unique_id( 'evssSlider-' );
$newAttr = $attributes;
extract( $newAttr );

foreach ( $slides as $index => $slide ) {
	$newAttr['slides'][$index]['content'] = '';
}

if( !function_exists( 'evssAllowedHTML' ) ){
	function evssAllowedHTML(){
		global $allowedposttags;

		return wp_parse_args( [
			'style' => [],
			'svg' => [
				'xmlns' => [],
				'viewbox' => [],
				'width' => [],
				'height' => [],
				'fill' => [],
				'class' => []
			],
			'path' => [
				'd' => [],
				'fill' => [],
				'stroke' => [],
				'stroke-width' => [],
				'class' => []
			],
			'rect' => [
				'x' => [],
				'y' => [],
				'width' => [],
				'height' => [],
				'fill' => [],
				'stroke' => [],
				'stroke-width' => [],
				'class' => []
			],
			'circle' => [
				'cx' => [],
				'cy' => [],
				'r' => [],
				'fill' => [],
				'stroke' => [],
				'stroke-width' => [],
				'class' => []
			],
			'ellipse' => [
				'cx' => [],
				'cy' => [],
				'rx' => [],
				'ry' => [],
				'fill' => [],
				'stroke' => [],
				'stroke-width' => [],
				'class' => []
			],
			'line' => [
				'x1' => [],
				'y1' => [],
				'x2' => [],
				'y2' => [],
				'stroke' => [],
				'stroke-width' => [],
				'class' => []
			],
			'polyline' => [
				'points' => [],
				'fill' => [],
				'stroke' => [],
				'stroke-width' => [],
				'class' => []
			],
			'polygon' => [
				'points' => [],
				'fill' => [],
				'stroke' => [],
				'stroke-width' => [],
				'class' => []
			],
			'g' => [
				'fill' => [],
				'transform' => [],
				'class' => []
			],
			'title' => [],
			'desc' => [],
			'iframe' => [
				'src' => [],
				'width' => [],
				'height' => [],
				'frameborder' => [],
				'allowfullscreen' => [],
				'class' => []
			],
		], $allowedposttags );
	}
}
?>
<div
	<?php echo get_block_wrapper_attributes( [ 'class' => "align$align" ] ); ?>
	id='<?php echo esc_attr( $id ); ?>'
	data-attributes='<?php echo esc_attr( wp_json_encode( $newAttr ) ); ?>'
>
	<div id='<?php echo esc_attr( $id ); ?>-contents' style='display: none;'>
		<?php foreach ( $attributes['slides'] as $index => $slide ) {
			$blocks = parse_blocks( $slide['content'] );
			$slideContent = '';

			foreach ( $blocks as $block ) {
				$slideContent .= render_block( $block );
			}
		?>
			<div id='slideContent-<?php echo esc_attr( $index ); ?>'><?php echo wp_kses( $slideContent, evssAllowedHTML() ); ?></div>
		<?php } ?>
	</div>
</div>