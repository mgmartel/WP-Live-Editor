<?php
class RaptorStates {

    public static function admin() {
        return is_admin();
    }

    /**
     * @return boolean True if the user can edit any currently visible posts
     */
    public static function adminViewingPosts() {
        // @todo check if the logged in user can edit any currently visible posts
        return current_user_can('edit_posts');
    }

    /**
     * @return boolean True if the visitor is able to comment on current page
     */
    public static function userCanComment() {
        // @todo check if comments are enabled on this page & if so whether current visitor is elegible to comment
        return comments_open();
    }
}
