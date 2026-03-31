<?php
declare(strict_types=1);
if ( ! defined( 'ABSPATH' ) ) { exit; }
/**
 * Admin View: Business Edit Meta Boxes
 *
 * Adds meta boxes for Location, Contact, Business Details, and Status
 * to the dbp_business post type editor.
 *
 * @package DirectoriesBuilderPro\Admin\Views
 */
// Register meta boxes.
add_action( 'add_meta_boxes', static function (): void {
    $boxes = [
        'dbp_location' => [ __( 'Location', 'directories-builder-pro' ), 'dbp_render_location_meta_box' ],
        'dbp_contact'  => [ __( 'Contact', 'directories-builder-pro' ), 'dbp_render_contact_meta_box' ],
        'dbp_details'  => [ __( 'Business Details', 'directories-builder-pro' ), 'dbp_render_details_meta_box' ],
        'dbp_status'   => [ __( 'Status', 'directories-builder-pro' ), 'dbp_render_status_meta_box' ],
    ];
    foreach ( $boxes as $id => $info ) {
        add_meta_box( $id, $info[0], $info[1], 'dbp_business', 'normal', 'high' );
    }
} );
// Save meta boxes.
add_action( 'save_post_dbp_business', static function ( int $post_id ): void {
    // Verify nonce.
    if ( ! isset( $_POST['dbp_business_nonce'] ) || ! wp_verify_nonce( $_POST['dbp_business_nonce'], 'dbp_save_business' ) ) {
        return;
    }
    // Permissions.
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }
    // Autosave.
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    $business_service = new \DirectoriesBuilderPro\Services\Business_Service();
    $existing = $business_service->get_business_by_post_id( $post_id );
    $data = [
        'wp_post_id'  => $post_id,
        'name'        => sanitize_text_field( wp_unslash( $_POST['dbp_name'] ?? get_the_title( $post_id ) ) ),
        'address'     => sanitize_text_field( wp_unslash( $_POST['dbp_address'] ?? '' ) ),
        'city'        => sanitize_text_field( wp_unslash( $_POST['dbp_city'] ?? '' ) ),
        'state'       => sanitize_text_field( wp_unslash( $_POST['dbp_state'] ?? '' ) ),
        'zip'         => sanitize_text_field( wp_unslash( $_POST['dbp_zip'] ?? '' ) ),
        'country'     => sanitize_text_field( wp_unslash( $_POST['dbp_country'] ?? 'US' ) ),
        'lat'         => floatval( $_POST['dbp_lat'] ?? 0 ),
        'lng'         => floatval( $_POST['dbp_lng'] ?? 0 ),
        'phone'       => sanitize_text_field( wp_unslash( $_POST['dbp_phone'] ?? '' ) ),
        'website'     => esc_url_raw( wp_unslash( $_POST['dbp_website'] ?? '' ) ),
        'email'       => sanitize_email( wp_unslash( $_POST['dbp_email'] ?? '' ) ),
        'price_level' => max( 1, min( 4, absint( $_POST['dbp_price_level'] ?? 1 ) ) ),
        'status'      => sanitize_text_field( wp_unslash( $_POST['dbp_status'] ?? 'active' ) ),
        'featured'    => isset( $_POST['dbp_featured'] ) ? 1 : 0,
    ];
    // Parse hours.
    $hours = [];
    $days  = [ 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' ];
    foreach ( $days as $day ) {
        if ( isset( $_POST["dbp_hours_{$day}_closed"] ) ) {
            $hours[ $day ] = [ 'closed' => true ];
        } else {
            $hours[ $day ] = [
                'open'  => sanitize_text_field( wp_unslash( $_POST["dbp_hours_{$day}_open"] ?? '09:00' ) ),
                'close' => sanitize_text_field( wp_unslash( $_POST["dbp_hours_{$day}_close"] ?? '17:00' ) ),
            ];
        }
    }
    $data['hours'] = $hours;
    if ( $existing ) {
        $business_service->update_business( (int) $existing['id'], $data );
    } else {
        $business_service->create_business( $data );
    }
}, 10, 1 );
// ── Meta Box Render Functions ──
function dbp_render_location_meta_box( \WP_Post $post ): void {
    wp_nonce_field( 'dbp_save_business', 'dbp_business_nonce' );
    $service  = new \DirectoriesBuilderPro\Services\Business_Service();
    $business = $service->get_business_by_post_id( $post->ID ) ?: [];
    ?>
    <table class="form-table">
        <tr><th><label for="dbp_address"><?php esc_html_e( 'Address', 'directories-builder-pro' ); ?></label></th>
            <td><input type="text" id="dbp_address" name="dbp_address" value="<?php echo esc_attr( $business['address'] ?? '' ); ?>" class="regular-text"></td></tr>
        <tr><th><label for="dbp_city"><?php esc_html_e( 'City', 'directories-builder-pro' ); ?></label></th>
            <td><input type="text" id="dbp_city" name="dbp_city" value="<?php echo esc_attr( $business['city'] ?? '' ); ?>" class="regular-text"></td></tr>
        <tr><th><label for="dbp_state"><?php esc_html_e( 'State', 'directories-builder-pro' ); ?></label></th>
            <td><input type="text" id="dbp_state" name="dbp_state" value="<?php echo esc_attr( $business['state'] ?? '' ); ?>" class="regular-text"></td></tr>
        <tr><th><label for="dbp_zip"><?php esc_html_e( 'ZIP / Postal Code', 'directories-builder-pro' ); ?></label></th>
            <td><input type="text" id="dbp_zip" name="dbp_zip" value="<?php echo esc_attr( $business['zip'] ?? '' ); ?>" class="small-text"></td></tr>
        <tr><th><label for="dbp_country"><?php esc_html_e( 'Country', 'directories-builder-pro' ); ?></label></th>
            <td><input type="text" id="dbp_country" name="dbp_country" value="<?php echo esc_attr( $business['country'] ?? 'US' ); ?>" class="small-text"></td></tr>
    </table>
    <input type="hidden" id="dbp_lat" name="dbp_lat" value="<?php echo esc_attr( (string) ( $business['lat'] ?? '' ) ); ?>">
    <input type="hidden" id="dbp_lng" name="dbp_lng" value="<?php echo esc_attr( (string) ( $business['lng'] ?? '' ) ); ?>">
    <div id="dbp-admin-map" class="dbp-admin-map" style="height:300px;margin-top:10px;"
         data-lat="<?php echo esc_attr( (string) ( $business['lat'] ?? '40.7128' ) ); ?>"
         data-lng="<?php echo esc_attr( (string) ( $business['lng'] ?? '-74.0060' ) ); ?>">
    </div>
    <p class="description"><?php esc_html_e( 'Click on the map to set the business location.', 'directories-builder-pro' ); ?></p>
    <?php
}
function dbp_render_contact_meta_box( \WP_Post $post ): void {
    $service  = new \DirectoriesBuilderPro\Services\Business_Service();
    $business = $service->get_business_by_post_id( $post->ID ) ?: [];
    ?>
    <table class="form-table">
        <tr><th><label for="dbp_phone"><?php esc_html_e( 'Phone', 'directories-builder-pro' ); ?></label></th>
            <td><input type="text" id="dbp_phone" name="dbp_phone" value="<?php echo esc_attr( $business['phone'] ?? '' ); ?>" class="regular-text"></td></tr>
        <tr><th><label for="dbp_website"><?php esc_html_e( 'Website', 'directories-builder-pro' ); ?></label></th>
            <td><input type="url" id="dbp_website" name="dbp_website" value="<?php echo esc_attr( $business['website'] ?? '' ); ?>" class="regular-text"></td></tr>
        <tr><th><label for="dbp_email"><?php esc_html_e( 'Email', 'directories-builder-pro' ); ?></label></th>
            <td><input type="email" id="dbp_email" name="dbp_email" value="<?php echo esc_attr( $business['email'] ?? '' ); ?>" class="regular-text"></td></tr>
    </table>
    <?php
}
function dbp_render_details_meta_box( \WP_Post $post ): void {
    $service  = new \DirectoriesBuilderPro\Services\Business_Service();
    $business = $service->get_business_by_post_id( $post->ID ) ?: [];
    $hours    = $business['hours'] ?? '{}';
    if ( is_string( $hours ) ) {
        $hours = json_decode( $hours, true ) ?: [];
    }
    $price = (int) ( $business['price_level'] ?? 1 );
    $days  = [
        'monday' => __( 'Monday', 'directories-builder-pro' ), 'tuesday' => __( 'Tuesday', 'directories-builder-pro' ),
        'wednesday' => __( 'Wednesday', 'directories-builder-pro' ), 'thursday' => __( 'Thursday', 'directories-builder-pro' ),
        'friday' => __( 'Friday', 'directories-builder-pro' ), 'saturday' => __( 'Saturday', 'directories-builder-pro' ),
        'sunday' => __( 'Sunday', 'directories-builder-pro' ),
    ];
    ?>
    <h4><?php esc_html_e( 'Price Level', 'directories-builder-pro' ); ?></h4>
    <?php for ( $i = 1; $i <= 4; $i++ ) : ?>
        <label style="margin-right:15px;">
            <input type="radio" name="dbp_price_level" value="<?php echo esc_attr( (string) $i ); ?>" <?php checked( $price, $i ); ?>>
            <?php echo esc_html( dbp_get_price_label( $i ) ); ?>
        </label>
    <?php endfor; ?>
    <h4 style="margin-top:20px;"><?php esc_html_e( 'Opening Hours', 'directories-builder-pro' ); ?></h4>
    <table class="form-table dbp-hours-editor">
        <thead>
            <tr><th><?php esc_html_e( 'Day', 'directories-builder-pro' ); ?></th><th><?php esc_html_e( 'Open', 'directories-builder-pro' ); ?></th><th><?php esc_html_e( 'Close', 'directories-builder-pro' ); ?></th><th><?php esc_html_e( 'Closed', 'directories-builder-pro' ); ?></th></tr>
        </thead>
        <tbody>
        <?php foreach ( $days as $key => $label ) :
            $day_data  = $hours[ $key ] ?? [];
            $is_closed = ! empty( $day_data['closed'] );
            $open_time = $day_data['open'] ?? '09:00';
            $close_time = $day_data['close'] ?? '17:00';
            ?>
            <tr>
                <td><strong><?php echo esc_html( $label ); ?></strong></td>
                <td><input type="time" name="dbp_hours_<?php echo esc_attr( $key ); ?>_open" value="<?php echo esc_attr( $open_time ); ?>" <?php echo $is_closed ? 'disabled' : ''; ?>></td>
                <td><input type="time" name="dbp_hours_<?php echo esc_attr( $key ); ?>_close" value="<?php echo esc_attr( $close_time ); ?>" <?php echo $is_closed ? 'disabled' : ''; ?>></td>
                <td><input type="checkbox" name="dbp_hours_<?php echo esc_attr( $key ); ?>_closed" value="1" <?php checked( $is_closed ); ?> class="dbp-closed-toggle" data-day="<?php echo esc_attr( $key ); ?>"></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php
}
function dbp_render_status_meta_box( \WP_Post $post ): void {
    $service  = new \DirectoriesBuilderPro\Services\Business_Service();
    $business = $service->get_business_by_post_id( $post->ID ) ?: [];
    $status   = $business['status'] ?? 'active';
    $featured = (bool) ( $business['featured'] ?? false );
    ?>
    <table class="form-table">
        <tr>
            <th><label for="dbp_status"><?php esc_html_e( 'Status', 'directories-builder-pro' ); ?></label></th>
            <td>
                <select id="dbp_status" name="dbp_status">
                    <option value="active" <?php selected( $status, 'active' ); ?>><?php esc_html_e( 'Active', 'directories-builder-pro' ); ?></option>
                    <option value="inactive" <?php selected( $status, 'inactive' ); ?>><?php esc_html_e( 'Inactive', 'directories-builder-pro' ); ?></option>
                    <option value="pending" <?php selected( $status, 'pending' ); ?>><?php esc_html_e( 'Pending', 'directories-builder-pro' ); ?></option>
                </select>
            </td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Featured', 'directories-builder-pro' ); ?></th>
            <td>
                <label>
                    <input type="checkbox" name="dbp_featured" value="1" <?php checked( $featured ); ?>>
                    <?php esc_html_e( 'Mark as featured listing', 'directories-builder-pro' ); ?>
                </label>
            </td>
        </tr>
    </table>
    <?php
}
