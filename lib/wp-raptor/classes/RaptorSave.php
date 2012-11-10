<?php
class RaptorSave {

    const SAVE_POSTS = 'save_posts';
    const SAVE_POSTS_NONCE = 'save_posts_nonce';
    const SAVE_COMMENTS = 'save_comments';

    public function savePosts() {

        if (!$this->verifyNonce()) {
            header('HTTP/1.0 403 Unauthorized', true, 403);
            die('Access denied');
        }

        $data = $this->extractData('posts');
        if (!$data) {
            header('HTTP/1.0 400 Bad Request', true, 400);
            die('Error saving content');
        }

        $post = array();
        $updatedPosts = 0;
        foreach ($data as $id => $content) {
            if(current_user_can('edit_post', $id)){
                $post['ID'] = $id;
                $post['post_content'] = $content;
                if (wp_update_post($post) !== 0) {
                    $updatedPosts++;
                }
            }
        }

        if ($updatedPosts == 1) {
            die("{$updatedPosts} post saved successfully");
        } else {
            die("{$updatedPosts} posts saved successfully");
        }
    }

    public function verifyNonce() {
        if (!isset($_POST['nonce'])) {
            return false;
        }
        return wp_verify_nonce($_POST['nonce'], self::SAVE_POSTS_NONCE);
    }

    public function extractData($key) {
        // If post content data isn't set
        if (!isset($_POST[$key])) {
            return false;
        }

        // Or it is falsy
        if (!$_POST[$key]) {
            return false;
        }

        // Or json_decode can't decode it successfully
        return $_POST[$key];
    }
}
