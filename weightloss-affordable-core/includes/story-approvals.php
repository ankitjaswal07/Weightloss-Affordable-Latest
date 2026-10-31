<?php
/** Explicit editorial approval for published patient stories. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function wla_experience_review_approval_box() {
	add_meta_box( 'wla-story-approval', __( 'Patient story publication review', 'weightloss-affordable-core' ), 'wla_experience_review_approval_box_render', 'wla_review', 'side', 'high' );
}
add_action( 'add_meta_boxes_wla_review', 'wla_experience_review_approval_box' );
function wla_experience_review_approval_box_render( $post ) {
	wp_nonce_field( 'wla_review_approval_save', 'wla_review_approval_nonce' );
	$approved = '1' === get_post_meta( $post->ID, '_wla_review_approved', true );
	?>
	<p><label><input type="checkbox" name="wla_review_approved" value="1" <?php checked( $approved ); ?> /> <?php esc_html_e( 'Consent and substantiation reviewed; approve public display.', 'weightloss-affordable-core' ); ?></label></p>
	<p class="description"><?php esc_html_e( 'Only explicitly approved stories appear in the WLA results component. Review consent, context, imagery and applicable clinical/legal requirements outside WordPress before checking this box.', 'weightloss-affordable-core' ); ?></p>
	<?php
}
function wla_experience_review_approval_save( $post_id ) {
	if ( ! isset( $_POST['wla_review_approval_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wla_review_approval_nonce'] ) ), 'wla_review_approval_save' ) ) { return; }
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
	if ( 'wla_review' !== get_post_type( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) { return; }
	if ( isset( $_POST['wla_review_approved'] ) && '1' === sanitize_text_field( wp_unslash( $_POST['wla_review_approved'] ) ) ) {
		update_post_meta( $post_id, '_wla_review_approved', '1' );
	} else {
		delete_post_meta( $post_id, '_wla_review_approved' );
	}
}
add_action( 'save_post_wla_review', 'wla_experience_review_approval_save' );
