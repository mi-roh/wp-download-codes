<?php
/**
 * Created by PhpStorm.
 * User: micharohde on 12.07.18 14:12
 *
 * SDG
 */

function dc_init_capabilities() {

    $role = get_role('administrator');

    /**
     * The Admin has all Capabilities – without – add it.
     */
    if ( !$role->has_cap( DC_CAPABILITY ) ) {
        dc_add_capabilities();
    }

}

function dc_add_capabilities() {

    $roles = get_editable_roles();

    if ( DC_CAPABILITY_REF === DC_CAPABILITY ) {
        return;
    }

    foreach ( $GLOBALS['wp_roles']->role_objects as $key => $role ) {
        /** @type $role WP_Role */
        /** @type $key string */
        if ( isset( $roles[ $key ] ) && $role->has_cap( DC_CAPABILITY_REF ) ) {
            $role->add_cap( DC_CAPABILITY );
        }
    }

}

function dc_remove_capabilities() {

    $roles = get_editable_roles();

    if ( DC_CAPABILITY_REF === DC_CAPABILITY ) {
        return;
    }

    foreach ( $GLOBALS['wp_roles']->role_objects as $key => $role ) {
        /** @type $role WP_Role */
        /** @type $key string */
        if ( isset( $roles[ $key ] ) ) {
            $role->remove_cap( DC_CAPABILITY );
        }
    }

}
